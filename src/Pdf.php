<?php 

namespace Selvi;
use TCPDF;

class Pdf extends TCPDF {

    private $width = 0;
    private $height = 0;

    private $marginTop = 0;
    private $marginBottom = 0;

    private $variables = [];

    function setVariable($name, $value) {
        $this->variables[$name] = $value;
    }

    function getVariable($name) {
        return $this->variables[$name];
    }

    function __construct($pageSize, $orientation = 'potrait', $margins = .5) {
        $this->width = $pageSize[0];
        $this->height = $pageSize[1];
        parent::__construct($orientation == 'potrait' ? 'P' : 'L', 'in', $pageSize);
        $this->SetPrintHeader(false);
        $this->SetAutoPageBreak(false);
        if(is_numeric($margins)) {
            $this->SetMargins($margins, $margins, $margins, false);
            $this->marginTop = $margins;
            $this->marginBottom = $margins;
        } else {
            $this->SetMargins($margins[0], 0, $margins[1]);
            $this->marginTop = $margins[1];
            $this->marginBottom = $margins[3];
        }
    }

    private $isPrintFooter = false;

    function column($txt, $options = [], $break = false) {
        $defaults = ['width' => 0, 'height' => 0, 'align' => 'L', 'border' => 'LTRB', 'valign' => 'C'];
        $options = array_merge($defaults, $options);

        $w = $options['width'];
        $h = $options['h'];
        $align = $options['align'];
        $border = $options['border'];
        $valign = $options['valign'];

        $pageNo = $this->PageNo();

        if(!$this->isPrintFooter) {
            $this->startTransaction();
            $y = $this->GetY();
            $this->Cell($w, 0, $txt, $border, 1, $align, 1, '', 0, false, 'T', $valign);
            $cellHeight = $this->GetY() - $y;
            $this->rollbackTransaction(true);
            $isOverflow = $this->GetY() + $cellHeight > ($this->height - $this->marginBottom);

            if($pageNo == 0 || $isOverflow) {
                $this->addPage();

                $this->SetY(0);
                $this->doCallback('pageHeader');

                $this->SetY($this->height - $this->marginBottom);
                $this->isPrintFooter = true;
                $this->doCallback('pageFooter');
                $this->isPrintFooter = false;

                $this->SetY($this->marginTop);
            }
        }
        
        $this->Cell($w, $h, $txt, $border, $break ? 1 : 0, $align, 0, '', 0, false, 'T', $valign);
    }

    private $callbacks = [];

    function pageHeader($callback) {
        $this->callbacks['pageHeader'][] = $callback;
    }

    function reportHeader($callback) {
        $this->callbacks['reportHeader'][] = $callback;
    }

    function reportBody($callback) {
        $this->callbacks['reportBody'][] = ['type' => 'body', 'callback' => $callback];
    }

    function reportFooter($callback) {
        $this->callbacks['reportFooter'][] = $callback;
    }

    function pageFooter($callback) {
        $this->callbacks['pageFooter'][] = $callback;
    }

    function doCallback($name) {
        if($this->callbacks[$name]) {
            foreach($this->callbacks[$name] as $callback) {
                if(is_callable($callback)) {
                    $callback($this);
                }

                if(is_array($callback)) {
                    if(is_callable($callback['callback'])) {
                        $callback['callback']($this);
                    }
                }
            }
        }
    }

    function render($name = 'download.pdf') {
        $this->doCallback('reportHeader');
        $this->doCallback('reportBody');
        $this->doCallback('reportFooter');
        $this->Output($name, 'I');
        die();
    }

}