// Modify date object so it returns data according to timezone settings
function tx_timezones_adjust_date(d) {
	if (typeof tx_timezone_id !== 'undefined') {
		var utcOffset = d.getTimezoneOffset()*60;
		var utcTime = d.getTime();
		
		// Find the target offset
		var targetOffset = utcOffset;
		for (i = 0; i < tx_timezone_props.length; i++) {
			if ((tx_timezone_props[i][0]*1000 <= utcTime) && (tx_timezone_props[i][1]*1000 >= utcTime)) {
				targetOffset = 0 - tx_timezone_props[i][3];
			}
		}

		// Adjust the date object
		d.setTime(utcTime + (utcOffset-targetOffset)*1000);
	}
	return d;
}

