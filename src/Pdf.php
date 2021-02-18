<?php 

namespace Selvi;
use TCPDF; 

class Pdf extends TCPDF {

    private $variables = [];

    function setVariable($name, $value) {
        $this->variables[$name] = $value;
    }

    function getVariable($name) {
        return $this->variables[$name];
    }

    public $hasInit = false;
    private $callbacks = [];

    function __construct() { }

    function tcpdf($args) {
        parent::__construct($args['orientation'], $args['units'], $args['pageSize']);
    }

    private $defaultFontSettings = ['family' => 'helvetica', 'style' => '', 'size' => 12];

    function SetDefaultFontSettings() {
        $opt = $this->defaultFontSettings;
        $this->SetFont($opt['family'], $opt['style'], $opt['size']);
    }

    function SetFontSettings($args = []) {
        $default = $this->defaultFontSettings;
        $opt = array_merge($default, $args);
        $this->SetFont($opt['family'], $opt['style'], $opt['size']);
    }

    private $defaultCellPadding = [0.05, 0.025];

    function SetDefaultCellPaddingSettings() {
        $top = $this->defaultCellPadding[1]; $bottom = $this->defaultCellPadding[1];
        $left = $this->defaultCellPadding[0]; $right = $this->defaultCellPadding[0];
        $this->SetCellPaddings($left, $top, $right, $bottom);
    }

    function SetCellPaddingSettings($padding) {
        $top = $this->defaultCellPadding[1]; $bottom = $this->defaultCellPadding[1];
        $left = $this->defaultCellPadding[0]; $right = $this->defaultCellPadding[0];
        if(is_numeric($padding)) {
            $top = $padding; $bottom = $padding; $left = $padding; $right = $padding;
        }
        if(is_array($padding)) {
            if(count($padding) == 2) {
                $left = $padding[0]; $right = $padding[0];
                $top = $padding[1]; $bottom = $padding[1];
            }
            if(count($padding) == 4) {
                $left = $padding[0]; $right = $padding[2];
                $top = $padding[1]; $bottom = $padding[3];
            }
        }
        $this->SetCellPaddings($left, $top, $right, $bottom);
    }

    function pageStart($args = []) {
        $default = ['pageSize' => 'A4', 'orientation' => 'P', 'units' => 'in', 'margins' => .5];
        $args = array_merge($default, $args);

        $this->callbacks[] = [
            'type' => 'pageStart',
            'args' => $args,
            'callback' => function ($pdf, $args) {
                if($pdf->hasInit == false) {
                    $pdf->tcpdf($args);
                    $pdf->SetPrintHeader(false);
                    $pdf->SetPrintFooter(false);
                    $pdf->SetAutoPageBreak(false);
                    $pdf->SetDefaultFontSettings();
                    $pdf->hasInit = true;
                }
                $pdf->AddPage($args['orientation'], $args['pageSize']);

                if(is_numeric($args['margins'])) {
                    $pdf->SetMargins($args['margins'], 0, $args['margins'], false);
                    $pdf->SetY($args['margins']);
                } else {
                    $pdf->SetMargins($args['margins'][0], 0, $args['margins'][2]);
                }

                $this->checkOverflow = false;
                $pdf->SetY(0);
                $pageHeader = $pdf->getAncestor('pageHeader');
                if($pageHeader != null) {
                    $pageHeader['callback']($this);
                }

                $pdf->SetY($pdf->getPageHeight() - (is_numeric($args['margins']) ? $args['margins'] : $args['margins'][3]));
                $pageFooter = $pdf->getAncestor('pageFooter');
                if($pageFooter != null) {
                    $pageFooter['callback']($this);
                }

                $pdf->SetY(is_numeric($args['margins']) ? $args['margins'] : $args['margins'][1]);
                $this->checkOverflow = true;
            }
        ];
    }

    function pageHeader($callback) {
        $this->callbacks[] = [
            'type' => 'pageHeader',
            'autoCall' => true,
            'callback' => $callback
        ];
    }

    function pageBody($callback) {
        $this->callbacks[] = [
            'type' => 'pageBody',
            'callback' => $callback
        ];
    }

    function pageFooter($callback) {
        $this->callbacks[] = [
            'type' => 'pageFooter',
            'autoCall' => true,
            'callback' => $callback
        ];
    }

    function pageEnd() {
        $this->callbacks[] = [
            'type' => 'pageEnd',
            'callback' => function($pdf) {
                $pdf->endPage(false);
            }
        ];
    }

    function masterHeader($callback) {
        $this->callbacks[] = [
            'type' => 'masterHeader',
            'autoCall' => true,
            'callback' => $callback
        ];
    }

    function masterFooter($callback) {
        $this->callbacks[] = [
            'type' => 'masterFooter',
            'autoCall' => true,
            'callback' => $callback
        ];
    }

    function printAutoCall($name) {
        $this->checkOverflow = false;
        $ancestor = $this->getAncestor($name);
        if($ancestor != null) {
            $ancestor['callback']($this);
        }
        $this->checkOverflow = true;
    }

    function masterData($name, $callback) {
        $this->callbacks[] = [
            'type' => 'masterData',
            'args' => ['dataset' => $name],
            'callback' => function($pdf, $args) use ($callback) {
                $dataset = $pdf->getVariable($args['dataset']);
                $this->printAutoCall('masterHeader');
                foreach($dataset as $index => $row) {
                    $callback($pdf, $row, $index);
                }
                $this->printAutoCall('masterFooter');
            }
        ];
    }

    public $currentIndex = -1;

    function getAncestor($type) {
        $i = $this->currentIndex;
        while($this->callbacks[$i]['type'] != 'pageStart') {$i--;}
        $pageStartIndex = $i;
        
        $j = $this->currentIndex;
        while($this->callbacks[$j]['type'] != 'pageEnd') {$j++;}
        $pageEndIndex = $j;

        $k = $i;
        while($k < $j) { 
            if($this->callbacks[$k]['type'] == $type) return $this->callbacks[$k];
            $k++;
        }
        return null;
    }

    function getCurrentAncestor() {
        return $this->callbacks[$this->currentIndex];
    }

    function render($name = 'download.pdf') {
        foreach($this->callbacks as $index => $cb) {
            if(!isset($cb['autoCall']) || (isset($cb['autoCall']) && $cb['autoCall'] == false)) {
                $this->currentIndex = $index;
                if(isset($cb['args'])) {
                    $cb['callback']($this, $cb['args']);
                } else {
                    $cb['callback']($this);
                }
            }
        }
        $this->Output($name, 'I');
        die();
    }

    private $checkOverflow = true;

    function column($txt, $options = [], $break = false) {
        $defaults = ['width' => 0.0, 'height' => 0, 'align' => 'L', 'border' => 'LTRB', 'valign' => 'C', 'colAlign' => 'T', 'stretch' => 0];
        $options = array_merge($defaults, $options);

        $w = $options['width'];
        $h = $options['height'];
        $align = $options['align'];
        $border = $options['border'];
        $valign = $options['valign'];
        $colAlign = $options['colAlign'];
        $stretch = $options['stretch'];

        if($this->checkOverflow == true) {
            $isOverflow = false;
            $pageStart = $this->getAncestor('pageStart');
            $margins = $pageStart['args']['margins'];
            $max = $this->getPageHeight() - (is_numeric($margins) ? $margins : $margins[3]);

            if($this->getCurrentAncestor()['type'] == 'masterData') {
                $this->startTransaction();
                $y = $this->GetY();
                $this->printAutoCall('masterFooter');
                $cellHeight = $this->GetY() - $y;
                $this->rollbackTransaction(true);
                $max -= $cellHeight;
            }

            $this->startTransaction();
            $y = $this->GetY();
            if(isset($options['font'])) {
                $this->SetFontSettings($options['font']);
            }
            if(isset($options['padding'])) {
                $this->SetCellPaddingSettings($options['padding']);
            }
            $this->Cell($w, $h, $txt, $border, 1, $align, 1, '', $stretch, false, $colAlign, $valign);
            $cellHeight = $this->GetY() - $y;
            $this->SetDefaultFontSettings();
            $this->SetDefaultCellPaddingSettings();
            $this->rollbackTransaction(true);

            $isOverflow = $this->GetY() + $cellHeight > $max;
            if($isOverflow == true) {
                $this->printAutoCall('masterFooter');
                $pageStart['callback']($this, $pageStart['args']);
                $this->printAutoCall('masterHeader');
            }
        }

        if(isset($options['font'])) {
            $this->SetFontSettings($options['font']);
        }
        if(isset($options['padding'])) {
            $this->SetCellPaddingSettings($options['padding']);
        }
        $this->Cell($w, $h, $txt, $border, $break ? 1 : 0, $align, 0, '', $stretch, false, $colAlign, $valign);
        $this->SetDefaultFontSettings();
        $this->SetDefaultCellPaddingSettings();
    }

}