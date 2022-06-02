<?php 

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/Excel.php';

$excel = new Selvi\Report\Excel();

$excel->rowStart();
    $excel->column('Header 1', ['width' => '50%']);
    $excel->column('Header 2', ['width' => '25%']);
    $excel->column('Header 3', ['width' => '25%']);
$excel->rowEnd();

for($i = 1; $i <= 10; $i++) {
    $excel->rowStart();
        $excel->column('Row '.$i.', Col 1');
        $excel->column('Row '.$i.', Col 2');
        $excel->column('Row '.$i.', Col 3');
    $excel->rowEnd();
}

$excel->render('test.xlsx');