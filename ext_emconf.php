<?php

########################################################################
# Extension Manager/Repository config file for ext: "pt_xml2pdf"
#
# Auto generated 13-06-2008 16:13
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'XML 2 PDF Converter',
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
	'version' => '0.0.1dev',
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
	'_md5_values_when_last_written' => 'a:5:{s:9:"ChangeLog";s:4:"0239";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"1bdc";s:19:"doc/wizard_form.dat";s:4:"a567";s:20:"doc/wizard_form.html";s:4:"72c2";}',
);

?>