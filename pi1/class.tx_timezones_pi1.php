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
 * Plugin 'Timezone Library' for the 'timezones' extension.
 *
 * @author	Ralph Schuster <rs.eschborn@gmx.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

// Load timezones
require_once(t3lib_extMgm::extPath('timezones').'pi1/timezones.inc.php');

$GLOBALS['TX_TIMEZONES']['OFFSETS'] = array();
$GLOBALS['TX_TIMEZONES']['INIT'] = false;

class tx_timezones_pi1 extends tslib_pibase {
	var $prefixId = 'tx_timezones_pi1';
	var $scriptRelPath = 'pi1/class.tx_timezones_pi1.php';	
	var $extKey = 'timezones';
	
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		if (!$GLOBALS['TX_TIMEZONES']['INIT']) tx_timezones_pi1::initZones();

		$zone = tx_timezones_pi1::get_feuser_zone();

		// Set cookie anyway to user timezone
		setcookie('typo3_tx_timezone', $zone, time()+60*60*24*365, '/');

		// Update in FE user profile
		if ($GLOBALS['TSFE']->loginUser) {
			$fields = array(
				'tx_timezones_timezone' => $zone,
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users',
			       'uid='.$GLOBALS['TSFE']->fe_user->user['uid'], $fields);
		}

	}

	function initZones() {
		$GLOBALS['TX_TIMEZONES']['INIT'] = true;
		if (!isset($GLOBALS['TX_TIMEZONES']['DEFAULT_ZONE']))
			$GLOBALS['TX_TIMEZONES']['DEFAULT_ZONE'] = 'Europe/Amsterdam';
		$GLOBALS['TX_TIMEZONES']['CURRENT_ZONE'] = false;

		//$GLOBALS['TX_TIMEZONES']['DEFAULT_ZONE'] = $conf['default_zone'];

		// Create array of possibilities in order of precedence
		$tz = array();

		// Timezone is set by param
		$rc = t3lib_div::_GP('tx_timezones');
		if (is_array($rc) && isset($rc['timezone'])) {
			$tz[] = $rc['timezone'];
		}

		// FE user just logged in? Get value from user profile
		if ($GLOBALS['TSFE']->loginUser && (t3lib_div::_GP('logintype') == 'login')) {
			$tz[] = $GLOBALS['TSFE']->fe_user->user['tx_timezones_timezone'];
		}

		// timezone cookie
		if (isset($GLOBALS['_COOKIE']['typo3_tx_timezone'])) {
			$tz[] = $GLOBALS['_COOKIE']['typo3_tx_timezone'];
		}

		// FE user is coming? Get value from user profile
		if ($GLOBALS['TSFE']->loginUser) {
			$tz[] = $GLOBALS['TSFE']->fe_user->user['tx_timezones_timezone'];
		}

		// default timezone
		$tz[] = $GLOBALS['TX_TIMEZONES']['DEFAULT_ZONE'];

		// check all values now
		foreach ($tz AS $z) {
			if ($z && isset($GLOBALS['TX_TIMEZONES']['TIMEZONES'][$z])) {
				$GLOBALS['TX_TIMEZONES']['CURRENT_ZONE'] = $z;
				return;
			}
		}

		// Set timezone to GMT
		$GLOBALS['TX_TIMEZONES']['CURRENT_ZONE'] = 'Europe/London';
	}

	function get_feuser_zone() {
		if (!$GLOBALS['TX_TIMEZONES']['INIT']) tx_timezones_pi1::initZones();
		return $GLOBALS['TX_TIMEZONES']['CURRENT_ZONE'];
	}

	function formatDate($format, $timestamp = 0) {
		if (!$GLOBALS['TX_TIMEZONES']['INIT']) tx_timezones_pi1::initZones();

		if (!$timestamp) $timestamp = time();
		$offset = tx_timezones_pi1::getOffset($timestamp, tx_timezones_pi1::get_feuser_zone());
		$timestamp += $offset;

		// Hack: Maximum Date
		if ($timestamp > 2127483647) $timestamp = 2127483647;
		return gmdate($format, $timestamp);
	}

	function getTimezoneEntry($timestamp, $zone) {
		if (!$GLOBALS['TX_TIMEZONES']['INIT']) tx_timezones_pi1::initZones();
		if (!isset($GLOBALS['TX_TIMEZONES']['OFFSETS'][$zone])) {
			// Load offsets of this zone
			tx_timezones_pi1::loadOffsets($zone);
		}
		if (isset($GLOBALS['TX_TIMEZONES']['OFFSETS'][$zone])) {
			$offsets = $GLOBALS['TX_TIMEZONES']['OFFSETS'][$zone];
			// Search for correct entry
			foreach ($offsets AS $entry) {
				if (($timestamp >= $entry[0]) && ($timestamp <= $entry[1])) return $entry;
			}
		}
		return array(0, 2147483647, 0, 0);
	}

	function loadOffsets($zone) {
		$filename = preg_replace('/[^A-Za-z0-9]/', '', $zone).'inc.php';
		require_once(t3lib_extMgm::extPath('timezones').'res/'.$filename);
	}

	function getOffset($timestamp, $zone) {
		if (!$GLOBALS['TX_TIMEZONES']['INIT']) tx_timezones_pi1::initZones();
		$entry =  tx_timezones_pi1::getTimezoneEntry($timestamp, $zone);
		return $entry[3];
	}

	function isDST($timestamp, $zone) {
		if (!$GLOBALS['TX_TIMEZONES']['INIT']) tx_timezones_pi1::initZones();
		$entry =  tx_timezones_pi1::getTimezoneEntry($timestamp, $zone);
		return $entry[2];
	}

	function getTimezoneSelector($sel_zone = 'XXX', $name = "tx_timezones[timezone]") {
		if (!$GLOBALS['TX_TIMEZONES']['INIT']) tx_timezones_pi1::initZones();
		$tz = tx_timezones_pi1::get_feuser_zone();
		if ($sel_zone != 'XXX') {
			$tz = $sel_zone;
			if (!$tz) $tz = $GLOBALS['TX_TIMEZONES']['DEFAULT_ZONE'];
		}

		$rc = '<select name="'.$name.'">';
		foreach ($GLOBALS['TX_TIMEZONES']['TIMEZONES'] AS $zone => $props) {
			if ($tz == $zone) $sel = 'selected="selected"';
			else $sel = '';
			$rc .= "<option value=\"$zone\" $sel>$props[0]</option>\n";
		}
		$rc .= '</select>';
		return $rc;
	}

	function guessTimezone() {
		// whois must be installed: http://whois.sourceforge.net/
		// whois -h whois.ripe.net <client-ip>|grep country
		// TBD: mapping country code to timezone
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timezones/pi1/class.tx_timezones_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timezones/pi1/class.tx_timezones_pi1.php']);
}

?>
