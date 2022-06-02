<?php 

namespace Selvi\Report;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class Excel {

    private $spreadsheet;
    private $sheet;

    function __construct() {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    private function getColumnLetter($x, $y) {
        return Coordinate::stringFromColumnIndex($x + 1).($y + 1);
    }

    private $x = 0;
    private $y = 0;

    function rowStart() {
        $this->x = 0;
    }

    function column($txt, $options = []) {
        $col = $this->getColumnLetter($this->x, $this->y);
        $this->sheet->setCellValue($col, $txt);
        $this->x++;
    }

    function rowEnd() {
        $this->y++;
    }

    function render($fileName) {
        $writer = new Xlsx($this->spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
    }

}