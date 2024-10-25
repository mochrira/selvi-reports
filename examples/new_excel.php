<?php 

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/../src/Excel.php';

$excel = new Selvi\Report\Excel();

$excel->pageSetup([
    'orientation' => 'portrait'
]);
$excel->openBrowser('test.xlsx');

$excel->setBold(true);
$excel->setFontSize(14);
$excel->rowStart();
    $excel->column('LAPORAN DATA PENDAFTAR', ['colspan' => 5, 'align' => 'C']);
$excel->rowEnd();
$excel->restoreFontSize();

$excel->setFillColor(31, 108, 142);
$excel->setTextColor(255, 255, 255);

$excel->rowStart();
    $excel->column('3510161103920002', ['fill' => 1, 'align' => 'C']);
    $excel->column('Fill Colored', ['fill' => 1, 'border' => 'b', 'align' => 'C']);
    $excel->column('Fill Colored', ['fill' => 1, 'border' => 'b', 'align' => 'L']);
    $excel->column('Fill Colored', ['fill' => 1, 'border' => 'b', 'align' => 'C']);
    $excel->column('Fill Colored', ['fill' => 1, 'border' => 'b', 'align' => 'R']);
$excel->rowEnd();
$excel->clearFillColor();
// $excel->clearTextColor();
$excel->setBold(false);

for($i = 1; $i <= 100; $i++) {
    $excel->rowStart();
        $excel->column('Inner Width', ['border' => 'b']);
        $excel->column('Inner Width', ['border' => 'b']);
        $excel->column('Inner Width', ['border' => 'b']);
        $excel->column('Inner Width', ['border' => 'b']);
        $excel->column('Inner Width', ['border' => 'b']);
    $excel->rowEnd();
}
$excel->close();
