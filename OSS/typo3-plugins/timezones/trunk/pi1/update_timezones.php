<?php
/********************************************
 HOW TO UPDATE TIMEZONES
 Install latest timezone RPM!!!
    => http://rpm.pbone.net/index.php3?stat=3&search=timezone&srodzaj=3
 Backup file offsetinfo.inc.php
 Make sure offsetinfo.inc.php is writable for yr webserver
 Call update_timezones.php in yr browser and wait to complete
 Done!
*********************************************/

// Path to zdump
$zdump = '/usr/bin/zdump';

// Load timezone information
include_once('timezones.inc.php');

echo "<pre>";

$outfp = fopen('offsetinfo.inc.php', 'w');
fputs($outfp, "<?php\n\n");
fputs($outfp, "\$GLOBALS['TX_TIMEZONES']['OFFSETS'] = array(\n");

foreach ($GLOBALS['TX_TIMEZONES']['TIMEZONES'] AS $zone => $props) {
	
	$output = array();
	exec("$zdump $zone -v", $output, $rc);
	if ($rc) {
		echo "Oops: $zone - rc=$rc\n";
	}

	fputs($outfp, "    '$zone' => array(\n");

	// JS file
	$outfpjs = fopen('../res/js/'.makeFilename($zone).'.js', 'w');
	fputs($outfpjs, "// Timezone definition for $zone\n");
	fputs($outfpjs, "var tx_timezone_id = '$zone';\n");
	fputs($outfpjs, "var tx_timezone_props = Array(\n");

	$start = array(0, false, false);
	$prev  = array(0, false, false);

	foreach ($output AS $line) {
		// Europe/Amsterdam  Sun Oct 25 01:00:00 2499 UTC = Sun Oct 25 02:00:00 2499 CET isdst=0 gmtoff=3600
		// split line at =
		$line = trim($line);
		list($utcinfo, $linfo) = split('=', $line, 2);

		// split off timezone info
		$utcinfo = trim(substr($utcinfo, strlen($zone)+1));
		$timestamp = strtotime($utcinfo);

		$L = split(' ', $linfo);
		foreach ($L AS $s) {
			$arr = split('=', $s, 2);
			if (count($arr) > 1) {
				$key = $arr[0];
				$value = $arr[1];
				if ($key == 'isdst') {
					$dst = $value;
				} else if ($key == 'gmtoff') {
					$offset = $value;
				}
			}
		}

		if ($timestamp > 0) {
			if ($start[1] === false) {
				$start = array(0, $dst, $offset);
				$prev  = array(0, $dst, $offset);
			}

			// has GMT offset changed?
			if ($prev[2] != $offset) {

				// print period now
				printLine($outfp, $zone, $start[0], $prev[0], $prev[1], $prev[2]);
				printJsLine($outfpjs, $zone, $start[0], $prev[0], $prev[1], $prev[2]);
				$start = array($timestamp, $dst, $offset);
			}

			$prev = array($timestamp, $dst, $offset);
		}
	}

	// Output last information
	printLine($outfp, $zone, $start[0], $prev[0], $prev[1], $prev[2]);
	fputs($outfp, "    ),\n");

	fputs($outfpjs, ");\n");
	fclose($outfpjs);
}

fputs($outfp, ");\n\n");
fputs($outfp, "?>\n");
fclose($outfp);

echo "</pre>";

function printLine($outfp, $zone, $start, $stop, $dst, $offset) {
	fputs($outfp, "        array($start, $stop, $dst, $offset),\n");
	echo "$zone: $start - $stop DST=$dst GMTOFFSET=$offset\n";
}

function printJsLine($outfp, $zone, $start, $stop, $dst, $offset) {
	fputs($outfp, "    Array($start, $stop, $dst, $offset),\n");
	echo "$zone: $start - $stop DST=$dst GMTOFFSET=$offset\n";
}

function makeFilename($zone) {
	return preg_replace('/[^A-Za-z0-9]/', '', $zone);
}

?>
