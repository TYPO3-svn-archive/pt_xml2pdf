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


require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_smartyAdapter.php';  // Smarty template engine adapter
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_smartyAdapter.php';
require_once t3lib_extMgm::extPath('pt_xml2pdf').'res/class.tx_ptxml2pdf_main.php';


/**
 * Xml2Pdf "Generator": Wizard for creating PDFs out of smarty templated xml files
 * 
 * @author	Fabrizio Branca <branca@punkt.de>
 * @since	2008-06-20
 */
class tx_ptxml2pdf_generator {
	
	/**
	 * @var string	Character set of the xml template file	
	 */
    protected $xmlCharSet = 'utf-8';

    /**
     * @var array	configuration array
     */
    protected $conf = array();
    
    /**
     * @var array	marker array
     */
    protected $markerArray = array();
    
    /**
     * @var string	file path to smarty template
     */
    protected $xmlSmartyTemplate;
    
    /**
     * @var string	xml content
     */
    protected $xml;
    
    /**
     * @var string	path to language file
     */
    protected $languageFile = '';
    
    /**
     * @var string	TYPO3 language key
     */
    protected $languageKey = '';    
    
    /**
     * @var string	output file path
     */
    protected $outputFile;
    
    /**
     * @var bool	if true, the xml file will be saved 
     */
    protected $saveXmlFile = false;
    
    
    /**
     * Sets the "saveXmlFile" flag
     *
     * @param 	bool	saveXmlFile flag
     * @return 	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-10-31
     */
    public function set_saveXmlFile($saveXmlFile) {
    	$this->saveXmlFile = $saveXmlFile;
    }
    
    /**
     * Gets the "saveXmlFile" flag
     *
     * @param	void
     * @return 	bool	saveXmlFile flag
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-10-31
     */
    public function get_saveXmlFile() {
    	return $this->saveXmlFile;
    }
    
    
    /**
     * Set xml smart template
     *
     * @param 	string	xml smarty template
     * @return 	tx_ptxml2pdf_generator $this
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-06-10
     */
    public function set_xmlSmartyTemplate($xmlSmartyTemplate) {
    	
    	tx_pttools_assert::isFilePath($xmlSmartyTemplate);
    	$this->xmlSmartyTemplate = t3lib_div::getFileAbsFileName($xmlSmartyTemplate);
    	
    	return $this;
    }
    
    
    
    /**
     * Get xml smarty template
     *
     * @return 	string	path to xml smarty template
     */
    public function get_xmlSmartyTemplate() {
    	
    	return $this->xmlSmartyTemplate;
    }
    
    
    
    /**
     * Set xml smart template
     *
     * @param 	string	language key
     * @return 	tx_ptxml2pdf_generator $this
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-06-10
     */
    public function set_languageKey($languageKey) {
    	
    	tx_pttools_assert::isNotEmptyString($languageKey);
    	$this->languageKey = $languageKey;
    	
    	return $this;
    }
    
    
    
    /**
     * Set xml character set
     *
     * @param 	string	xml smarty template
     * @return 	tx_ptxml2pdf_generator $this
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-06-10
     */
    public function set_xmlCharSet($xmlCharSet) {
    	
    	tx_pttools_assert::isNotEmptyString($xmlCharSet);
    	$this->xmlCharSet = $xmlCharSet;
    	
    	return $this;
    }
    
    
    
    /**
     * Get path to language file
     *
     * @return string   path to language file
     */
    public function get_languageFile() {
        
        return $this->languageFile;
    }
    
    
    
    
    /**
     * Set path to language file
     *
     * @param 	string	path to language file
     * @return 	tx_ptxml2pdf_generator $this
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-06-10
     */
    public function set_languageFile($languageFile) {
    	
    	tx_pttools_assert::isNotEmptyString($languageFile);
    	$this->languageFile = $languageFile;
    	
    	return $this;
    }
    
    
    
    /**
     * Returns the outputFile path
     *
     * @param 	void
     * @return 	string	output file path
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-10-31
     */
    public function get_outputFile() {
    	return $this->outputFile;
    }

    
    
    /**
     * Add additional markers
     *
     * @param  	array	additionalMarkers
     * @return 	tx_ptxml2pdf_generator	$this
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-06-10
     */
	public function addMarkers(array $additionalMarkers) {
		$this->markerArray = array_merge($this->markerArray, $additionalMarkers);

		if (TYPO3_DLOG) t3lib_div::devLog('In ' . __METHOD__, 'pt_xml2pdf', 0, array('markerArray' => $this->markerArray));
 
		return $this;
	}
	
	
    
    /**
     * Create XML 
     *
     * @param 	array	(optional) smartyConfiguration
     * @return 	tx_ptxml2pdf_generator	$this
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-06-10
     */
    public function createXml($smartyConfiguration = array()) {
        trace('[METHOD] '.__METHOD__);
    	
    	tx_pttools_assert::isNotEmptyString($this->xmlSmartyTemplate, array('message' => 'No "xmlSmartyTemplate" set!'));
    	tx_pttools_assert::isNotEmptyArray($this->markerArray, array('message' => 'No "markerArray" set!'));
    	tx_pttools_assert::isNotEmptyString($this->xmlCharSet, array('message' => 'No "xmlCharSet" set!'));
    	
    	$smartyConfiguration['error_reporting'] = 6135; // E_ALL ^ E_NOTICE // TODO: only for development
    	if (!empty($this->languageFile)) {
    		$smartyConfiguration['t3_languageFile'] = $this->languageFile;
    	}
    	if (!empty($this->languageKey)) {
    		$smartyConfiguration['t3_languageKey'] = $this->languageKey;
    	}
    	$smartyConfiguration['t3_charSet'] = $this->xmlCharSet;
    	
		trace($smartyConfiguration['compile_dir'],0, 'pt_xml2pdf compile_dir');
    	
    	$smarty = new tx_pttools_smartyAdapter($this, $smartyConfiguration); 
    	
        $smarty->left_delimiter = '<!--{';
        $smarty->right_delimiter = '}-->';
        
        // convert markerArray from site charset to xml charset
        if (!strcasecmp($siteCharset = tx_pttools_div::getSiteCharsetEncoding(), $this->xmlCharSet)) {
        	$this->markerArray = tx_pttools_div::iconvArray($this->markerArray, $siteCharset, $this->xmlCharSet) ;
        }   

        // encode XML entities
        array_walk_recursive($this->markerArray, array($this, 'encodeArrayCallback'));
        
        // Output some debug information
        if (TYPO3_DLOG) t3lib_div::devLog('In ' . __METHOD__ . ' setting Smarty params', 'pt_xml2pdf', 0, array('makerArray' => $this->markerArray));
        
        foreach ($this->markerArray as $markerKey => $markerValue) {
            $smarty->assign($markerKey, $markerValue);
        }
        
        $this->xml = $smarty->fetch($this->xmlSmartyTemplate);
        
        return $this;
    }

    /**
     * Wrapper for htmlspecialchars() to use as callback in array_walk_recursive()
     *
     * @param   string  string to encode
     * @return  string  encoded string
     * @author  Fabrizio Branca <branca@punkt.de>
     * @since   2008-10-17
     */
    public function encodeArrayCallback(&$value /*, $key*/) {
        
        return $value = htmlspecialchars($value);
        
    }

    /**
     * Renders xml to pdf
     *
     * @param 	string	path to outputfile
     * @param 	string	I (send to browser), 
     * 					D (send to browser, force download), 
     * 					F (local file), 
     * 					S (return as string) 
     * 					(see fpdf->output() for details), 
     * 					default is 'F' (local file)
     * @return 	mixed	return of fpdf->output() 
     * @throws  tx_pttools_exception	if assertions fail
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-06-10
     */
    public function renderPdf($outputFile, $destination = 'F') {
    	
    	tx_pttools_assert::isNotEmptyString($outputFile);
    	tx_pttools_assert::isNotEmptyString($this->xml);
    	tx_pttools_assert::isInList($destination, 'I,D,F,S', array('message' => '"'.$destination.'" is an unsupported destination (use I, D, F or S)!'));
    	
    	
    	$this->outputFile = t3lib_div::getFileAbsFileName($outputFile);
        $this->outputFile = str_replace(PATH_site, '', $this->outputFile);
    	
   		if ($this->saveXmlFile) {
    		$xmlPath = PATH_site.$this->outputFile.'.xml';
    		$result = @file_put_contents($xmlPath, $this->xml);
    		if ($result == false) {
    			throw new tx_pttools_exception(sprintf('Error while creating file "%s"', $xmlPath));
    		}
    	}
    	
        $pdf = new tx_ptxml2pdf_main($this->xml);

        // Add file path if file is saved to disc
        if ($destination == 'F') {
        	return $pdf->Output(PATH_site.$outputFile, $destination);
        } else {
        	return $pdf->Output($outputFile, $destination);
        }
        
    }
    
    
    
    /**
     * Returns the property value
     *
     * @param 	void
     * @return 	string
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-06-16
     */
    public function get_xml() {
    	return $this->xml;
    }
    

}

?>