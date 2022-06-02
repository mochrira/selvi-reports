<?php 

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/Excel.php';

$excel = new Selvi\Report\Excel();

$excel->pageSetup([
    'orientation' => 'potrait'
]);

$excel->setBold(true);
$excel->setFillColor(31, 108, 142);
$excel->setTextColor(255, 255, 255);
$excel->rowStart();
    $excel->column('Fill Colored', ['width' => '20%', 'fill' => 1, 'border' => 'b', 'align' => 'C', 'valign' => 'T', 'colspan' => 2, 'rowspan' => 2]);
    $excel->column('Fill Colored', ['width' => '20%', 'fill' => 1, 'border' => 'b', 'align' => 'L', 'valign' => 'T']);
    $excel->column('Fill Colored', ['width' => '20%', 'fill' => 1, 'border' => 'b', 'align' => 'C']);
    $excel->column('Fill Colored', ['width' => '20%', 'fill' => 1, 'border' => 'b', 'align' => 'R']);
$excel->rowEnd();

$excel->clearFillColor();
$excel->clearTextColor();
$excel->setBold(false);
$excel->rowStart();
    $excel->skipColumn();
    $excel->skipColumn();
    $excel->column('Inner Width', ['border' => 'b']);
    $excel->column('Inner Width', ['border' => 'b', 'valign' => 'T']);
    $excel->column('Inner Width', ['border' => 'b']);
$excel->rowEnd();

$excel->rowStart();
    $excel->column('Inner Width', ['border' => 'b', 'width' => '20%']);
    $excel->column('Inner Width', ['border' => 'b', 'width' => '20%']);
$excel->rowEnd();

$excel->render('test.xlsx');