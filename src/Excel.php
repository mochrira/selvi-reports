<?php 

namespace Selvi\Report;

use OpenSpout\Writer\XLSX\Options\PaperSize;
use OpenSpout\Writer\XLSX\Writer;

class Excel {

    private Writer $writer;

    function __construct($filename = 'sheet.xlsx') {
        $this->writer = new Writer();
        $this->writer->openToBrowser($filename);
    }

    function rowStart() {

    }

    function column() {

    }

    function rowEnd() {

    }

    function render() {
        $this->writer->close();
    }

}