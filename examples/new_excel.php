<?php 

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/../src/Excel.php';

$excel = new Selvi\Report\Excel();

$excel->pageSetup([
    'orientation' => 'portrait'
]);
$excel->open('test.xlsx');

$excel->addRow(['Carl', 'is', 'great!']);
$excel->addRow(['Carl']);

$excel->close();
