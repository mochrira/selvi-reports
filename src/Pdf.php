<?php 

namespace Selvi\Report;

use TCPDF;

class Pdf extends TCPDF {

    private $init = false;
    private $components = [];

    private $simulationLock = false;
    private $simulation = false;

    function __construct() {
        
    }

    function startSimulation($lock = false) {
        if($this->simulation === false) {
            $this->simulation = true;
            $this->simulationLock = $lock;
            $this->startTransaction();
        }
    }

    function stopSimulation($forceStop = false) {
        if(($this->simulation && !$this->simulationLock) || $forceStop) {
            $this->simulation = false;
            $this->simulationLock = false;
            $this->rollbackTransaction(true);
        }
    }

    function getNearestTopIndex($name, $startIndex, $stopIndex = 0) {
        $i = $startIndex;
        while($i >= $stopIndex && $i >= 0) {
            if($this->components[$i]['name'] == $name) {
                return $i;
            }
            if($this->components[$i]['name'] == 'pageStart') {
                return null;
            }
            $i--;
        }
        return -1;
    }

    function getNearestTop($name, $startIndex, $stopIndex = 0) {
        $index = $this->getNearestTopIndex($name, $startIndex, $stopIndex);
        if($index > -1) return $this->components[$index];
        return null;
    }

    function getNearestBottomIndex($name, $startIndex, $stopIndex = 0) {
        $i = $startIndex;
        if($stopIndex == 0) $stopIndex = count($this->components) - 1;
        while($i <= $stopIndex) {
            if($this->components[$i]['name'] == $name) {
                return $i;
            }
            if($this->components[$i]['name'] == 'pageEnd') {
                return null;
            }
            $i++;
        }
        return -1;
    }

    function getNearestBottom($name, $startIndex, $stopIndex = 0) {
        $index = $this->getNearestBottomIndex($name, $startIndex, $stopIndex);
        if($index > -1) return $this->components[$index];
        return null;
    }

    function pageStart($args = []) {
        $this->components[] = [
            'name' => 'pageStart',
            'args' => [$args]
        ];
    }

    function getFeps() {
        return $this->feps;
    }

    function _pageStart($args = [], $comIndex = null) {
        $default = ['orientation' => 'P', 'units' => 'in', 'size' => 'A4'];
        $pageArgs = array_merge($default, $args);
        if(!$this->init) {
            parent::__construct($pageArgs['orientation'], $pageArgs['units'], $pageArgs['size']);
            $this->SetPrintHeader(false);
            $this->SetPrintFooter(false);
            $this->init = true;
        }

        $this->SetLeftMargin($pageArgs['margins']['left'] ?? 0.5);
        $this->SetRightMargin($pageArgs['margins']['right'] ?? 0.5);
        $this->SetTopMargin(0);
        $this->components[$comIndex]['margins']['top'] = $pageArgs['margins']['top'] ?? 0;
        $this->components[$comIndex]['margins']['bottom'] = $pageArgs['margins']['bottom'] ?? 0;

        $this->setAutoPageBreak(false);
        $this->AddPage($pageArgs['orientation'], $pageArgs['size']);
        $this->SetY($pageArgs['margins']['top']);
        $this->_pageHeader($comIndex);
    }

    function pageEnd($args = []) {
        $this->components[] = [
            'name' => 'pageEnd',
            'args' => $args
        ];
    }

    function _pageEnd($comIndex = null) {
        $this->_pageFooter($comIndex);
        $this->endPage();
    }

    function pageHeader($callback = null) {
        $this->components[] = [
            'name' => 'pageHeader',
            'callback' => $callback
        ];
    }

    function _pageHeader($comIndex) {
        $pageHeader = $this->getNearestTop('pageHeader', $comIndex);
        if(!$pageHeader) {
            $pageHeader = $this->getNearestBottom('pageHeader', $comIndex);
        }

        if($pageHeader) {
            $this->SetY(0);
            $pageHeader['callback']($this);
            $this->Ln();
        }

        $pageStart = $this->getNearestTop('pageStart', $comIndex);
        $this->SetY($pageStart['margins']['top']);
    }

    function pageFooter($callback = null) {
        $this->components[] = [
            'name' => 'pageFooter',
            'callback' => $callback
        ];
    }

    function _pageFooter($comIndex = null) {
        $pageFooter = $this->getNearestTop('pageFooter', $comIndex);
        if(!$pageFooter) {
            $pageFooter = $this->getNearestBottom('pageFooter', $comIndex);
        }

        if($pageFooter) {
            $pageStart = $this->getNearestTop('pageStart', $comIndex);
            $y = ((float)$this->getPageHeight()) - ($pageStart['margins']['bottom'] ?? 0);
            $this->SetY($y);
            $pageFooter['callback']($this);
            $this->Ln();
        }
    }

    function band($callback = null) {
        $this->components[] = [
            'name' => 'band',
            'args' => [$callback]
        ];
    }

    function _band($callback = null, $comIndex = null) {
        if($callback != null) {
            $this->_detectPageBreak($callback, $comIndex);
            $callback($this);
        }
    }

    function masterStart() {
        $this->components[] = [
            'name' => 'masterStart'
        ];
    }

    function _masterStart() {}

    function masterHeader($callback = null) {
        $this->components[] = [
            'name' => 'masterHeader', 
            'args' => [$callback]
        ];
    }

    function _masterHeader($callback = null, $comIndex = null) {
        if($callback != null) {
            $callback($this);
        }
    }

    function masterBand($callback = null) {
        $this->components[] = [
            'name' => 'masterBand',
            'args' => [$callback]
        ];
    }

    function _masterBand($callback = null, $comIndex = null) {
        if($callback != null) {
            $pageBreak = $this->_detectPageBreak($callback, $comIndex);
            $masterStartIndex = $this->getNearestTopIndex('masterStart', $comIndex);
            $masterHeader = $this->getNearestTop('masterHeader', $comIndex, $masterStartIndex);
            if($pageBreak && $masterHeader != null) {
                $masterHeader['args'][0]($this);
            }

            $callback($this);
        }
    }

    function masterEnd() {
        $this->components[] = [
            'name' => 'masterEnd'
        ];
    }

    function _masterEnd() {}

    function _detectPageBreak($callback, $comIndex) {
        $isBreak = false;

        $this->startSimulation(true /** lock simulation */);

        $y = $this->GetY();
        if($callback != null) {
            $callback($this);
            $this->Ln();
        }
        $height = $this->GetY() - $y;

        $this->stopSimulation(true /** force unlock simulation */);

        $pageStart = $this->getNearestTop('pageStart', $comIndex);
        $pageHeight = $this->getPageHeight();
        if(($y + $height) > ($pageHeight - $pageStart['margins']['bottom'])) {
            $this->_pageFooter($comIndex);
            $this->AddPage();
            $this->_pageHeader($comIndex);
            $isBreak = true;
        }

        return $isBreak;
    }

    private $cols = [];

    function percentWidth($percent) {
        $margins = $this->getMargins();
        $width = $this->getPageWidth();
        $innerWidth = $width - $margins['left'] - $margins['right'];
        return (float)$percent / 100 * $innerWidth;
    }

    function getPageInnerWidth() {
        $margins = $this->getMargins();
        $width = $this->getPageWidth();
        return $width - $margins['left'] - $margins['right'];
    }

    function rowStart() {
        $this->cols = [];
        $this->startSimulation();
    }

    function column($txt, $options = [], $break = false) {
        $options = array_merge([
            'width' => 0,
            'height' => 0,
            'align' => 'L',
            'valign' => 'M',
            'multiline' => false,
            'border' => 0,
            'fill' => 0
        ], $options);

        if($this->simulation && !$this->simulationLock) { 
            $options['width'] = (strpos($options['width'], '%') === false ? ($options['width'] == 0 ? $this->getPageInnerWidth() : $options['width']) : $this->percentWidth($options['width']));
        }

        if($options['multiline'] == true) {
            $this->MultiCell(
                round($options['width'], 2), // width
                $options['height'], // height
                $txt, // content
                $options['border'], // border
                $options['align'], 
                $options['fill'], // fill
                $break ? 1 : 0 // break
            );
            if($this->simulation && !$this->simulationLock) $options['height'] = $this->GetLastH();
        } else {
            if(!$this->simulation) {
                $this->StartTransform();
                $this->Rect($this->GetX(), $this->GetY(), round($options['width'], 2), $options['height'], 'CNZ');
            }

            $this->Cell(
                round($options['width'], 2), // width
                $options['height'], // height
                $txt, // content
                $options['border'], // border
                $break ? 1 : 0, // break
                $options['align'], // align
                $options['fill'], // fill
                '', // link
                0, // stretch
                false, // ignore_min_height
                'T', // calign
                $options['valign'] // valign
            );

            if($this->simulation && !$this->simulationLock) $options['height'] = $this->GetLastH(); 
            if(!$this->simulation) {
                $this->StopTransform();
            }
        }

        if($this->simulation && !$this->simulationLock) {
            $this->cols[] = [$txt, $options, $break];
        }
    }

    function rowEnd() {
        $heightest = 0;
        foreach($this->cols as $col) {
            if($col[1]['height'] > $heightest) {
                $heightest = $col[1]['height'];
            }
        }

        $this->stopSimulation();

        if(!$this->simulation) {
            foreach($this->cols as $index => $col) {
                $args = $col;
                $args[1]['height'] = $heightest;
                if($index == count($this->cols) - 1) {
                    $args[2] = 1;
                }
                $this->column(...$args);
            }
        }
    }

    function _render() {
        foreach($this->components as $index => $component) {
            if(!in_array($component['name'], ['pageHeader', 'pageFooter'])) {
                call_user_func_array([$this, '_'.$component['name']], array_merge($component['args'] ?? [], [$index]));
            }
        }
    }

    function render($name = 'download.pdf', $mode = 'I') {
        $this->_render();
        $this->Output($name, $mode);
        die();
    }
}