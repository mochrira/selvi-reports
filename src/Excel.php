<?php 

namespace Selvi\Report;

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\XLSX\Sheet;
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

    private $align = [
        'l' => CellAlignment::LEFT,
        'c' => CellAlignment::CENTER,
        'r' => CellAlignment::RIGHT,
        'j' => CellAlignment::JUSTIFY
    ];

    private $valign = [
        't' => CellVerticalAlignment::TOP,
        'b' => CellVerticalAlignment::BOTTOM,
        'c' => CellVerticalAlignment::CENTER,
        'j' => CellVerticalAlignment::JUSTIFY,
    ];

    private $border = [
        't' => Border::TOP,
        'b' => Border::BOTTOM,
        'l' => Border::LEFT,
        'r' => Border::RIGHT,
    ];

    private Writer $writer;
    private Options $options;
    private Sheet $sheet;
    private array $cells = [];
    private Style $style;

    private $pageMargins = ['top' => .5, 'left' => .5, 'bottom' => .5, 'right' => .5];
    private $pageOptions = ['orientation' => 'portrait', 'size' => 'A4'];

    function __construct() {
        $this->options = new Options();
        $this->writer = new Writer($this->options);
        $this->style = new Style();
        // $this->sheet = $this->writer->getCurrentSheet();
    }

    function getSpreadsheet() {
        return $this->writer;
    }

    function pageSetup(array $options) {
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

    function setFontSize(int $size){
        $this->style->setFontSize($size);
    }

    function restoreFontSize(){
        $this->style->setFontSize(11);
    }

    private $fillColor = null;
    function setFillColor($r, $g, $b) {
        $this->fillColor = sprintf("FF%02x%02x%02x", $r, $g, $b);
        $this->style->setBackgroundColor($this->fillColor);
    }

    private $textColor = null;
    function setTextColor($r, $g, $b) {
        $this->textColor = sprintf("FF%02x%02x%02x", $r, $g, $b);
        $this->style->setFontColor($this->textColor);
    }

    public function setBold(bool $bold){
        if ($bold) {
            $this->style->setFontBold();
        }
    }

    function clearFillColor(){
        $this->style->setBackgroundColor(Color::WHITE);
    }

    function clearTextColor(){
        $this->style->setFontColor(Color::BLACK);
    }

    public function rowStart(){
        $this->cells = [];
    }

    private function setBorder(string $border) : Border {
        $borerPart = new BorderPart(
            name: $this->border[strtolower($border)],
            color:Color::BLACK, 
            width:Border::WIDTH_THIN, 
            style:Border::STYLE_SOLID
        );

        return new Border($borerPart);
    }

    public function column(mixed $txt, array $options = null){
        if (is_array($options) && count($options) > 0 ) {
            $style = new Style();
            if (isset($options['align'])) {
                $style->setCellAlignment($this->align[strtolower($options['align'])]);
            }

            if (isset($options['valign'])) {
                $style->setCellVerticalAlignment($this->valign[strtolower($options['valign'])]);
            }

            if (isset($options['border'])) {
                $style->setBorder($this->setBorder(strtolower($options['border'])));
            }
            
            if (isset($options['fill']) && $options['fill'] == 1) {
                $style->setBackgroundColor($this->fillColor);
            }
        }

        $this->cells[] = Cell::fromValue(value:$txt, style:$style);
    }

    public function rowEnd(){
        $row = new Row($this->cells, $this->style);
        $this->writer->addRow($row);
    }

    function close() {
        $this->writer->close();
    }

    function openBrowser(string $fileName){
        $this->writer->openToBrowser($fileName);
    }


}