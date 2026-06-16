
function editFromTo(fromField, toField, fieldType) {
	if (fieldType == "A") {
		fromValue = fromField.value.toUpperCase();
		toValue = toField.value.toUpperCase();
	} else if (fieldType == "D") {
		fromValue = fromField.value;
		toValue = toField.value;
		fcc = 0;
		fyy = fromValue.substring(4,6);
		fmd = fromValue.substring(0,4);
		tcc = 0;
		tyy = toValue.substring(4,6);
		tmd = toValue.substring(0,4);
		if (fyy>=0 && fyy<=39) {fcc=1;}
		if (tyy>=0 && tyy<=39) {tcc=1;}
		fromValue = fcc + fyy + fmd;
		toValue   = tcc + tyy + tmd;
	} else if (fieldType > "0") {
		fromValue = fromField.value;
		toValue = toField.value;
		for (var i=fromValue.length; i<fieldType; i++) {fromValue = "0" + fromValue;}
		for (var i=toValue.length; i<fieldType; i++)   {toValue = "0" + toValue;}
	}

	if (fromValue > toValue) {
		alert("From value greater than to value");
		fromField.focus();
		fromField.select();
		return false;
	} else {return true;}
}

function editFromToAll(fromField, toField, allField, fieldType) {
	if (fieldType == "A") {
		fromValue = fromField.value.toUpperCase();
		toValue = toField.value.toUpperCase();
	} else if (fieldType == "D") {
		fromValue = fromField.value;
		toValue = toField.value;
		fcc = 0;
		fyy = fromValue.substring(4,6);
		fmd = fromValue.substring(0,4);
		tcc = 0;
		tyy = toValue.substring(4,6);
		tmd = toValue.substring(0,4);
		if (fyy>=0 && fyy<=39) {fcc=1;}
		if (tyy>=0 && tyy<=39) {tcc=1;}
		fromValue = fcc + fyy + fmd;
		toValue   = tcc + tyy + tmd;
	} else if (fieldType == "P" || fieldType == "DP") {
		fromValue = fromField.value;
		toValue = toField.value;
		fcc = 0; fyy = 0; fpp = 0;
		if (fromValue > "0") {
			for (var i=fromValue.length; i<4; i++) {fromValue = "0" + fromValue;}
			fyy = fromValue.substring(2,4);
			fpp = fromValue.substring(0,2);
			if (fyy>=0 && fyy<=39) {fcc=1;}
		}
		tcc = 0; tyy = 0; tpp = 0;
		if (toValue > "0") {
			for (var i=toValue.length; i<4; i++)   {toValue = "0" + toValue;}
			tyy = toValue.substring(2,4);
			tpp = toValue.substring(0,2);
			if (tyy>=0 && tyy<=39) {tcc=1;}
		}
		fromValue = fcc + fyy + fpp;
		toValue   = tcc + tyy + tpp;
	} else if (fieldType > "0") {
		fromValue = fromField.value;
		toValue = toField.value;
		for (var i=fromValue.length; i<fieldType; i++) {fromValue = "0" + fromValue;}
		for (var i=toValue.length; i<fieldType; i++)   {toValue = "0" + toValue;}
	}

	if (allField.checked && (fromField.value > "" || toField.value > "")) {
		alert("From and to values are not valid with all selection");
		fromField.focus();
		fromField.select();
		return false;
	} else if (fromValue > toValue) {
		alert("From value greater than to value");
		fromField.focus();
		fromField.select();
		return false;
	} else if (fromField.value == "" && toField.value == "" && allField.checked == false) {
		alert("From to and all selections are all blank");
		fromField.focus();
		fromField.select();
		return false;
	} else {return true;}
}

function editFromToAll2(fromField1, fromField2, toField1, toField2, allField, fieldType1, fieldType2) {
	if (fieldType1 == "A") {
		fromValue1 = fromField1.value.toUpperCase();
		toValue1 = toField1.value.toUpperCase();
	} else if (fieldType1 == "D") {
		fromValue1 = fromField1.value;
		toValue1 = toField1.value;
		fcc = 0; fyy = fromValue1.substring(4,6); fmd = fromValue1.substring(0,4);
		tcc = 0; tyy = toValue1.substring(4,6); tmd = toValue1.substring(0,4);
		if (fyy>=0 && fyy<=39) {fcc=1;}
		if (tyy>=0 && tyy<=39) {tcc=1;}
		fromValue1 = fcc + fyy + fmd;
		toValue1   = tcc + tyy + tmd;
	} else if (fieldType1 == "P" || fieldType1 == "DP") {
		fromValue1 = fromField1.value;
		toValue1 = toField1.value;
		fcc = 0; fyy = 0; fpp = 0;
		if (fromValue1 > "0") {
			for (var i=fromValue1.length; i<4; i++) {fromValue1 = "0" + fromValue1;}
			fyy = fromValue1.substring(2,4); fpp = fromValue1.substring(0,2);
			if (fyy>=0 && fyy<=39) {fcc=1;}
		}
		tcc = 0; tyy = 0; tpp = 0;
		if (toValue1 > "0") {
			for (var i=toValue1.length; i<4; i++)   {toValue1 = "0" + toValue1;}
			tyy = toValue1.substring(2,4); tpp = toValue1.substring(0,2);
			if (tyy>=0 && tyy<=39) {tcc=1;}
		}
		fromValue1 = fcc + fyy + fpp;
		toValue1   = tcc + tyy + tpp;
	} else if (fieldType1 > "0") {
		fromValue1 = fromField1.value;
		toValue1 = toField1.value;
		for (var i=fromValue1.length; i<fieldType1; i++) {fromValue1 = "0" + fromValue1;}
		for (var i=toValue1.length; i<fieldType1; i++)   {toValue1 = "0" + toValue1;}
	}

	if (fieldType2 == "A") {
		fromValue2 = fromField2.value.toUpperCase();
		toValue2 = toField2.value.toUpperCase();
	} else if (fieldType2 == "D") {
		fromValue2 = fromField2.value;
		toValue2 = toField2.value;
		fcc = 0; fyy = fromValue2.substring(4,6); fmd = fromValue2.substring(0,4);
		tcc = 0; tyy = toValue2.substring(4,6); tmd = toValue2.substring(0,4);
		if (fyy>=0 && fyy<=39) {fcc=1;}
		if (tyy>=0 && tyy<=39) {tcc=1;}
		fromValue2 = fcc + fyy + fmd;
		toValue2   = tcc + tyy + tmd;
	} else if (fieldType2 == "P" || fieldType2 == "DP") {
		fromValue2 = fromField2.value;
		toValue2 = toField2.value;
		fcc = 0; fyy = 0; fpp = 0;
		if (fromValue2 > "0") {
			for (var i=fromValue2.length; i<4; i++) {fromValue2 = "0" + fromValue2;}
			fyy = fromValue2.substring(2,4); fpp = fromValue2.substring(0,2);
			if (fyy>=0 && fyy<=39) {fcc=1;}
		}
		tcc = 0; tyy = 0; tpp = 0;
		if (toValue2 > "0") {
			for (var i=toValue2.length; i<4; i++)   {toValue2 = "0" + toValue2;}
			tyy = toValue2.substring(2,4); tpp = toValue2.substring(0,2);
			if (tyy>=0 && tyy<=39) {tcc=1;}
		}
		fromValue2 = fcc + fyy + fpp;
		toValue2   = tcc + tyy + tpp;
	} else if (fieldType2 > "0") {
		fromValue2 = fromField2.value;
		toValue2 = toField2.value;
		for (var i=fromValue2.length; i<fieldType2; i++) {fromValue2 = "0" + fromValue2;}
		for (var i=toValue2.length; i<fieldType2; i++)   {toValue2 = "0" + toValue2;}
	}

	if (allField.checked && (fromField1.value > "" || fromField2.value > "" || toField1.value > "" || toField2.value > "")) {
		alert("From and to values are not valid with all selection");
		fromField1.focus();
		fromField1.select();
		return false;
	} else if (fromValue1 > toValue1 || fromValue1==toValue1 && fromValue2 > toValue2) {
		alert("From value greater than to value");
		fromField1.focus();
		fromField1.select();
		return false;
	} else if (fromField1.value == "" && fromField2.value == "" && toField1.value == "" && toField2.value == "" && allField.checked == false) {
		alert("From to and all selections are all blank");
		fromField1.focus();
		fromField1.select();
		return false;
	} else {return true;}
}

function editFromToOper(fromField, toField, operField, fieldType) {
	if (operField.value != "BETWEEN") {return true;}
	else {
		if (fieldType == "A") {
			fromValue = fromField.value.toUpperCase();
			toValue = toField.value.toUpperCase();
		} else if (fieldType == "D") {
			fromValue = fromField.value;
			toValue = toField.value;
			fcc = 0;
			fyy = fromValue.substring(4,6);
			fmd = fromValue.substring(0,4);
			tcc = 0;
			tyy = toValue.substring(4,6);
			tmd = toValue.substring(0,4);
			if (fyy>=0 && fyy<=39) {fcc=1;}
			if (tyy>=0 && tyy<=39) {tcc=1;}
			fromValue = fcc + fyy + fmd;
			toValue   = tcc + tyy + tmd;
		} else if (fieldType == "P" || fieldType == "DP") {
			fromValue = fromField.value;
			toValue = toField.value;
			fcc = 0; fyy = 0; fpp = 0;
			if (fromValue > "0") {
				for (var i=fromValue.length; i<4; i++) {fromValue = "0" + fromValue;}
				fyy = fromValue.substring(2,4);
				fpp = fromValue.substring(0,2);
				if (fyy>=0 && fyy<=39) {fcc=1;}
			}
			tcc = 0; tyy = 0; tpp = 0;
			if (toValue > "0") {
				for (var i=toValue.length; i<4; i++)   {toValue = "0" + toValue;}
				tyy = toValue.substring(2,4);
				tpp = toValue.substring(0,2);
				if (tyy>=0 && tyy<=39) {tcc=1;}
			}
			fromValue = fcc + fyy + fpp;
			toValue   = tcc + tyy + tpp;
		} else if (fieldType > "0") {
			fromValue = fromField.value;
			toValue = toField.value;
			for (var i=fromValue.length; i<fieldType; i++) {fromValue = "0" + fromValue;}
			for (var i=toValue.length; i<fieldType; i++)   {toValue = "0" + toValue;}
		}

		if (fromValue > toValue) {
			alert("From value greater than to value");
			fromField.focus();
			fromField.select();
			return false;
		} else {return true;}
	}
}

function editFromToOper2(fromField1, fromField2, toField1, toField2, operField, fieldType1, fieldType2) {
	if (operField.value != "BETWEEN") {return true;}
	else {
		if (fieldType1 == "A" || fieldType2 == "A") {
			fromValue1 = fromField1.value.toUpperCase();
			fromValue2 = fromField2.value.toUpperCase();
			toValue1 = toField1.value.toUpperCase();
			toValue2 = toField2.value.toUpperCase();
		} else if (fieldType1 > 0 || fieldType2 > 0) {
			fromValue1 = fromField1.value;
			fromValue2 = fromField2.value;
			toValue1 = toField1.value;
			toValue2 = toField2.value;
			for (var i=fromValue1.length; i<fieldType1; i++) {fromValue1 = "0" + fromValue1;}
			for (var i=fromValue2.length; i<fieldType2; i++) {fromValue2 = "0" + fromValue2;}
			for (var i=toValue1.length; i<fieldType1; i++)   {toValue1 = "0" + toValue1;}
			for (var i=toValue2.length; i<fieldType2; i++)   {toValue2 = "0" + toValue2;}
		}

		if (fromValue1 > toValue1 || fromValue1==toValue1 && fromValue2 > toValue2) {
			alert("From value greater than to value");
			fromField1.focus();
			fromField1.select();
			return false;
		} else {return true;}
	}
}

