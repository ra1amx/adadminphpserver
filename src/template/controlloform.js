// Funzioni per la validazione dei formati
// ====================================================================================================

function testNumericoIntPos(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var re = /^\d+$/
	return(re.test(oggTextfield.value));
}

function testNumerico(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var re = /-?[0-9]+$/
	return(re.test(oggTextfield.value));
}

function testNumericoDecimale(oggTextfield, boolObbligatorio, decimali) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	if (oggTextfield.value == "-") return false;
	var re = /^\-?\d*(\.\d+)?$/
	return (re.test(oggTextfield.value))
}

function parseFloatString (v,d) {
	var x = parseFloat( !v ? 0 : v);
	x = parseFloat( Math.round(  x * Math.pow(10,d)  )  ) / Math.pow(10,d);
	y = x + "";
	if (y.indexOf(".")==-1) y = x + ".";
	var a = y.split(".");
	if (a[1].length<d) for(k=a[1].length;k<d;k++) a[1]+="0";
	y = a[0]+"."+a[1];
	return y;
}

function testDataAA(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var re = /^(\d{1,2})\/(\d{1,2})\/(\d{2})$/        // accetta anche 4/6/99
	if (!(re.test(oggTextfield.value))) return false
	var arrMatches = re.exec(oggTextfield.value)
	var giorno = parseInt(arrMatches[1],10)
	var mese = parseInt(arrMatches[2],10)
	var anno = parseInt(arrMatches[3],10)
	if (mese < 1 || mese > 12) return false
	var nGiorni;
	switch (mese) {
		case 4 : case 6 : case 9 : case 11 :
			nGiorni = 30
			break
		case 2 :
			if (anno % 4 == 0 && (anno % 1000 == 0 || anno % 100 != 0)) nGiorni = 29; else nGiorni = 28;
			break
		default :
			nGiorni = 31
	}
	if (giorno > nGiorni || giorno < 1) return false; else return true;
}

function testDataAAAAstr(stri) {
	stri = stri.replace(/\s+$|^\s+/g,"")
	var re = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/       // accetta anche 4/6/1999
	if (!(re.test(stri))) return false;
	var arrMatches = re.exec(stri)
	var giorno = parseInt(arrMatches[1],10)
	var mese = parseInt(arrMatches[2],10)
	var anno = parseInt(arrMatches[3],10)
	if (mese < 1 || mese > 12) return false;
	var nGiorni;
	switch (mese) {
		case 4 : case 6 : case 9 : case 11 :
			nGiorni = 30
			break
		case 2 :
			if (anno % 4 == 0 && (anno % 1000 == 0 || anno % 100 != 0)) nGiorni = 29; else nGiorni = 28;
			break
		default :
			nGiorni = 31
	}
	if (giorno > nGiorni || giorno < 1) return false; else return true;
}

function testDataAAAA(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var re = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/       // accetta anche 4/6/1999
	if (!(re.test(oggTextfield.value))) return false;
	var arrMatches = re.exec(oggTextfield.value)
	var giorno = parseInt(arrMatches[1],10)
	var mese = parseInt(arrMatches[2],10)
	var anno = parseInt(arrMatches[3],10)
	if (mese < 1 || mese > 12) return false;
	var nGiorni;
	switch (mese) {
		case 4 : case 6 : case 9 : case 11 :
			nGiorni = 30
			break
		case 2 :
			if (anno % 4 == 0 && (anno % 1000 == 0 || anno % 100 != 0)) nGiorni = 29; else nGiorni = 28;
			break
		default :
			nGiorni = 31
	}
	if (giorno > nGiorni || giorno < 1) return false; else return true;
}

function testData(o,b,formato) {
	if (formato=="gg-mm-aaaa") return testDataGgMmAaaa(o,b);
	if (formato=="aaaa-mm-gg") return testDataAaaaMmGg(o,b);
	return false;
}

function testDataOra(o,b,formato) {
	if (formato=="gg-mm-aaaa") return testDataGgMmAaaaHHii(o,b);
	if (formato=="aaaa-mm-gg") return testDataAaaaMmGgHHii(o,b);
	return false;
}

function testDataGgMmAaaa(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var re = /^(\d{1,2})\-(\d{1,2})\-(\d{4})$/       // accetta anche 4-6-1999
	if (!(re.test(oggTextfield.value))) return false
	var arrMatches = re.exec(oggTextfield.value);
	var giorno = parseInt(arrMatches[1],10)
	var mese = parseInt(arrMatches[2],10)
	var anno = parseInt(arrMatches[3],10)

	//aggiunta la possibilità di mettere date con 0 nei mesi e nei giorni
	if (mese < 0 || mese > 12) return false
	var nGiorni;
	switch (mese) {
		case 4 : case 6 : case 9 : case 11 :
			nGiorni = 30
			break
		case 2 :
			if (anno % 4 == 0 && (anno % 1000 == 0 || anno % 100 != 0)) nGiorni = 29; else nGiorni = 28;
			break
		default :
			nGiorni = 31
	}

	if (giorno > nGiorni || giorno < 0) return false;
	else{
		oggTextfield.value = (
				( (giorno<10) ? '0' + giorno : giorno ) + '-' + 
				( (mese<10) ? '0' + mese : mese) + '-' + 
				( anno )
			)
		return true
	}
}

function testDataGgMmAaaaHHii(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var re = /^(\d{1,2})\-(\d{1,2})\-(\d{4}) (\d{1,2}):(\d{1,2})$/       // accetta anche 4-6-1999
	if (!(re.test(oggTextfield.value))) return false;
	var arrMatches = re.exec(oggTextfield.value)
	var giorno = parseInt(arrMatches[1],10)
	var mese = parseInt(arrMatches[2],10)
	var anno = parseInt(arrMatches[3],10)
	var ore = parseInt(arrMatches[4],10)
	var minu = parseInt(arrMatches[5],10)

	//aggiunta la possibilità di mettere date con 0 nei mesi e nei giorni
	if (mese < 0 || mese > 12) return false;
	if (ore < 0 || ore > 24) return false;
	if (minu < 0 || minu > 59) return false;
	var nGiorni;
	switch (mese) {
		case 4 : case 6 : case 9 : case 11 :
			nGiorni = 30
			break
		case 2 :
			if (anno % 4 == 0 && (anno % 1000 == 0 || anno % 100 != 0)) nGiorni = 29; else nGiorni = 28;
			break
		default :
			nGiorni = 31
	}

	if (giorno > nGiorni || giorno < 0) return false;
	else{
		oggTextfield.value = (
				( (giorno<10) ? '0' + giorno : giorno ) + '-' + 
				( (mese<10) ? '0' + mese : mese) + '-' + 
				( anno ) + ' '  +
				( (ore<10) ? '0' + ore : ore) + ':' + 
				( (minu<10) ? '0' + minu: minu )
			)
		return true
	}
}



function testDataAaaaMmGg(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var re = /^(\d{4})\-(\d{1,2})\-(\d{1,2})$/       // accetta anche 4-6-1999
	if (!(re.test(oggTextfield.value))) return false;
	var arrMatches = re.exec(oggTextfield.value)
	var giorno = parseInt(arrMatches[3],10)
	var mese = parseInt(arrMatches[2],10)
	var anno = parseInt(arrMatches[1],10)

	//aggiunta la possibilità di mettere date con 0 nei mesi e nei giorni
	if (mese < 0 || mese > 12) return false;

	var nGiorni;

	switch (mese) {
		case 4 : case 6 : case 9 : case 11 :
			nGiorni = 30
			break
		case 2 :
			if (anno % 4 == 0 && (anno % 1000 == 0 || anno % 100 != 0)) nGiorni = 29; else nGiorni = 28;
			break
		default :
			nGiorni = 31
	}

	if (giorno > nGiorni || giorno < 0) return false;
	else {
		oggTextfield.value = (
			( anno ) + '-' + 
			( (mese<10) ? '0' + mese : mese) + '-' + 
			( (giorno<10) ? '0' + giorno : giorno )
		)
		return true
	}
		
}

function testDataAaaaMmGgHHii(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var re = /^(\d{4})\-(\d{1,2})\-(\d{1,2}) (\d{1,2}):(\d{1,2})$/       // accetta anche 4-6-1999
	if (!(re.test(oggTextfield.value))) return false;
	var arrMatches = re.exec(oggTextfield.value)
	var giorno = parseInt(arrMatches[3],10)
	var mese = parseInt(arrMatches[2],10)
	var anno = parseInt(arrMatches[1],10)
	var ore = parseInt(arrMatches[4],10)
	var minu = parseInt(arrMatches[5],10)
	//aggiunta la possibilità di mettere date con 0 nei mesi e nei giorni
	if (mese < 0 || mese > 12) return false;
	if (ore < 0 || ore > 24) return false;
	if (minu < 0 || minu > 59) return false;
	var nGiorni;
	switch (mese) {
		case 4 : case 6 : case 9 : case 11 :
			nGiorni = 30
			break
		case 2 :
			if (anno % 4 == 0 && (anno % 1000 == 0 || anno % 100 != 0)) nGiorni = 29; else nGiorni = 28;
			break
		default :
			nGiorni = 31
	}

	if (giorno > nGiorni || giorno < 0) return false;
	else {
		oggTextfield.value = (
			( anno ) + '-' + 
			( (mese<10) ? '0' + mese : mese) + '-' + 
			( (giorno<10) ? '0' + giorno : giorno ) + ' ' +
			( (ore<10) ? '0' + ore : ore) + ':' + 
			( (minu<10) ? '0' + minu: minu )
		)
		return true
	}
}


function testUrl(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var re = /^http://///g
	return(re.test(oggTextfield.value))
}


function testEmail(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var rex = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	var risultato = rex.test(oggTextfield.value);
	return risultato
}

function testRadio(oggRadio, boolObbligatorio) {
	for (var i=0; i<oggRadio.length; i++) if (oggRadio[i].checked) return true;
	if (boolObbligatorio) return false; else return true;
}

function testCheckbox(oggCheckbox, boolObbligatorio) {
	if ((!oggCheckbox.checked) && boolObbligatorio) return false; else return true;
}

function testCAP(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var re = /^\d{5}$/
	return(re.test(oggTextfield.value))
}

function testCodiceFiscale(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.toUpperCase().replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true;
	var re = /^[A-Z]{6}\d{2}[A-Z]\d{2}[A-Z]\d{3}[A-Z]$/
	return(re.test(oggTextfield.value))
}

function testAlfanumerico(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true; 
	var re = /^[a-zA-Z0-9]+$/
	return(re.test(oggTextfield.value))
}

function testCampoTesto(oggTextfield, boolObbligatorio) {
	oggTextfield.value = oggTextfield.value.replace(/\s+$|^\s+/g,"")
	if (oggTextfield.value == "") if (boolObbligatorio) return false; else return true; 
	return true
}

function testSerieDiCheckbox(oggCheckbox, boolObbligatorio) {
	for (var i=0; i<oggCheckbox.length; i++) if (oggCheckbox[i].checked) return true;
	if (boolObbligatorio) return false; else return true;
}

function testCombobox(oggComboBox, boolObbligatorio) {
	var valore = oggComboBox.options[oggComboBox.selectedIndex].value;
	if ((valore == "") && boolObbligatorio) return false; else return true;
}

function testComboboxMultiple(oggComboBox, boolObbligatorio) {
	if ((oggComboBox.selectedIndex == -1) && boolObbligatorio) return false; else return true;
}

function trim(str) {
	// Trimma gli eventuali spazi all'inizio e alla fine della stringa e trasforma tutti gli spazi doppi in spazi singoli
	return str.replace(/\s+/g," ").replace(/^\s+/,"").replace(/\s+$/,"")
}

// ====================================================================================================
