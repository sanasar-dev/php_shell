<?php
return [
  
  /*
    |--------------------------------------------------------------------------
    | File headers.
    |--------------------------------------------------------------------------
    |
    | Since the file headers may change in the future, you can configure them here.
    | 
    |
    */
  'product_object_keys' => [
    'brand_name' => 'make',
    'model_name' => 'model',
    'colour_name' => 'colour',
    'gb_spec_name' => 'capacity',
    'network_name' => 'network',
    'grade_name' => 'grade',
    'condition_name' => 'condition'
  ],

  /*
    |--------------------------------------------------------------------------
    | MIME TYPES.
    |--------------------------------------------------------------------------
    |
    | All mime types can be found here
    | https://www.iana.org/assignments/media-types/media-types.xhtml
    |
    | We need to check if the file can be parsed.
    | There are a lot of possible MIME types for CSV files, depending on the user's OS
    | So we need to check all the cases.
    |
    */
  'allowed_mime_types' => [
    'text/csv',
    'text/plain',
    'application/csv',
    'text/comma-separated-values',
    'application/excel',
    'application/vnd.ms-excel',
    'application/vnd.msexcel',
    'text/anytext',
    'application/octet-stream',
    'application/txt',
    'text/xml',
  ],
  
  'allowed_extension' => [
    'tsv',
    'csv',
    'xml',
    'json'
  ]
  
];
