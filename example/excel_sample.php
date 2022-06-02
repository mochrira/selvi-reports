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
    $excel->column('Fill Colored', ['width' => '20%', 'fill' => 1, 'border' => 'b']);
    $excel->column('Fill Colored', ['width' => '20%', 'fill' => 1, 'border' => 'b']);
    $excel->column('Fill Colored', ['width' => '20%', 'fill' => 1, 'border' => 'b']);
    $excel->column('Fill Colored', ['width' => '20%', 'fill' => 1, 'border' => 'b']);
    $excel->column('Fill Colored', ['width' => '20%', 'fill' => 1, 'border' => 'b']);
$excel->rowEnd();
$excel->clearFillColor();
$excel->clearTextColor();
$excel->setBold(false);

$excel->rowStart();
    $excel->column('Inner Width', ['border' => 'b']);
    $excel->column('Inner Width', ['border' => 'b']);
    $excel->column('Inner Width', ['border' => 'b']);
    $excel->column('Inner Width', ['border' => 'b']);
    $excel->column('Inner Width', ['border' => 'b']);
$excel->rowEnd();

$excel->render('test.xlsx');