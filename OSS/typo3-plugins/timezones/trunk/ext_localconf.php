<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_timezones_pi1.php','_pi1','includeLib',0);





  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
        tt_content.CSS_editor.ch.tx_timezones_pi2 = < plugin.tx_timezones_pi2.CSS_editor
',43);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_timezones_pi2.php','_pi2','list_type',0);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_timezones_pi3.php','_pi3','list_type',0);
?>
