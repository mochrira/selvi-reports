<?php 

namespace Selvi\Report;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Excel {

    private $paperMap = [
        'A4' => [
            'code' => PageSetup::PAPERSIZE_A4,
            'width' => 8.27,
            'height' => 11.69
        ]
    ];

    private $orientationMap = [
        'portrait' => PageSetup::ORIENTATION_PORTRAIT,
        'landscape' => PageSetup::ORIENTATION_LANDSCAPE
    ];

    private $spreadsheet;
    private $sheet;

    function __construct() {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    private $x = 0;
    private $y = 0;

    private $pageMargins = ['top' => .5, 'left' => .5, 'bottom' => .5, 'right' => .5];
    private $pageOptions = ['orientation' => 'portrait', 'size' => 'A4'];

    function pageSetup($options) {
        $this->pageMargins = array_merge($this->pageMargins, $options['margin'] ?? []);
        if(isset($this->pageMargins['top'])) $this->sheet->getPageMargins()->setTop($this->pageMargins['top']);
        if(isset($this->pageMargins['left'])) $this->sheet->getPageMargins()->setLeft($this->pageMargins['left']);
        if(isset($this->pageMargins['right'])) $this->sheet->getPageMargins()->setRight($this->pageMargins['right']);
        if(isset($this->pageMargins['bottom'])) $this->sheet->getPageMargins()->setBottom($this->pageMargins['bottom']);

        $this->pageOptions = array_merge($this->pageOptions, $options);
        $this->sheet->getPageSetup()->setOrientation($this->orientationMap[$this->pageOptions['orientation']]);
        $this->sheet->getPageSetup()->setPaperSize($this->paperMap[$this->pageOptions['size']]['code']);
    }

    function getInnerWidth() {
        $orientation = $this->pageOptions['orientation'];
        $paper = $this->paperMap[$this->pageOptions['size']];
        $width = ($orientation == 'landscape' ? $paper['height'] : $paper['width']);
        return $width - ($this->pageMargins['left'] + $this->pageMargins['right']);
    }

    private $tolerance = .79;

    function getPercentWidth($percent) {
        return ((float)$percent / 100 * ($this->getInnerWidth() - $this->tolerance));
    }

    private function getColumnLetter($x) {
        return Coordinate::stringFromColumnIndex($x + 1);
    }

    function rowStart() {
        $this->x = 0;
    }

    function column($txt, $options = []) {
        $options = array_merge([
            'width' => null,
            'fill' => 0,
            'border' => 0,
            'align' => 'L',
            'valign' => 'M',
            'colspan' => 1,
            'rowspan' => 1
        ], $options);

        $colName = $this->getColumnLetter($this->x);
        if($options['width'] != null) {
            if($options['width'] == 'auto') {
                $this->sheet->getColumnDimension($colName)->setAutoSize(true);
            } else {
                if(strpos($options['width'], '%') !== false) {
                    $this->sheet->getColumnDimension($colName)->setWidth($this->getPercentWidth($options['width']), 'in');
                } else {
                    if($options['width'] == 0) {
                        $this->sheet->getColumnDimension($colName)->setWidth($this->getInnerWidth() - $this->tolerance, 'in');
                    } else {
                        $this->sheet->getColumnDimension($colName)->setWidth($options['width'], 'in');
                    }
                }
            }
        }

        $coordinate = $colName.($this->y + 1);
        if(isset($options['type'])) {
            $this->sheet->setCellValueExplicit($coordinate, $txt, $options['type']);
        } else {
            $this->sheet->setCellValue($coordinate, $txt);
        }

        $this->sheet->getStyle($coordinate)->getFont()->setSize($this->currentFontSize);
        $this->sheet->getStyle($coordinate)->getFont()->setBold($this->currentBoldState);
        if($this->currentTextColor !== null) $this->sheet->getStyle($coordinate)->getFont()->getColor()->setARGB($this->currentTextColor);

        $align = 'left';
        switch(strtolower($options['align'])) {
            case 'r' : $align = 'right'; break;
            case 'c' : $align = 'center'; break;
            default : $align = 'left'; break;
        }
        $this->sheet->getStyle($coordinate)->getAlignment()->setHorizontal($align);

        $valign = 'center';
        switch(strtolower($options['valign'])) {
            case 't' : $valign = 'top'; break;
            case 'b' : $valign = 'bottom'; break;
            default : $valign = 'center'; break;
        }
        $this->sheet->getStyle($coordinate)->getAlignment()->setVertical($valign);

        if($options['fill'] == 1) 
            if($this->currentFillColor !== null) $this->sheet->getStyle($coordinate)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($this->currentFillColor);

        $spanRange = $coordinate;
        if($options['colspan'] > 1 && $options['rowspan'] > 1) {
            $start = $coordinate;
            $stopCol = $this->getColumnLetter($this->x + ($options['colspan'] - 1));
            $stopRow = ($this->y + 1) + ($options['rowspan'] - 1);
            $stop = $stopCol.$stopRow;
            $spanRange = $start.':'.$stop;
            $this->sheet->mergeCells($spanRange);
            $this->x += ($options['colspan'] - 1);
        } else {
            if($options['colspan'] > 1) {
                $start = $coordinate;
                $stop = $this->getColumnLetter($this->x + ($options['colspan'] - 1)).($this->y + 1);
                $spanRange = $start.':'.$stop;
                $this->sheet->mergeCells($spanRange);
                $this->x += ($options['colspan'] - 1);
            }
    
            if($options['rowspan'] > 1) {
                $start = $colName.($this->y + 1);
                $stop = $colName.(($this->y + 1) + ($options['rowspan'] - 1));
                $spanRange = $start.':'.$stop;
                $this->sheet->mergeCells($spanRange);
            }
        }

        if($options['border'] !== 0) {
            $l = strpos(strtolower($options['border']), 'l') !== false;
            $t = strpos(strtolower($options['border']), 't') !== false;
            $r = strpos(strtolower($options['border']), 'r') !== false;
            $b = strpos(strtolower($options['border']), 'b') !== false;
            
            if($l && $t && $r && $b) {
                $this->sheet->getStyle($spanRange)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('FF000000'));
            } else {
                if($l) $this->sheet->getStyle($spanRange)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('FF000000'));
                if($t) $this->sheet->getStyle($spanRange)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('FF000000'));
                if($r) $this->sheet->getStyle($spanRange)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('FF000000'));
                if($b) $this->sheet->getStyle($spanRange)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('FF000000'));
            }
        }
        $this->x++;
    }

    function skipColumn() {
        $this->x++;
    }

    function skipRow() {
        $this->y++;
    }

    function rowEnd() {
        $this->y++;
    }

    private $currentFillColor = null;

    function setFillColor($r, $g, $b) {
        $this->currentFillColor = sprintf("FF%02x%02x%02x", $r, $g, $b);
    }

    function clearFillColor() {
        $this->currentFillColor = null;
    }

    private $currentTextColor = null;

    function setTextColor($r, $g, $b) {
        $this->currentTextColor = sprintf("FF%02x%02x%02x", $r, $g, $b);
    }

    function clearTextColor() {
        $this->currentTextColor = null;
    }

    private $currentBoldState = false;

    function setBold($bold) {
        $this->currentBoldState = $bold;
    }

    private $currentFontSize = 11;

    function setFontSize($size) {
        $this->currentFontSize = $size;
    }

    function restoreFontSize() {
        $this->currentFontSize = 11;
    }

    function header($formula) {
        $this->sheet->getHeaderFooter()->setOddHeader($formula);
        $this->sheet->getHeaderFooter()->setEvenHeader($formula);
    }

    function footer($formula) {
        $this->sheet->getHeaderFooter()->setOddFooter($formula);
        $this->sheet->getHeaderFooter()->setEvenFooter($formula);
    }

    function repeatRows($start, $end) {
        $this->sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($start, $end);
    }

    function render($fileName) {
        $writer = new Xlsx($this->spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
    }

}