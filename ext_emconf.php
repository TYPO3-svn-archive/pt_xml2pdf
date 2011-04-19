<?php

########################################################################
# Extension Manager/Repository config file for ext "pt_xml2pdf".
#
# Auto generated 11-04-2011 15:16
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'XML to PDF Converter',
	'description' => 'Creates PDF documents out of xml templates using FPDF',
	'category' => 'misc',
	'author' => 'Fabrizio Branca',
	'author_email' => 'branca@punkt.de',
	'shy' => '',
	'dependencies' => 'fpdf,pt_tools,smarty',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'punkt.de GmbH',
	'version' => '0.0.3dev',
	'constraints' => array(
		'depends' => array(
			'fpdf' => '0.1.2-',
			'pt_tools' => '',
			'smarty' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:10:{s:9:"ChangeLog";s:4:"0239";s:12:"ext_icon.gif";s:4:"5837";s:14:"doc/DevDoc.txt";s:4:"e42c";s:14:"doc/manual.sxw";s:4:"a0b6";s:19:"doc/wizard_form.dat";s:4:"a567";s:20:"doc/wizard_form.html";s:4:"72c2";s:30:"doc/samples/xml2pdf_sample.tpl";s:4:"c7de";s:42:"res/class.tx_ptxml2pdf_barcode_code128.php";s:4:"51a8";s:36:"res/class.tx_ptxml2pdf_generator.php";s:4:"e415";s:31:"res/class.tx_ptxml2pdf_main.php";s:4:"8d3e";}',
	'suggests' => array(
	),
);

?>