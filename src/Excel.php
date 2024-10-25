<?php 

namespace Selvi\Report;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Options\PageMargin;
use OpenSpout\Writer\XLSX\Options\PageOrientation;
use OpenSpout\Writer\XLSX\Options\PageSetup;
use OpenSpout\Writer\XLSX\Options\PaperSize;

class Excel {

    private $paperMap = [
        'A4' => [
            'code' => PaperSize::A4,
            'width' => 8.27,
            'height' => 11.69
        ]
    ];

    private $orientationMap = [
        'portrait' => PageOrientation::PORTRAIT,
        'landscape' => PageOrientation::LANDSCAPE
    ];

    private Writer $writer;
    private Options $options;
    private $sheet;

    function __construct() {
        $this->options = new Options();
        $this->writer = new Writer($this->options);
        // $this->sheet = $this->writer->getCurrentSheet();
    }

    function getSpreadsheet() {
        return $this->writer;
    }

    private $x = 0;
    private $y = 0;

    private $pageMargins = ['top' => .5, 'left' => .5, 'bottom' => .5, 'right' => .5];
    private $pageOptions = ['orientation' => 'portrait', 'size' => 'A4'];

    function pageSetup($options) {
        $this->pageMargins = array_merge($this->pageMargins, $options['margin'] ?? []);
        $this->pageOptions = array_merge($this->pageOptions, $options);
        $this->options->setPageSetup(new PageSetup(
            pageOrientation:$this->orientationMap[$this->pageOptions['orientation']],
            paperSize:$this->paperMap[$this->pageOptions['size']]['code'],
            fitToHeight:0,
            fitToWidth:1 
        ));
        
        $this->options->setPageMargin(new PageMargin(
           top:$this->pageMargins['top'],
           right:$this->pageMargins['right'],
           bottom:$this->pageMargins['bottom'],
           left:$this->pageMargins['left'],
        ));
    }

    

    function addRow(array $data) {
        $rowFromValues = Row::fromValues($data);
        $this->writer->addRow($rowFromValues);
    }

    function close() {
        $this->writer->close();
    }

    function open($fileName){
        $this->writer->openToBrowser($fileName);
    }


}