<?php
/***************************************************************
 *  Copyright notice
 *  
 *  (c) 2008 Fabrizio Branca (branca@punkt.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is 
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 * 
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 * 
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once t3lib_extMgm::extPath('pt_tools') . 'res/objects/class.tx_pttools_exception.php';
require_once t3lib_extMgm::extPath('pt_tools') . 'res/staticlib/class.tx_pttools_assert.php';
require_once t3lib_extMgm::extPath('fpdf') . 'fpdi.php';



/**
 * Xml2Pdf main class
 * 
 * @author	Fabrizio Branca <branca@punkt.de>
 * @since	2008-06-20
 */
class tx_ptxml2pdf_main extends FPDI {
	
	/**
	 * @var array	available templates "template name" => "template identifier"
	 */
	protected $templates;
	
	/**
	 * @var int		Height of the last command
	 */
	protected $lastheight;
	
	/**
	 * @var string	template key (in $this->templates) used for the first page
	 */
	protected $firstpageTemplate;
	
	/**
	 * @var string	template key (in $this->templates) used for odd pages
	 */
	protected $oddpagesTemplate;
	
	/**
	 * @var string	template key (in $this->templates) used for even pages
	 */
	protected $evenpagesTemplate;
	
	/**
	 * @var SimpleXMLElement Header for the first page
	 */
	protected $fistpageHeader;
	
	/**
	 * @var SimpleXMLElement Header for all odd pages
	 */
	protected $oddpagesHeader;
	
	/**
	 * @var SimpleXMLElement Header for all even pager
	 */
	protected $evenpagesHeader;
	
	/**
	 * @var SimpleXMLElement Footer for the first page
	 */
	protected $fistpageFooter;
	
	/**
	 * @var SimpleXMLElement Footer for all odd pages
	 */
	protected $oddpagesFooter;
	
	/**
	 * @var SimpleXMLElement Footer for all even pages
	 */
	protected $evenpagesFooter;
	
	/**
	 * @var float  Y Starting position of table on each page (for multi-page tables)
	 */
	protected $tableYStartPos;
	
	/**
     * @var float  X Starting position of table on each page (for multi-page tables)
     */
    protected $tableXStartPos;
    
    /**
     * @var float  Border on the bottom of a page
     */
    protected $bottomBorder;
    
    /**
     * @var float   Height of table currently rendered
     */
    protected $currTableHeight; 
	
	/**
	 * @var array  Debug output for table rendering
	 */
	protected $tablePosArray;
	
	/**
     * @var array  Debug output for table rendering
     */
    protected $currentTablePosArray;
    
    /**
     * @var array  Debug output for table rendering
     */
    protected $currentTableRowPosArray;
    
    /**
     * @var float   X position of current row
     */
    protected $currentRowXPos;
    
    /**
     * @var float Y position of current row
     */
    protected $currentRowYPos;
    
    /**
     * @var array   heights of currently processed cells
     */
    protected $currentCellHeights;
    
    /**
     * @var float   maximum height of currently processed cells
     */
    protected $maxCellHeight;
    
    /**
     * @var float   Heights of current row
     */
    protected $currentRowHeight;
    
    /**
     * @var float   x position of currently processed cell
     */
    protected $currentCellXPosition;
    
    /**
     * @var float   y position of next row
     */
    protected $newRowYPosition;
    
    /**
     * @var array   Array of positions for currently processed cells
     */
	protected $currentTableCellPosArray;
	
	/**
	 * @var float  x position of currently processed cell
	 */
	protected $currentCellXPos;
	
	/**
	 * @var float  y position of currently processed cell
	 */
	protected $currentCellYPos;
	
	/**
	 * @var float  Width of currently processed cell
	 */
	protected $currentCellWidth;
	
	/**
	 * @var float Height of currently processed cell
	 */
	protected $currentCellHeight;
	
	/**
	 * @var string  Align of currently processed cell
	 */
	protected $currentCellAlign;
	
	/**
	 * @var string  Background color of current cell
	 */
	protected $currentCellBgColor;
	
	/**
	 * @var bool   Should table border be drawn or not
	 */
	protected $drawTableBorder = true;
	
	/**
	 * @var string   Font style before table row
	 */
	protected $styleBeforeRow;
	
	/**
     * @var string   Font style before table cell
     */
    protected $styleBeforeCell;



	/**
	 * Constructor
	 *
	 * @example 
	 * 	new tx_ptxml2pdf_main('<document orientation="P" unit="mm" format="A4">[...]</document>');
	 * or
	 * 	new tx_ptxml2pdf_main('EXT:<myExt>/path/to/file.xml');
	 * or
	 *  new tx_ptxml2pdf_main($SimpleXMLElementObject);
	 * @param	string|SimpleXMLElement  XML String, path to xml file or SimpleXMLElement object
	 * @return	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-16
	 */
	public function __construct($xml) {

		if (is_string($xml)) {
			/**
			 * ATTENTION! $xml needs to be utf-8 encoded. Make sure all contents
			 * of your XML file are utf-8 encoded!
			 */
			trace($xml,0,'$xml');
			$xml = trim($xml);
			if ($xml[0] == '<') {
				if (($xmlObj = simplexml_load_string($xml)) == false) {
					throw new tx_pttools_exception('Error processing xml content');
				}
			} else {
				$filename = t3lib_div::getFileAbsFileName($xml);
				tx_pttools_assert::isFilePath($filename);
				if (($xmlObj = simplexml_load_file($filename)) == false) {
					throw new tx_pttools_exception('No valid xml file: ' . $filename);
				}
			}
		} elseif ($xml instanceof SimpleXMLElement) {
			$xmlObj = $xml;
		} else {
			throw new tx_pttools_exception('Invalid xml (use xml string, path to xml file or SimpleXMLElement object here!)');
		}
		trace($xmlObj,0,'$xmlObj');
		$attr = $this->getPlainAttributes($xmlObj);
		
		$pageFormat = $this->getPageFormat($attr['format']);
		
		parent::FPDI($attr['orientation'], $attr['unit'], $pageFormat);
		
		$this->bottomBorder = $attr['border_bottom'] > 0 ? $attr['border_bottom'] : 20;
		
		if (!empty($xmlObj->meta)) {
			$this->processMetadata($xmlObj->meta);
		}
		if (!empty($xmlObj->templates)) {
			$this->processTemplates($xmlObj->templates);
		}
		if (!empty($xmlObj->header)) {
			$this->processHeaders($xmlObj->header);
		}
		if (!empty($xmlObj->footer)) {
			$this->processFooters($xmlObj->footer);
		}
		if (!empty($xmlObj->content)) {
			$this->processContent($xmlObj->content);
		}
	}

	
	
	/**
	 * Returns a fpdf format for a given format string
	 * 
	 * @param  string  $formatString   Format string for FPDF configuration
	 * @return mixed                   FPDF format
	 * @author Michael Knoll <knoll@punkt.de>
	 * @since  2009-04-28
	 */
	protected function getPageFormat($formatString) {
		
		if (in_array($formatString, array('A3', 'A4', 'A5', 'Letter', 'Legal'))) {
			return $formatString;
		} elseif (preg_match('/(.+?)x(.+)/', $formatString, $matches)) {
			return array($matches[1], $matches[2]);
		} else {
			throw new tx_pttools_exception('No valid page format given: ' . $formatString . 
			    ' . Page format should be of form A4 A5... or <width>x<height>');
		}
		
	}
	


	/**
	 * Process meta data
	 *
	 * @param 	SimpleXMLElement $xmlObj
	 * @return	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-18
	 */
	protected function processMetadata(SimpleXMLElement $xmlObj) {

		$this->SetAuthor((string) $xmlObj->author);
		$this->SetCreator((string) $xmlObj->creator);
		$this->SetTitle((string) $xmlObj->title);
		$this->SetSubject((string) $xmlObj->subject);
		$this->SetKeywords((string) $xmlObj->keywords);
		
	}



	/**
	 * Process headers
	 *
	 * @param 	SimpleXMLElement $xmlObj
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-16
	 */
	protected function processTemplates(SimpleXMLElement $xmlObj) {

		foreach ($xmlObj->templates->children() as $template) { /* @var $template SimpleXMLElement */
			
			tx_pttools_assert::isEqual($template->getName(), 'template', array('message' => 'Only &lt; template filename="" page="" /&gt; nodes allowed'));
			
			$a = $this->getPlainAttributes($template);
			trace($a, 0, '$a plainAttributes');
			tx_pttools_assert::isNotEmptyString($a['filename'], array('message' => sprintf('No filename given!')));
			tx_pttools_assert::isFilePath($a['filename'], array('message' => sprintf('File "%s" not found!', $a['filename'])));
			
			$filename = t3lib_div::getFileAbsFileName($a['filename']);
			tx_pttools_assert::isFilePath($a['filename']);
			$pagecount = $this->setSourceFile($filename);
			$a['page'] = !empty($a['page']) ? $a['page'] : 1;
			if ($a['page'] <= $pagecount) {
				$this->templates[$a['name']] = $this->importPage($a['page']);
			} else {
				throw new tx_pttools_exception('Invalid page number', 0, 'PDF has only "' . $pagecount . '" pages. Trying to reqeust page "' . $a['page'] . '"');
			}
		}
		
		// Set templates
		if ($xmlObj->pagebackgrounds->allpages) {
			if ($xmlObj->pagebackgrounds->allpages->attributes()->template) {
				$a = $this->getPlainAttributes($xmlObj->pagebackgrounds->allpages);
				tx_pttools_assert::isNotEmptyString($a['template']);
				if ($GLOBALS['TT'] instanceof t3lib_timeTrack) $GLOBALS['TT']->setTSlogMessage('Setting "allpages template" with "'.$a['template'].'"');
				$this->firstpageTemplate = $this->oddpagesTemplate = $this->evenpagesTemplate = $a['template'];
			}
		} else {
			if (!empty($xmlObj->pagebackgrounds->firstpage->attributes()->template)) {
				$a = $this->getPlainAttributes($xmlObj->pagebackgrounds->firstpage);
				tx_pttools_assert::isNotEmptyString($a['template']);
				if ($GLOBALS['TT'] instanceof t3lib_timeTrack) $GLOBALS['TT']->setTSlogMessage('Setting "firstpage template" with "'.$a['template'].'"');
				$this->firstpageTemplate = $a['template'];
			}
			if (!empty($xmlObj->pagebackgrounds->oddpages->attributes()->template)) {
				$a = $this->getPlainAttributes($xmlObj->pagebackgrounds->oddpages);
				tx_pttools_assert::isNotEmptyString($a['template']);
				if ($GLOBALS['TT'] instanceof t3lib_timeTrack) $GLOBALS['TT']->setTSlogMessage('Setting "oddpages template" with "'.$a['template'].'"');
				$this->oddpagesTemplate = $a['template'];
			}
			if (!empty($xmlObj->pagebackgrounds->evenpages->attributes()->template)) {
				$a = $this->getPlainAttributes($xmlObj->pagebackgrounds->evenpages);
				tx_pttools_assert::isNotEmptyString($a['template']);
				if ($GLOBALS['TT'] instanceof t3lib_timeTrack) $GLOBALS['TT']->setTSlogMessage('Setting "evenpages template" with "'.$a['template'].'"');
				$this->evenpagesTemplate = $a['template'];
			}
		}		
		tx_pttools_assert::isNotEmptyString($this->firstpageTemplate, array('message' => 'No template for first page set.'));
		tx_pttools_assert::isNotEmptyString($this->oddpagesTemplate, array('message' => 'No template for odd pages set.'));
		tx_pttools_assert::isNotEmptyString($this->evenpagesTemplate, array('message' => 'No template for even pages set.'));
		
	}



	/**
	 * Process headers
	 *
	 * @param 	SimpleXMLElement 	headers
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-18
	 */
	protected function processHeaders(SimpleXMLElement $header) {

		if (!empty($header->allpages)) {
			$this->firstpageHeader = $this->oddpagesHeader = $this->evenpagesHeader = $header->allpages;
		}
		if (!empty($header->firstpage)) {
			$this->firstpageHeader = $header->firstpage;
		}
		if (!empty($header->oddpages)) {
			$this->oddpagesHeader = $header->oddpages;
		}
		if (!empty($header->evenpages)) {
			$this->evenpagesHeader = $header->evenpages;
		}
		
	}



	/**
	 * Process footers
	 *
	 * @param 	SimpleXMLElement 	footers
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-18
	 */
	protected function processFooters(SimpleXMLElement $footer) {
		
		if (!empty($footer->allpages)) {
			$this->firstpageFooter = $this->oddpagesFooter = $this->evenpagesFooter = $footer->allpages;
		}
		if (!empty($footer->firstpage)) {
			$this->firstpageFooter = $footer->firstpage;
		}
		if (!empty($footer->oddpages)) {
			$this->oddpagesFooter = $footer->oddpages;
		}
		if (!empty($footer->evenpages)) {
			$this->evenpagesFooter = $footer->evenpages;
		}
		
	}



	/**
	 * Process content
	 *
	 * @param 	SimpleXMLElement $xml
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-16
	 */
	protected function processContent(SimpleXMLElement $xmlObj) {

		foreach ($xmlObj->children() as $cmd) { /* @var $cmd SimpleXMLElement */
			
			$a = $this->getPlainAttributes($cmd);
			
			// remember height or set height from remembered height
			if (!empty($a['h'])) {
				$this->lastheight = $a['h'];
			} else {
				$a['h'] = $this->lastheight;
			}
			
			$c = trim($cmd);
			$c = html_entity_decode($c);
			$c = str_replace('\n', chr(10), $c);
			
			$replace = array('###CURRENTPAGENO###' => $this->PageNo());
			
			$c = str_replace(array_keys($replace), array_values($replace), $c);
			
			// converting to ISO-8859-15 (as fpdf doesn't understand utf-8)
			$c = iconv('UTF-8', 'ISO-8859-15//TRANSLIT//IGNORE', $c);
			
			// convert to the right euro symbol TODO: check if this conversion is needed an all fonts
			$c = str_replace(chr(164), chr(128), $c);
			
			// no html supported! some basic convertions
			$c = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $c);
			$c = strip_tags($c);
			
		    /* Special processing for tables */
            if ($cmd->getName() == 'table') {
                $this->processTable($a, $cmd);
                return;
            }
			
			$methodName = 'xml_' . strtolower($cmd->getName());
			
			// if (TYPO3_DLOG) t3lib_div::devLog('Processing command "'.$cmd->getName().'"', 'pt_xml2pdf', 1, array('attributes' => $a, 'content' => $c));
			
			if (method_exists($this, $methodName)) {
				$this->$methodName($a, $c);
			} else {
				throw new tx_pttools_exception('"' . $cmd->getName() . '" is an unknown command!');
			}
		
		}
	}

	
	
    /***************************************************************************
     * Table rendering processors
     ***************************************************************************/
    
    
	/**
	 * Process table rendering
	 * 
	 * @param  array               $a  Array of attributes for table tag
	 * @param  SimpleXMLElement    $c  Child nodes of table tag
	 * @return void
	 * @author Michael Knoll <knoll@punkt.de>
	 * @since  2009-04-21
	 */
	protected function processTable(array $a, $c) {
		
		$this->initTableProperties();
		$this->drawTableBorder = $a['border'] == '1' ? true : false;
		
		foreach ($c->children() as $row) { /* @var $row SimpleXMLElement */
			if ($row->getName() != 'tr') {
				throw new tx_pttools_exception('Error processing XML element: Child node of table was not tr but ' . $row->getName());
			}
			$a = $this->getPlainAttributes($row);
			$this->processTableRow($a, $row);
		}
		
		$this->logTablePositions();
		
	}
	
	
	
	/**
     * Process table row rendering
     * 
     * @param  array               $a  Array of attributes for row tag
     * @param  SimpleXMLElement    $c  Child nodes of row tag
     * @return void
     * @author Michael Knoll <knoll@punkt.de>
     * @since  2009-04-21
     */
	protected function processTableRow(array $a, $c) {
		
		$this->initRowProperties($a);
		$this->getCurrentMaxCellHeight($c);
		$this->addTablePageIfNeccessary();
		
		/* Helpers for drawing borders */
		$firstLine = true;
		$verticalBorderPositions = array();
		
		foreach ($c->children() as $cell) { /* @var $cell SimpleXMLElement */
			$verticalBorderPositions[] = $this->currentCellXPosition;
			if ($cell->getName() != 'td') {
				throw new tx_pttools_exception('Error processing XML element', 
				    'Error processing XML element: Child node of tr was not td but ' . $cell->getName());
			}
			$a = $this->getPlainAttributes($cell);
			$this->processTableCell($a, $cell);
			if ($this->currentCellHeight > $this->currentRowHeight) 
			    $this->currentRowHeight = $this->currentCellHeight;
            $this->currentCellXPosition += $a['width'];
			$this->SetXY($this->currentCellXPosition, $this->currentRowYPosition);
		}
		
        $this->newRowYPosition = $this->currentRowYPosition + $this->currentRowHeight;
        
        if ($this->drawTableBorder) {
        	
			$this->SetDrawColor(0);
            $this->SetLineWidth();
            
            // Draw first line
        	if ($firstLine) {
                $this->Line($this->tableXStartPos, $this->currentRowYPosition, $this->currentCellXPosition, $this->currentRowYPosition);   
                $firstLine = false;     		
        	}
        	
        	// Draw line under row
            $this->Line($this->tableXStartPos, $this->newRowYPosition, $this->currentCellXPosition, $this->newRowYPosition);
	        
	        // Draw vertical lines
	        $verticalBorderPositions[] = $this->currentCellXPosition;
	        foreach ($verticalBorderPositions as $borderXPosition) {
	        	$this->Line($borderXPosition, $this->newRowYPosition, $borderXPosition, $this->currentRowYPosition);
	        }
	        
        }
        
        $this->logRowPositions();
		$this->SetXY($this->currentRowXPosition, $this->newRowYPosition);
		$this->resetBeforeRowProperties();
		
	}
	
	
	
	/**
     * Process table cell rendering
     * 
     * @param  array               $a  Array of attributes for cell tag
     * @param  SimpleXMLElement    $c  Child nodes of cell tag
     * @return float                   Cell height
     * @author Michael Knoll <knoll@punkt.de>
     * @since  2009-04-21
     */
	protected function processTableCell($a, $c) {
		
		$c = iconv('UTF-8', 'ISO-8859-15', $c);
		
		$this->initCellProperties($a);
        $this->logCellSettingsBefore();
        if (intval($this->currentCellBgColor) > 0) {
        	$this->SetFillColor($this->currentCellBgColor);
        } else {
        	$this->SetFillColor(255);
        }
		if ($a['multi'] == 1) {
			// TODO make border configurable
		    $this->MultiCell(
		        $this->currentCellWidth,      // width of cell
		        $this->currentCellHeight,     // height of cell
		        $c,                           // content (text) of cell
		        0,                            // draw cell border?
		        $this->currentCellAlign,      // align of cell 
		        1                             // fill cell?
		    );
		} else {
			// TODO make border configurable
			$this->Cell(
			    $this->currentCellWidth,     // width of cell
			    $this->currentCellHeight,    // height of cell
			    $c,                          // content (text) of cell
			    0,                           // draw cell border?
			    0,                           // where to go after cell is rendered
			    $this->currentCellAlign,     // align of cell
			    1                            // fill cell?
			);
		}
		$this->SetFillColor(255);
		$this->determineCurrentCellHeight();
		$this->logCellPositionsAfter();
		
		// Reset styles to styles used before cell
		$this->resetBeforeCellProperties();
		
		
	}
	
	
	
	/**
	 * Helper method for logging cell position information
	 * 
	 * @return void
	 * @author Michael Knoll <knoll@punkt.de>
	 * @since  2009-04-24
	 */
	protected function logCellPositionsAfter() {
		
        $currentTableCellPosArray['cell_x_after'] = $this->GetX();
        $currentTableCellPosArray['cell_y_after'] = $this->GetY(); 
        $currentTableCellPosArray['return_y'] = $this->currentCellHeight;
        $this->currentTableRowPosArray[] = $currentTableCellPosArray;
		
	}
	
	
	
	/**
	 * Helper method for calculating cell heights
	 * 
     * @return void
     * @author Michael Knoll <knoll@punkt.de>
     * @since  2009-04-24
	 */
	protected function determineCurrentCellHeight() {
		
	    /* ATTENTION: multiline cells can have a pagebreak, so GetY() < currentYPos */
        if ($this->GetY() < $this->currentCellYPos) {
            /* Page break in cell */
            $this->currentCellHeight = $this->h - $this->bottomBorder - $this->currentCellYPos + $this->GetY() - $this->tableYStartPos;
        } else {
            /* No page break in cell */
            $this->currentCellHeight = $this->GetY() - $this->currentCellYPos;
        }
		
	}
	
	
	
	/**
	 * Helper methods for logging cell positions before rendering
	 * 
     * @return void
     * @author Michael Knoll <knoll@punkt.de>
     * @since  2009-04-24
	 */
	protected function logCellSettingsBefore() {
		
		$this->currentTableCellPosArray['cell_x_before'] = $this->currentCellXPos;
        $this->currentTableCellPosArray['cell_y_before'] = $this->currentCellYPos;
		
	}
	
	
	
	/**
	 * Helper method for initializing cell properties
	 * 
     * @return void
     * @author Michael Knoll <knoll@punkt.de>
     * @since  2009-04-24
	 */
	protected function initCellProperties($cellAttributes) {
		
        $this->currentTableCellPosArray = array();
        $this->currentCellXPos      = $this->GetX();
        $this->currentCellYPos      = $this->GetY();
        $this->currentCellWidth     = $cellAttributes['width'] > 0 ? $cellAttributes['width'] : 0;
        $this->currentCellHeight    = $cellAttributes['min_height'] > 0 ? $cellAttributes['min_height'] : 0;
        $this->currentCellAlign     = $cellAttributes['align'] != '' ? $cellAttributes['align'] : 'L';
        $this->currentCellBgColor   = $cellAttributes['bg_color'] != '' ? $cellAttributes['bg_color'] : 255;
        
        // Set some font properties
        $this->styleBeforeCell = $this->FontStyle;
        $this->SetFont('', $cellAttributes['style']);
		
	}
	
	
	
	/**
	 * Helper method for writing some row positions into devlog
	 * 
	 * @return void
	 * @author Michael Knoll <knoll@punkt.de>
	 * @since  2009-04-23
	 */
	protected function logRowPositions() {
		
		/* Generate some debug output */
        $this->currentTableRowPosArray['row_y_pos_aftercorrection'] = $this->newRowYPosition;
        $this->currentTableRowPosArray['page_height'] = $this->h;
        $this->currentTableRowPosArray['bottom_border'] = $this->bottomBorder;
        $this->currentTablePosArray[] = $this->currentTableRowPosArray;
        
	}
	
	
	
    /**
     * Helper method for adding a new page for a table, if page overflows
     * 
     * @return void
     * @author Michael Knoll <knoll@punkt.de>
     * @since  2009-04-23
     */
    protected function addTablePageIfNeccessary() {
        
        /* Check whether line should start on new page */
        if ( ($this->currentMaxCellHeight + $this->currentRowYPosition + 1) > ($this->h - $this->bottomBorder) ) {
            $this->AddPage();
            $this->currentRowYPosition = $this->tableYStartPos;
            $this->currentRowXPos = $this->tableXStartPos;
            $this->SetXY($this->tableXStartPos, $this->tableYStartPos);
        }
        
    }
    
    
    
    /**
     * Returns the maximum rendering height of an array of cells
     * 
     * @param   SimpleXMLElement    $rowElement     XML Element containing cells
     * @return  float                               Maximum height of cells
     * @author  Michael Knoll <knoll@punkt.de>
     * @since   2009-04-23
     */
    protected function getCurrentMaxCellHeight($rowElement) {
        
    	$this->currentCellHeights = array();
    	
        foreach ($rowElement->children() as $cell) {
            $a = $this->getPlainAttributes($cell);
            $this->currentCellHeights[] = $this->nbLines($a['width'], $cell) * $a['min_height'];
        }
        rsort($this->currentCellHeights,SORT_NUMERIC);
        $this->currentTableRowPosArray['cell_heights'] = $this->currentCellHeights;
        $this->currentTableRowPosArray['max_cell_heights'] = $this->currentMaxCellHeight;
        $this->currentMaxCellHeight = $this->currentCellHeights[0];
        
    }
    
    
    
    /**
     * Helper method for initializing row properties
     * 
     * @param  array    $rowAttributes  Attributes array for row
     * @return void 
     * @author Michael Knoll <knoll@punkt.de>
     * @since  2009-04-23
     */
    protected function initRowProperties($rowAttributes) {
        
        $this->currentTableRowPosArray = array();
        $this->currentRowXPosition = $this->GetX();
        $this->currentCellXPosition = $this->currentRowXPosition;
        $this->currentRowYPosition = $this->GetY();
        $this->currentTableRowPosArray['row_y_before'] = $this->currentRowYPosition;
        $this->currentTableRowPosArray['row_x_before'] = $this->currentRowXPosition;
        $currentFontHeight = $this->FontSize * 3 / 8 + 2;
        $this->currentRowHeight = $rowAttributes['min_height'] > 0 ? $rowAttributes['min_height'] : $currentFontHeight;
        
        // Set some font styles
        $this->styleBeforeRow = $this->FontStyle;
        $this->SetFont('', $rowAttributes['style']);
        
    }
    
    
    
    /**
     * Helper method for resetting properties to
     * 'before row styles'
     * 
     * @param  void
     * @return void
     * @author Michael Knoll <knoll@punkt.de>
     * @since  2009-09-01
     */
    protected function resetBeforeRowProperties() {
    	
    	$this->SetFont('', $this->styleBeforeRow);
    	
    }
    
    
    
    /**
     * Helper method for resetting properties to 
     * 'before cell styles'
     * 
     * @param  void
     * @return void
     * @author Michael Knoll <knoll@punkt.de>
     * @since  2009-09-01
     */
    protected function resetBeforeCellProperties() {
    	
    	$this->SetFont('', $this->styleBeforeCell);
    	
    }

    

    /**
     * Helper method for initializing table properties on object
     * 
     * @return void
     * @author Michael Knoll <knoll@punkt.de>
     * @since  2009-04-23
     */
    protected function initTableProperties() {
        
        $this->tableXStartPos = $this->GetX();
        $this->tableYStartPos = $this->GetY();
        $this->currTableHeight = $this->h - $this->bottomBorder - $this->tableYStartPos;
        $this->currentTablePosArray = array();
        
    }
    
    
    
    /**
     * Helper method for generating some log output
     * 
     * @return void
     * @author Michael Knoll <knoll@punkt.de>
     * @since  2009-04-23
     */
    protected function logTablePositions() {
        
        $this->tablePosArray[] = $this->currentTablePosArray;
        // TODO this can make MySQL crash...
        // if (TYPO3_DLOG) t3lib_div::devLog('Table Positions', 'pt_xml2pdf', 0, array('Positions' => $this->tablePosArray));
        
    }
	
	
	
	/**
	 * Computes the number of lines a MultiCell of width $w with content $txt will take
	 * 
	 * @param  float   $w      Width of cell
	 * @param  string  $txt    Content of cell
	 * @return int             Number of lines to be rendered
	 * @author "Olivier", Michael Knoll <knoll@punkt.de>
	 * @since  2009-04-23
	 * @see    http://www.fpdf.de/downloads/addons/3/
	 */
	protected function nbLines($w, $txt) {
		
	    $cw=&$this->CurrentFont['cw'];
	    if($w==0)
	        $w=$this->w-$this->rMargin-$this->x;
	    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	    $s=str_replace("\r", '', $txt);
	    $nb=strlen($s);
	    if($nb>0 and $s[$nb-1]=="\n")
	        $nb--;
	    $sep=-1;
	    $i=0;
	    $j=0;
	    $l=0;
	    $nl=1;
	    
	    while($i<$nb) {
	        $c=$s[$i];
	        if($c=="\n") {
	            $i++;
	            $sep=-1;
	            $j=$i;
	            $l=0;
	            $nl++;
	            continue;
	        }
	        if($c==' ')
	            $sep=$i;
	        $l+=$cw[$c];
	        if($l>$wmax) {
	            if($sep==-1) {
	                if($i==$j)
	                    $i++;
	            } else
	                $i=$sep+1;
	            $sep=-1;
	            $j=$i;
	            $l=0;
	            $nl++;
	        } else
	            $i++;
	    }
	    return $nl;
	    
	}
	


	/***************************************************************************
	 * OVERRIDING "FPDF" METHODS
	 **************************************************************************/
	
	/**
	 * Overwrite error method to throw exception instead of dying!
	 *
	 * @param 	string	message
	 * @return	void
	 * @throws	tx_pttools_exception
	 */
	public function Error($msg) {

		throw new tx_pttools_exception('FPDF error: ' . $msg);
	}



	/**
	 * Header, this method will be called directly after each page is started (on automatical page breaks, too)
	 *
	 * @param 	void
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-16
	 */
	public function Header() {

		// Background:
		if ($this->PageNo() === 1) {
			// first page
			if(!empty($this->templates[$this->firstpageTemplate])) {
				$this->useTemplate($this->templates[$this->firstpageTemplate]);
			}
		} elseif ($this->PageNo() % 2 == 0) {
			// even pages
			if(!empty($this->templates[$this->evenpagesTemplate])) {
				$this->useTemplate($this->templates[$this->evenpagesTemplate]);
			}
		} elseif ($this->PageNo() % 2 == 1) {
			// odd pages (without first page)
			if(!empty($this->templates[$this->oddpagesTemplate])) {
				$this->useTemplate($this->templates[$this->oddpagesTemplate]);
			}
		}
		
		// Header
		if ($this->PageNo() === 1 && !empty($this->firstpageHeader)) {
			// first page
			$this->processContent($this->firstpageHeader);
		} elseif ($this->PageNo() % 2 == 0 && !empty($this->evenpagesHeader)) {
			// even pages
			$this->processContent($this->evenpagesHeader);
		} elseif ($this->PageNo() % 2 == 1 && !empty($this->oddpagesHeader)) {
			// odd pages (without first page)
			$this->processContent($this->oddpagesHeader);
		}
	}



	/**
	 * Footer, this method will be called directly before an new page is started (on automatical page breaks, too)
	 *
	 * @param 	void
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	public function Footer() {

		// Background:
		if ($this->PageNo() === 1 && !empty($this->firstpageFooter)) {
			// first page
			$this->processContent($this->firstpageFooter);
		} elseif ($this->PageNo() % 2 == 0 && !empty($this->evenpagesFooter)) {
			// even pages
			$this->processContent($this->evenpagesFooter);
		} elseif ($this->PageNo() % 2 == 1 && !empty($this->oddpagesFooter)) {
			// odd pages (without first page)
			$this->processContent($this->oddpagesFooter);
		}
	}
	
	



	/***************************************************************************
	 * ADDING FUNCTIONALITY
	 **************************************************************************/

	/**
	 * Append pdf on new page
	 *
	 * @param 	string	path to pdf
	 * @param 	int		(optional) page to import, 0 = import all pages
	 * @param 	int		(optional) x position
	 * @param 	int		(optional) y position
	 * @param	int		(optional) width
	 * @param 	int		(optional) height
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-09-05
	 */
	public function appendPdfOnNewPage($pathToPdf, $page = 0, $_x=null, $_y=null, $_w=0, $_h=0) {
		
		$filename = t3lib_div::getFileAbsFileName($pathToPdf);
		tx_pttools_assert::isFilePath($filename);
		tx_pttools_assert::isIntegerish($page);

		$pagecount = $this->setSourceFile($filename);
		
		if ($page == 0) {
			// import all pages
			for ($i = 1; $i <= $pagecount; $i++) {
				$this->AddPage();
				$this->useTemplate($this->importPage($i), $_x, $_y, $_w, $_h);	
			}
		} elseif ($page <= $pagecount) {
			// import this page
			$this->AddPage();
			$this->useTemplate($this->importPage($page), $_x, $_y, $_w, $_h);	
		} else {
			throw new tx_pttools_exception('Trying to append page "'.$page.'" of a document with "'.$pagecount.'" pages');
		}
		
	}
	
	

	/**
	 * Set line width
	 *
	 * @param 	mixed	(optional) width or "default", default is "default"
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-16
	 */
	public function SetLineWidth($width = 'default') {

		if ($width == 'default') {
			$width = .567 / $this->k;
		}
		parent::SetLineWidth($width);
	}
	

	/**
	 * Generation of a "Code 128" barcode 
	 * 
	 * @param 	string	x-position of the upper left angle
	 * @param	string	y-position of the upper left angle
	 * @param 	string	width
	 * @param 	string	height
	 * @param 	string	code
	 * @author	Roland Gautier
	 * @since	2008-05-20
	 * @see 	http://www.fpdf.org/en/script/script88.php
	 */
	public function Code128($x, $y, $w, $h, $code) {
		
		require_once t3lib_extMgm::extPath('pt_xml2pdf') . 'res/class.tx_ptxml2pdf_barcode_code128.php';
		
		$code128 = new tx_ptxml2pdf_barcode_code128();
		
		$Aguid = ""; // Cr�ation des guides de choix ABC
		$Bguid = "";
		$Cguid = "";
		for($i = 0; $i < strlen($code); $i++) {
			$needle = substr($code, $i, 1);
			$Aguid .= ((strpos($code128->Aset, $needle) === FALSE) ? "N" : "O");
			$Bguid .= ((strpos($code128->Bset, $needle) === FALSE) ? "N" : "O");
			$Cguid .= ((strpos($code128->Cset, $needle) === FALSE) ? "N" : "O");
		}
		
		$SminiC = "OOOO";
		$IminiC = 4;
		
		$crypt = "";
		while ( $code > "" ) {
			// BOUCLE PRINCIPALE DE CODAGE
			$i = strpos($Cguid, $SminiC); // for�age du jeu C, si possible
			if ($i !== FALSE) {
				$Aguid[$i] = "N";
				$Bguid[$i] = "N";
			}
			
			if (substr($Cguid, 0, $IminiC) == $SminiC) { // jeu C
				$crypt .= chr(($crypt > "") ? $code128->JSwap["C"] : $code128->JStart["C"]); // d�but Cstart, sinon Cswap
				$made = strpos($Cguid, "N"); // �tendu du set C
				if ($made === FALSE) {
					$made = strlen($Cguid);
				}
				if (fmod($made, 2) == 1) {
					$made--; // seulement un nombre pair
				}
				for($i = 0; $i < $made; $i += 2) {
					$crypt .= chr(strval(substr($code, $i, 2))); // conversion 2 par 2
				}
				$jeu = "C";
			} else {
				$madeA = strpos($Aguid, "N"); // �tendu du set A
				if ($madeA === FALSE) {
					$madeA = strlen($Aguid);
				}
				$madeB = strpos($Bguid, "N"); // �tendu du set B
				if ($madeB === FALSE) {
					$madeB = strlen($Bguid);
				}
				$made = (($madeA < $madeB) ? $madeB : $madeA); // �tendu trait�e
				$jeu = (($madeA < $madeB) ? "B" : "A"); // Jeu en cours
				$jeuguid = $jeu . "guid";
				
				$crypt .= chr(($crypt > "") ? $code128->JSwap["$jeu"] : $code128->JStart["$jeu"]); // d�but start, sinon swap
				$crypt .= strtr(substr($code, 0, $made), $code128->SetFrom[$jeu], $code128->SetTo[$jeu]); // conversion selon jeu
			}
			$code = substr($code, $made); // raccourcir l�gende et guides de la zone trait�e
			$Aguid = substr($Aguid, $made);
			$Bguid = substr($Bguid, $made);
			$Cguid = substr($Cguid, $made);
		} // FIN BOUCLE PRINCIPALE
		
		$check = ord($crypt[0]); // calcul de la somme de contr�le
		for($i = 0; $i < strlen($crypt); $i++) {
			$check += (ord($crypt[$i]) * $i);
		}
		$check %= 103;
		
		$crypt .= chr($check) . chr(106) . chr(107); // Chaine Crypt�e compl�te

		$i = (strlen($crypt) * 11) - 8; // calcul de la largeur du module
		$modul = $w / $i;
		
		for($i = 0; $i < strlen($crypt); $i++) { // BOUCLE D'IMPRESSION
			$c = $code128->T128[ord($crypt[$i])];
			for($j = 0; $j < count($c); $j++) {
				$this->Rect($x, $y, $c[$j] * $modul, $h, "F");
				$x += ($c[$j++] + $c[$j]) * $modul;
			}
		}
	}



	/***************************************************************************
	 * HELPER METHODS
	 **************************************************************************/
	
	
	
	/**
	 * Returns an array of strings with the attributes of a SimpleXMLElement
	 *
	 * @param 	SimpleXMLElement xml object
	 * @return 	array
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected static function getPlainAttributes(SimpleXMLElement $xmlObj) {

		$plainAttributes = array();
		foreach ($xmlObj->attributes() as $key => $value) {
			$plainAttributes[$key] = (string) $value;
		}
		return $plainAttributes;
	}



	/***************************************************************************
	 * METHODS FOR PROCESSING XML TAGS (xml_*)
	 **************************************************************************/
	
	
	
	protected function xml_code128(array $a, $c) {
		
		$this->Code128($a['x'], $a['y'], $a['w'], $a['h'], $c);
	}
	
	/**
	 * Processes the "setheight" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_setheight(array $a, $c) {

		$this->lastheight = $a['h'];
	}



	/**
	 * Processes the "addmarks" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_addmarks(array $a, $c) {

		$this->SetLineWidth('default');
		// Falzmarken fuer A4
		$this->line(0, 105, 8, 105);
		$this->line(0, 210, 8, 210);
		
		// Lochmarke fuer A4
		$this->line(0, 148.5, 10, 148.5);
	}



	/**
	 * Processes the "addpage" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_addpage(array $a, $c) {

		if ($a['iflessthan'] == 0 || ($this->GetY() > (($this->hPt / $this->k) - $a['iflessthan']))) {
			$this->AddPage($a['orientation']);
		}
		
	}



	/**
	 * Processes the "line" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_line(array $a, $c) {

		if ($a['width']) {
			$this->SetLineWidth($a['width']);
		}
		if (!$a['x1']) {
			$a['x1'] = $this->GetX();
		}
		if (!$a['y1']) {
			$a['y1'] = $this->GetY();
		}
		if ($a['length']) {
			if (strtolower($a['orientation']) == 'v') {
				$a['x2'] = $a['x1'];
				$a['y2'] = $a['y1'] + $a['length'];
			} else {
				$a['x2'] = $a['x1'] + $a['length'];
				$a['y2'] = $a['y1'];
			}
		}
		if (!$a['x2']) {
			$a['x2'] = $a['x1'];
		}
		if (!$a['y2']) {
			$a['y2'] = $a['y1'];
		}
		$this->line($a['x1'], $a['y1'], $a['x2'], $a['y2']);
	}



	/**
	 * Processes the "cell" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_cell(array $a, $c) {

		if (!$a['y'])
			$a['y'] = $this->GetY();
		if ($a['x'] && $a['y'])
			$this->SetXY($a['x'], $a['y']);
		$this->Cell($a['w'], $a['h'], $c, $a['border'], $a['ln'], $a['align'], $a['fill'], $a['link']);
	}



	/**
	 * Processes the "SetAutoPageBreak" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-09-01
	 */
	protected function xml_setautopagebreak(array $a, $c) {

		if (empty($a['auto'])) {
			$a['auto'] = 1;
		}
		if (!empty($a['margin'])) {
			$this->SetAutoPageBreak($a['auto'], $a['margin']);
		} else {
			$this->SetAutoPageBreak($a['auto']);
		}
	}



	/**
	 * Processes the "multicell" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_multicell(array $a, $c) {

		if (!$a['y']) {
			$a['y'] = $this->GetY();
		}
		if ($a['x'] && $a['y']) {
			$this->SetXY($a['x'], $a['y']);
		}
		$this->MultiCell($a['w'], $a['h'], $c, $a['border'], $a['align'], $a['fill']);
	}



	/**
	 * Processes the "setmargins" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_setmargins(array $a, $c) {

		$this->SetMargins($a['left'], $a['top'], $a['right']);
	}



	/**
	 * Processes the "setfont" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_setfont(array $a, $c) {

		$this->SetFont($a['family'], $a['style'], $a['size']);
	}



	/**
	 * Processes the "setxy" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_setxy(array $a, $c) {

		if ($a['x'][0] == '+')
			$a['x'] = $this->GetX() + $a['x'];
		if ($a['x'][0] == '-')
			$a['x'] = $this->GetX() - $a['x'];
		if ($a['y'][0] == '+')
			$a['y'] = $this->GetY() + $a['y'];
		if ($a['y'][0] == '-')
			$a['y'] = $this->GetY() - $a['y'];
		$this->SetXY($a['x'], $a['y']);
	}



	/**
	 * Processes the "setx" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_setx(array $a, $c) {

		if ($a['x'][0] == '+')
			$a['x'] = $this->GetX() + $a['x'];
		if ($a['x'][0] == '-')
			$a['x'] = $this->GetX() - $a['x'];
		$this->SetX($a['x']);
	}



	/**
	 * Processes the "sety" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_sety(array $a, $c) {

		if ($a['y'][0] == '+')
			$a['y'] = $this->GetY() + $a['y'];
		if ($a['y'][0] == '-')
			$a['y'] = $this->GetY() - $a['y'];
		$this->SetY($a['y']);
	}



	/**
	 * Processes the "ln" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_ln(array $a, $c) {

		$this->Ln($a['h']);
	}



	/**
	 * Processes the "setlinewidth" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_setlinewidth(array $a, $c) {

		$this->SetLineWidth($a['width']);
	}



	/**
	 * Processes the "usetemplate" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_usetemplate(array $a, $c) {

		$this->useTemplate($this->templates[$a['template']], $a['x'], $a['y'], $a['w'], $a['h']);
	}



	/**
	 * Processes the "text" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_text(array $a, $c) {

		$this->text($a['x'], $a['y'], $c);
	}



	/**
	 * Processes the "write" command
	 *
	 * @example	<write h="5" link="http://www.punkt.de">Content</write>
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_write(array $a, $c) {

		$this->Write($a['h'], $c, $a['link']);
	}



	/**
	 * Processes the "settextcolor" command
	 *
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-20
	 */
	protected function xml_settextcolor(array $a, $c) {

		$this->SetTextColor($a['r'], $a['g'], $a['b']);
	}



	/**
	 * Processes the "image" command
	 *
	 * @example <image file="fileadmin/mypic.jpg" x="100" y="150" />
	 * @param 	array	attributes
	 * @param 	string	content
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2009-02-20
	 */
	protected function xml_image(array $a, $c) {
		tx_pttools_assert::isNotEmptyString($a['file'], array('message' => 'No "file" parameter given!'));
		tx_pttools_assert::isNotEmptyString($a['x'], array('message' => 'No "x" parameter given!'));
		tx_pttools_assert::isNotEmptyString($a['y'], array('message' => 'No "y" parameter given!'));

		if (!empty($a['type'])) {
			$a['type'] = strtoupper($a['type']);
			tx_pttools_assert::isInList($val, 'JPG,JPEG,PNG,GIF', array('message' => 'Type is not one of "JPG, JPEG, PNG, GIF"'));
		}
		$this->Image($a['file'], $a['x'], $a['y'], $a['w'], $a['h'], $a['type'], $a['link']);
	}
	
	
	
	/**
	 * Process the "setFillColor" command
	 * 
	 * @param 	array		$a		attributes
	 * @param 	string		$c		content
	 * @return 	void
	 * @author	Michael Knoll <knoll@punkt.de>
	 * @since	2009-04-06
	 */
	protected function xml_setfillcolor(array $a, $c) {
		tx_pttools_assert::isNotEmptyString($a['r'], array('message' => 'No "r" parameter for setfillcolor given!'));
		if (!empty($a['g'])) {
			// color mode defined by r,g,b
			tx_pttools_assert::isNotEmptyString($a['b'], array('message' => 'No "b" parameter for setfillcolor given!'));
			$this->SetFillColor($a['r'],$a['g'],$a['b']);
		} else {
			// gray scale mode, parameter r defines the gray tone
			$this->SetFillColor($a['r']);
		}
	}
	
	
	
	/**
	 * Writes current page number. In addition to the PageNo() function it
	 * directly writes the page number and not only returns it!
	 * 
	 * @param 	array		$a		attributes
	 * @param 	string		$c		content
	 * @return 	void
	 * @author	Michael Knoll <knoll@punkt.de>
	 * @since	2009-04-07
	 */
	protected function xml_pageno($a=array(), $c='') {
		
		$this->xml_write(array(), $this->PageNo());
		
	}
	
	
	
	/**
	 * Additional functionality: Output a page numbering
	 * 
	 * usage:
	 * <code>
	 * <pagenocell x="[x position]" w="[width of cell]" align="[alignment of cell]" label="[here comes your page label]" />
	 * </code>
	 * @param 	array		$a		attributes
	 * @param 	string		$c		content
	 * @return 	void
	 * @author	Michael Knoll <knoll@punkt.de>
	 * @since	2009-04-07
	 */
	protected function xml_pagenocell($a, $c) {
		
		$c = $a['label'] . $this->PageNo();
		$this->xml_cell($a, $c);
		
	}

}

?>