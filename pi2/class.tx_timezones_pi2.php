<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Ralph Schuster (rs.eschborn@gmx.de)
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
/**
 * Plugin 'Oventa Forgot Password' for the 'timezones' extension.
 *
 * @author	Ralph Schuster <rs.eschborn@gmx.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_timezones_pi2 extends tslib_pibase {
	var $prefixId = 'tx_timezones_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_timezones_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey = 'timezones';	// The extension key.
	var $orig_templateCode;

	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;


		$type = $this->cObj->data['select_key'];
		if (!$type) $type = $this->conf['select_key'];
		if (!$type) $type = 'info';

		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->setParent($this->cObj->data, $this->cObj->currentRecord);
		$this->orig_templateCode = $this->cObj->fileResource($conf["templateFile"]);

		if ($type == 'form') {
			$content = $this->getForm($cObj);
		} else {
			$content = $this->getInfo($cObj);
		}
		return $this->pi_wrapInBaseClass($content);
	}

	function getForm(&$cObj) {
		$template = $cObj->getSubpart($this->orig_templateCode, "###PI2_FORM###");

		$tz = tx_timezones_pi1::get_feuser_zone();
		$tzinfo = $GLOBALS['TX_TIMEZONES']['TIMEZONES'][$tz];
		$isdst = tx_timezones_pi1::isDST(time(), $tz) > 0 ? 2 : 1;
		$curdatetime = tx_timezones_pi1::formatDate($this->pi_getLL('date_format'));

		$markers = array(
			'###LABEL###' => $this->pi_getLL('form_label'),
			'###SELECT_LABEL###' => $this->pi_getLL('select_label'),
			'###SUBMIT###'   => $this->pi_getLL('submit'),
			'###TZ_LABEL###' => $tzinfo[0],
			'###TZ_NAME###'  => $tzinfo[$isdst],
			'###TZ_ID###'    => $tz,
			'###ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id, '', ''),
			'###CURDATETIME###' => $curdatetime,
			'###CURDATELABEL###' => $this->pi_getLL('curdate_label'),
			'###SELECTOR###' => tx_timezones_pi1::getTimezoneSelector(),
		);
		return $cObj->substituteMarkerArray($template, $markers);
	}

	function getInfo(&$cObj) {
		$template = $cObj->getSubpart($this->orig_templateCode, "###PI2_INFO###");

		$tz = tx_timezones_pi1::get_feuser_zone();
		$tzinfo = $GLOBALS['TX_TIMEZONES']['TIMEZONES'][$tz];
		$isdst = tx_timezones_pi1::isDST(time(), $tz) > 0 ? 2 : 1;

		$markers = array(
			'###LABEL###' => $this->pi_getLL('info_label'),
			'###TZ_LABEL###' => $tzinfo[0],
			'###TZ_NAME###'  => $tzinfo[$isdst],
			'###TZ_ID###'    => $tz,
			'###CHANGE_URL###' => $this->pi_getPageLink($this->conf['changePage'], '', ''),
		);
		return $cObj->substituteMarkerArray($template, $markers);
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timezones/pi2/class.tx_timezones_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timezones/pi2/class.tx_timezones_pi2.php']);
}

?>
