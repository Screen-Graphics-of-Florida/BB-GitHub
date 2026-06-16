function filterToday(fld) {
	var field = fld.name;
	var str = document.getElementById(field).value;
	if (str) {
		var test = str.toUpperCase();
		test = test.substring(0, 5);
		if (test == 'TODAY') {
			return true;
		}
	}
}
