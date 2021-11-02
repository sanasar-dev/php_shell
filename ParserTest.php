<?php

namespace php_shell;

$GLOBALS['config'] = require_once 'config.php';
require_once 'Parser.php';
require_once 'helpers.php';

use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
  /**
   * Lines to add to the text file.
   * 
   * @var int 
   */
  protected $lines;

  /**
   * File for testing.
   * 
   * @var string 
   */
  protected $testCSVFileName;

  /**
   * Test output combination file.
   * 
   * @var string 
   */
  protected $outputUniqueCombinationFileName;

  /**
   * Required fields if not found within file should throw an exception.
   * 
   * This parameter is used to select for which columns 
   * to skip the value for testing required fields.
   * @var null 
   */
  private $skipColumn = null;
  
  public function __construct()
  {
    parent::__construct();
    $this->lines = 100000;
    $this->testCSVFileName = __DIR__ . '/' . time() . '-test-csv-file.csv';
    $this->outputUniqueCombinationFileName = __DIR__ . '/' . time() . '-combination-count.csv';
  }

  /**
   * File header names.
   * 
   * @return string[]
   */
  public function filHeadings()
  {
    return [
      'brand_name',
      'model_name',
      'colour_name',
      'gb_spec_name',
      'network_name', 
      'grade_name', 
      'condition_name'
    ];
  }

  /**
   * Here random numbers are created to get different strings with different values, 
   * and to get duplicate strings.
   * 
   * @return array
   */
  public function generateRandomValuesForFields()
  {
    $csvLine = [];
    $randomNumber = rand(10, 30);
    
    for ($i = 0; $i < count($this->filHeadings()); $i++) {
      $csvLine[] = (!is_null($this->skipColumn) && $i === $this->skipColumn) ? '' : 'Random values for csv columns ' . $randomNumber;
    }
    
    return $csvLine;
  }

  /**
   * Creating a text file that needs to be parsed.
   */
  public function createTestFile()
  {
    $csv = fopen($this->testCSVFileName, 'w');
    fputcsv($csv, $this->filHeadings());
    
    for ($i = 0; $i < $this->lines; $i++) {
      fputcsv($csv, $this->generateRandomValuesForFields());
    }
    
    fclose($csv);
  }

  /**
   * Options from command line.
   * 
   * @return array
   */
  public function getCommandOptions()
  {
    return [
      'file' => $this->testCSVFileName,
      'unique-combinations' => $this->outputUniqueCombinationFileName
    ];
  }

  /**
   * Testing required fields
   */
//  public function testRequiredProperties()
//  {
//    // Given
//    $this->skipColumn = 1;
//    $this->createTestFile();
//    
//    // When
//    (new Parser($this->getCommandOptions()))->parse();
//    
//    // Then
//    $this->assertFileExists($this->outputUniqueCombinationFileName);
//  }

  /**
   * Parsing file
   */
  public function testParseFile()
  {
    // Given
    $this->createTestFile();
    
    // When
    (new Parser($this->getCommandOptions()))->parse();
    
    // Then
    $this->assertFileExists($this->outputUniqueCombinationFileName);
  }
}
