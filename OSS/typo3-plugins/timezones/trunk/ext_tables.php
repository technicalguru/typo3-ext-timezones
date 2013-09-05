<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$tempColumns = Array (
	"tx_timezones_timezone" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:timezones/locallang_db.php:fe_users.tx_timezones_timezone",		
		"config" => Array (
			"type" => "input",
			"size" => "25",
			"max" => "100",
			"eval" => "trim",
		)
	),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_timezones_timezone;;;;1-1-1");


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout';
t3lib_extMgm::addPlugin(Array('LLL:EXT:timezones/locallang_db.php:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi2/static/","Timezones Plugin");
if (TYPO3_MODE=="BE")   $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_timezones_pi2_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi2/class.tx_timezones_pi2_wizicon.php';


?>
