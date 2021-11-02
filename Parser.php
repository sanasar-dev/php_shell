<?php

namespace php_shell;

class Parser
{
  /**
   * The source file that needs to be analyzed.
   * 
   * @var 
   */
  protected $inputFile;

  /**
   * File with a grouped count for each unique combination.
   * 
   * @var mixed|null 
   */
  protected $outputUniqueCombinationFile;

  /**
   * Configuration file.
   * 
   * @var mixed 
   */
  protected $config;

  /**
   * Extension of given file.
   * 
   * @var 
   */
  protected $extension;

  /**
   * Product object keys.
   * 
   * @var 
   */
  protected $productObjectKeys;

  /**
   * The separator of the input file.
   * 
   * @var 
   */
  protected $separator;

  /**
   * @var array
   */
  protected $product;

  /**
   * @param $options
   */
  public function __construct($options) {
    $this->validateCommandArguments($options);
    $this->config = $GLOBALS['config'];
    $this->inputFile = $options['file'];
    $this->outputUniqueCombinationFile = $options['unique-combinations'];
    $this->checkIfFileReadable();
    $this->checkIfFileEmpty();
    $this->checkMimeTypes();
    $this->setProductObjectKeys();
    $this->setSeparator();
  }

  /**
   * Parsing input file.
   */
  public function parse()
  {
    $this->chooseParsingMethod();
  }

  /**
   * Checking the required arguments for the command line.
   * 
   * @param $options
   */
  private function validateCommandArguments($options)
  {
    if (!array_key_exists('file', $options)) die("Missing required option --file\n");
    if (!array_key_exists('unique-combinations', $options)) die("Missing required option --unique-combinations\n");
  }

  /**
   * Checking whether the file exists and whether it can be read.
   */
  private function checkIfFileReadable()
  {
    if (!is_readable($this->inputFile)) die("File not found or it is invalid\n");
  }

  /**
   * Exit code if the file is empty.
   */
  private function checkIfFileEmpty()
  {
    if (!filesize($this->inputFile)) die("File is empty\n");
  }

  /**
   * Set separator for parsing file.
   */
  private function setSeparator()
  {
    $this->separator = $this->detectDelimiter();
  }

  /**
   * Get separator for parsing file.
   * 
   * @return mixed
   */
  private function getSeparator()
  {
    return $this->separator;
  }

  /**
   * Detect separator for input file.
   * 
   * @return false|int|string
   */
  private function detectDelimiter()
  {
    $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

    $handle = fopen($this->inputFile, "r");
    $firstLine = fgets($handle);
    fclose($handle);
    
    foreach ($delimiters as $delimiter => &$count) {
      $count = count(str_getcsv($firstLine, $delimiter));
    }

    return array_search(max($delimiters), $delimiters);
  }

  /**
   * If an XML document -- that is, the unprocessed, 
   * source XML document -- is readable by casual users, 
   * text/xml is preferable to application/xml. 
   * MIME user agents (and web user agents) 
   * that do not have explicit support for text/xml 
   * will treat it as text/plain, 
   * for example, by displaying the XML MIME entity as plain text.
   * And so we need to check the file extension to select the parsing method.
   * 
   * From the RFC (3023 | https://www.rfc-editor.org/rfc/rfc3023), under section 3, XML Media Types:
   */
  private function checkMimeTypes()
  {
    $this->extension = strtolower(pathinfo($this->inputFile, PATHINFO_EXTENSION));

    if (!in_array($this->extension , $this->config['allowed_extension'])) {
      die("The mime type of file is not supported\n");
    }
  }

  /**
   * Set keys for Product object.
   * Since the file headings may change in the future, 
   * we can change them in the configuration file.
   */
  private function setProductObjectKeys()
  {
    $this->productObjectKeys = array_keys($this->config['product_object_keys']);
  }

  /**
   * @return mixed
   */
  private function getProductObjectKeys()
  {
    return $this->productObjectKeys;
  }

  /**
   * @return mixed
   */
  private function getProductObjectInstance()
  {
    return $this->config['product_object_keys'];
  }

  /**
   * @param $fileHeaders
   */
  private function buildingInstanceOfProductObject($fileHeaders)
  {
    $this->product = [];

    foreach ($fileHeaders as $header) {
      array_push($this->product, $this->getProductObjectInstance()[$header]);
    }
  }

  /**
   * Choosing the parsing method depending on the file extension.
   * This way, in the future we will be able to create new methods for new files.
   */
  private function chooseParsingMethod()
  {
    switch ($this->extension) {
      case 'csv':
      case 'tsv':
        $this->buildProductObjectFromCSV();
        break;
      case 'xml':
        $this->buildProductObjectFromXML();
        break;
      case 'json':
        $this->buildProductObjectFromJSON();
        break;
      default:
        die("A file with the .$this->extension extension is not supported for parsing\n");
    }
  }

  /**
   * To find duplicates, we need to somehow save the lines from the file.
   * Using an array will work well with small file sizes,
   * but for large files, that will cause an exception about the allowable memory limit.
   * 
   * This could be done by splitting files into small fragments and saving duplicate lines in it,
   * and then recursively process the files to get a single file containing all the duplicates,
   * but for large files, that will cause exceptions about the maximum execution time.
   * 
   * And that's why "sort" is used here. It is a standard command line program of Unix and Unix-like operating systems.
   */
  private function buildProductObjectFromCSV()
  {
    $csv = fopen($this->inputFile, 'r') or die('Could not open source file');
    $fileHeaders = array_filter(fgetcsv($csv, 0, $this->getSeparator()));
    $diff = array_diff($this->getProductObjectKeys(), $fileHeaders);
    fclose($csv);

    if (count($diff)) {
      die("The file headers do not match the product object keys. Please check the configuration file to fix it\n");
    }

    $this->buildingInstanceOfProductObject($fileHeaders);
    $headingsForCombinationFile = array_merge(array_values($this->product), ['count']);
    $tempUniqueFileName = time() . '-temp-file.txt';
    $outputStream = fopen($this->outputUniqueCombinationFile, 'w') or die('Could not open file');
    fputcsv($outputStream, $headingsForCombinationFile);

    $command = 'sort ' . $this->inputFile . ' | uniq --count > ' . $tempUniqueFileName;
    exec($command, $output, $code);

    // Return will return non-zero upon an error
    if ($code) {
      if (file_exists($tempUniqueFileName)) @unlink($tempUniqueFileName);
      die('Something wrong with file');
    }

    $tempFileStream = fopen($tempUniqueFileName, 'r');

    // Headings in the file may be in other orders.
    $requiredKeyIndexForMake = array_search('make', $this->product);
    $requiredKeyIndexForModel = array_search('model', $this->product);
    $fileLines = 0;

    while (($line = fgets($tempFileStream)) !== false) {
      
      list($count, $string) = explode(' ', trim($line), 2);
      $string .= $this->getSeparator() . $count;
      $csvFormat = str_getcsv($string, $this->getSeparator());
      $fileLines += $count;

      if (is_array($csvFormat) && (!$csvFormat[$requiredKeyIndexForMake] || !$csvFormat[$requiredKeyIndexForModel])) {
        fclose($tempFileStream);
        fclose($outputStream);
        if (file_exists($tempUniqueFileName)) @unlink($tempUniqueFileName);
        if (file_exists($this->outputUniqueCombinationFile)) @unlink($this->outputUniqueCombinationFile);
        die('Missing required properties');
      }
      
      if ((int)$count > 1) {
        fputcsv($outputStream, $csvFormat);
      }

      array_pop($csvFormat);

      // Ignore exceptions when optional values are empty.
      $productObject = @array_combine($this->product, $csvFormat);

      /**
       * From exercise - "We have multiple different formats of files that need to be parsed and returned back as a Product object ..."
       * Here it is unclear for me where to return the created product objects.
       */
      for ($i = 0; $i < $count; $i++) {
//        print_r($productObject);
      }
    }

    fclose($outputStream);
    fclose($tempFileStream);
    if (file_exists($tempUniqueFileName)) @unlink($tempUniqueFileName);
    printf("Total lines: %s\r\n", $fileLines);
  }

  /**
   * Once the format of the for xml file is clear, 
   * then the implementation of this function will be simple.
   */
  private function buildProductObjectFromXML()
  {
    
  }
  
  private function buildProductObjectFromJSON()
  {
    
  }

}
