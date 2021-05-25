var t = document.location.href.split("/");
var PONSDIR = "";
for (var i=0; i<t.length; i++) { if (t[i]=="src") break; else PONSDIR += t[i] + "/"; }
//PONSDIR = PONSDIR.substring(0, PONSDIR.length-1);

var $language = "$en"; // $en o $it
var $it = {
	
	'NOTICE': 'AVVISO',
	'Close' : 'Chiudi',
	'OK' : 'OK',
	'CANCEL' : 'ANNULLA',
	'Are you sure to delete the file now?' : 'Sei sicuro di cancellare il file ora?',
	"Do you confirm to delete the item?" : 'Confermi la cancellazione?',
	"You don't have selected any item to delete." : 'Non hai selezionato nulla.',
	"Do you confirm to delete the selected items?" : 'Cancello i selezionati?'

};

function _e( $s ) {
	// funzione per traduzioni
	obj = $it;
	if (($s in obj)) {
		if ($language == "$it") return obj[$s]; else return $s;
	} else {
		return $s + "!";
	}
}


jQuery(document).ready(function($){
	//...qui...

	loadMenu();

	$.fn.hasAttr = function(name) {  
	   return this.attr(name) !== undefined;
	};

	$("legend a").each(function(){
		if($(this).hasAttr("rel")) {
			$(this).click(function(e){
				a = $(this);
				e.preventDefault;
				var v = a.attr("rel").split("|");
				show(v[1]);
				if(a.hasAttr("data-rel")) {
					a.html(a.attr("data-rel"));
					a.removeAttr("data-rel");
				} else {
					a.attr("data-rel",a.html());
					a.html(v[0]);
				}
			});
		}
	});

	$("table.griglia tbody tr:nth-child(odd)").addClass("odd");
	$("table.griglia tbody tr:nth-child(even)").addClass("even");

	$("input[type=text],input[type=password],textarea,select").focus(function(){
		$(this).addClass("focus");
	});
	$("input[type=text],input[type=password],textarea,select").blur(function(){
		$(this).removeClass("focus");
	});

	$("input[type=text]").each(function(){
		if($(this).attr("name").indexOf("_mm")!=-1) $(this).css("background-image","none");
		if($(this).attr("name").indexOf("_gg")!=-1) $(this).css("background-image","none");
		if($(this).attr("name").indexOf("_aaaa")!=-1) $(this).css("background-image","none");
	});


} );


// ----------------------------------------------------------------------------------------------
var layerone =0;
function loadMenu() {
	if($('#loginform').length==0) {
		var m = $('#mainmenu').length ? $('#mainmenu') : $('<div id="mainmenu"></div>').appendTo("body");
		$("#mainmenu").load(PONSDIR +"src/_include/ajax.menu.php", null, function(){
			$('.linkmenu0').each(function(){$(this).attr("href","javascript:;");});
			$('.linkmenu0').click(function(e){
				layerone=1;
				console.log("click");
				e.preventDefault();
				show($(this).attr("data-rel"));
				e.stopPropagation();
			});

			$('#mainmenu').mouseenter(function(e){
				if(!$(this).hasClass("on")) {
					$(this).addClass("on");
					console.log("entro");
					//$('body').append("<div id='modalContainer0'></div>");
				}
			});
			$('#mainmenu').mouseleave(function(e){
				if($(this).hasClass("on") && layerone==0) {
					var v= this;
					console.log("esco");
					/*$("#modalContainer0").fadeTo(200,0,function(){$(this).remove();
						$(v).removeClass("on");
					});*/
					$(this).removeClass("on");
									}
				layerone =0;
			});

		});
	}
}



//-----------------------------------------------------------------------------------------------
function gup( name ) { /* prende i parametri da una querystring get */
	name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var results = regex.exec( window.location.href );
	if( results == null ) return ""; else return results[1];
}

function urlencode(strText) {
	var isObj;
	var trimReg;
	if( typeof(strText) == "string" ) {
		if( strText != null ) {
			trimReg = /(^\s+)|(\s+$)/g;
			strText = strText.replace( trimReg, '');
			for(i=32;i<256;i++) {
				strText = strText.replace(String.fromCharCode(i),escape(String.fromCharCode(i)));
			}
		}
	}
	return strText;
}


/* mostra/nasconde un div, è un toggle, funziona con jquery */
function show(nomediv) { 
	if(typeof($)=="undefined") {
		document.getElementById(nomediv).style.display='block';
	} else $('#'+nomediv).toggle("slow");
}
/* mostra/nasconde un div con effetto, funziona con jquery o prototype o niente */
function showfade(nomediv) { show(nomediv); }


//--------------------------------------------------------------------------------
// this function is needed to work around 
// a bug in IE related to element attributes
function hasClass(obj) {
	var result = false;
	if (obj.getAttributeNode("class") != null) {
	result = obj.getAttributeNode("class").value;
	}
	return result;
}


//
// per Associator (controllo del form aggiunto da serini)
//
function associator(prefix) {
	
	this.prefix = prefix;
	this.pending = $('#'+prefix+'pending');
	this.recipient = $('#'+prefix+'recipient');
	this.hidrec = $('#'+prefix+'recval');
	this.hidpen = $('#'+prefix+'pendval');

	this.toggle = function(obj) {
		var appoggio = obj.parentNode;
		if (obj.parentNode.parentNode.id.indexOf('pending')!=-1)
		{
			this.pending.removeChild(obj.parentNode);
			this.recipient.appendChild(appoggio);
			this.hidrec.value=this.hidrec.value+obj.id+',';
			this.hidpen.value=this.hidpen.value.replace(obj.id+',','');
		}
		else
		{
			this.recipient.removeChild(obj.parentNode);
			this.pending.appendChild(appoggio);
			this.hidpen.value=this.hidpen.value+obj.id+',';
			this.hidrec.value=this.hidrec.value.replace(obj.id+',','');
		}
	}
}

//
// da utilizzare per attivare il submit sull'invio di qualche form
// perche' se una parte del form è in un campo in display none (tipo il
// dominio nella login) il form da solo non parte.
function submitonenter(formname,evt,thisObj) {
	evt = (evt) ? evt : ((window.event) ? window.event : "")
	if (evt) {
		// process event here
		// alert( evt.keyCode); // IE and Safari
		// alert( evt.which); // FF
		if ( evt.keyCode==13 || evt.which==13 ) {
			thisObj.blur();
			$('#'+formname).submit();
		}
	}
}



//--------------------------------------------------------------------------------------------







// over-ride the alert method only if this a newer browser.
// Older browser will see standard alerts
if(document.getElementById) {
	window.oldalert = window.alert;	// così posso continuare a chimare anche il vecchio alert
	window.alert = function(txt) {
		createCustomAlert(txt);
	}
	window.gconfirm = function(txt,fx) {
		createCustomConfirm(txt,fx);
	}
}

// 17/08/2011
// funzione che blocca lo schermo e su chiudi apre un link
function freeze($txt,$link) {
	createCustomAlert($txt);
	document.getElementById("closeBtn").onclick=function() { document.location.href=$link; };
}


function createCustomAlert(txt) {
	// shortcut reference to the document object
	d = document;

	// if the modalContainer object already exists in the DOM, bail out.
	if(d.getElementById("modalContainer")) return;

	// create the modalContainer div as a child of the BODY element
	mObj0 = d.getElementsByTagName("body")[0].appendChild(d.createElement("div"));
	mObj0.id = "modalContainer0";
	// make sure its as tall as it needs to be to overlay all the content on the page
	mObj0.style.height = document.documentElement.scrollHeight + "px";

	// create the modalContainer div as a child of the BODY element
	mObj = d.getElementsByTagName("body")[0].appendChild(d.createElement("div"));
	mObj.id = "modalContainer";
	// make sure its as tall as it needs to be to overlay all the content on the page
	mObj.style.height = document.documentElement.scrollHeight + "px";

	// create the DIV that will be the alert 
	alertObj = mObj.appendChild(d.createElement("div"));
	alertObj.id = "alertBox";
	// MSIE doesnt treat position:fixed correctly, so this compensates for positioning the alert
	if(d.all && !window.opera) alertObj.style.top = document.documentElement.scrollTop + "px";
	// center the alert box
	alertObj.style.left = (d.documentElement.scrollWidth - alertObj.offsetWidth)/2 + "px";

	// create an H1 element as the title bar
	h1 = alertObj.appendChild(d.createElement("h1"));
	h1.appendChild(d.createTextNode(_e("NOTICE")));

	// create a paragraph element to contain the txt argument
	msg = alertObj.appendChild(d.createElement("p"));
	msg.innerHTML = txt;

	// create an anchor element to use as the confirmation button.
	btn = alertObj.appendChild(d.createElement("a"));
	btn.id = "closeBtn";
	btn.appendChild(d.createTextNode(_e("Close")));
	btn.href = "#";
	// set up the onclick event to remove the alert when the anchor is clicked
	btn.onclick = function() { removeCustomAlert();return false; }
}

// removes the custom alert from the DOM
function removeCustomAlert() {
	document.getElementsByTagName("body")[0].removeChild(document.getElementById("modalContainer"));
	document.getElementsByTagName("body")[0].removeChild(document.getElementById("modalContainer0"));
}




function createCustomConfirm(txt,fx) {
	// shortcut reference to the document object
	d = document;

	// if the modalContainer object already exists in the DOM, bail out.
	if(d.getElementById("modalContainer")) return;

	// create the modalContainer div as a child of the BODY element
	mObj0 = d.getElementsByTagName("body")[0].appendChild(d.createElement("div"));
	mObj0.id = "modalContainer0";
	// make sure its as tall as it needs to be to overlay all the content on the page
	mObj0.style.height = document.documentElement.scrollHeight + "px";

	// create the modalContainer div as a child of the BODY element
	mObj = d.getElementsByTagName("body")[0].appendChild(d.createElement("div"));
	mObj.id = "modalContainer";
	// make sure its as tall as it needs to be to overlay all the content on the page
	mObj.style.height = document.documentElement.scrollHeight + "px";

	// create the DIV that will be the alert 
	alertObj = mObj.appendChild(d.createElement("div"));
	alertObj.id = "confirmBox";
	// MSIE doesnt treat position:fixed correctly, so this compensates for positioning the alert
	if(d.all && !window.opera) alertObj.style.top = document.documentElement.scrollTop + "px";
	// center the alert box
	alertObj.style.left = (d.documentElement.scrollWidth - alertObj.offsetWidth)/2 + "px";

	// create an H1 element as the title bar
	h1 = alertObj.appendChild(d.createElement("h1"));
	h1.appendChild(d.createTextNode(_e("NOTICE")));

	// create a paragraph element to contain the txt argument
	msg = alertObj.appendChild(d.createElement("p"));
	msg.innerHTML = txt;

	// create an anchor element to use as the confirmation button.
	btn = alertObj.appendChild(d.createElement("a"));
	btn.id = "closeBtnOK";
	btn.appendChild(d.createTextNode(_e("OK")));
	btn.href = "#";
	// set up the onclick event to remove the alert when the anchor is clicked
	btn.onclick = function(event) {
			if (event) event.preventDefault();
			removeCustomAlert();
			console.log(typeof fx);
			if(typeof fx == "string") eval(fx); 
			if(typeof fx == "function") fx(); 
			return true;
		}
	// create an anchor element to use as the confirmation button.
	btn = alertObj.appendChild(d.createElement("a"));
	btn.id = "closeBtnKO";
	btn.appendChild(d.createTextNode(_e("CANCEL")));
	btn.href = "#";
	// set up the onclick event to remove the alert when the anchor is clicked
	btn.onclick = function() { removeCustomAlert();return false; }

}



/* ---------------------- funzioni per pagine elenco e dettaglio, generiche. ------------------------- */
/*

	funzioni modificate, ampliate, specifiche per altri controlli non comuni a tante pagine
	vanno fatte nei template del componente. qui solo quelle riciclate.

*/


// ------------------------------------------------------------------------------------
// funzioni utilizzate in moltissime pagine per gestire le immagini
function elimina(s,div,i) {
	gconfirm(_e('Are you sure to delete the file now?'),function() {
		//chiamata ajax per cancellazione file, supporta spostamento file
		$.ajax({	'type' : 'GET',
			'url' : '../frwvars/ajax.deleteimg.php?f='+escape(s)+"&div0="+escape(div),
			'success' : function( $response ) { 
				if ($response.indexOf("ok")==0) { 
					divnuovo = $response.split("|");
					$("#" + div+ (i ? i : "")).parent().html(divnuovo[1]);
				 } },
			'error' : function () { alert("errore"); }
		});
	
	});
}

function movefromto(da,a,div,i) {
		//chiamata ajax per modificare ordinamento immagini.
		$.ajax({	'type' : 'GET',
			'url' : '../frwvars/ajax.moveimg.php?da='+escape(da)+'&a='+escape(a)+"&div0="+escape(div),
			'success' : function( $response ) { 
				if ($response.indexOf("ok")==0) { 
					divnuovo = $response.split("|");
					$("#" + div+ (i ? i : "")).parent().html(divnuovo[1]);
				 } },
			'error' : function () { alert("errore"); }
		});
}


// funzioni utilizzata per le spunte della griglia
function confermaDelete(id) {
	if (gconfirm(_e("Do you confirm to delete the item?"),"document.location.href = 'index.php?op=elimina&id="+id+"'")) {}
}

function confermaDeleteCheckMsg(theForm,$msg) {
	if (theForm) {
		var c = 0;
		for (var i = 0; i < theForm.elements['gridcheck[]'].length; i++) {
			if (theForm.elements['gridcheck[]'][i].checked) c=1;
		}
		if (c==0) {
			if (theForm.elements['gridcheck[]'].length==undefined) {
				if (theForm.elements['gridcheck[]'].checked==false) {
					alert (_e("You don't have selected any item to delete."));
					return;
				}
			} else {
				alert ( _e("You don't have selected any item to delete."));
				return;
			}
		}
		if (theForm.name) {
			if (gconfirm($msg,"document.forms['"+theForm.name+"'].op.value='eliminaSelezionati';document.forms['"+theForm.name+"'].submit();")) {}
		} else {
			if (confirm($msg)) {
				theForm.op.value='eliminaSelezionati';theForm.submit();
			}
		}
	} else {
		alert(_e("You don't have selected any item to delete."));
	}
}

function confermaDeleteCheck(theForm) {
	confermaDeleteCheckMsg(theForm, _e("Do you confirm to delete the selected items?"));
}


// per visualizzare e controllare la lunghezza delle
// textarea nei form.
function contacaratteri(sid,maxlimit) {
	if ( $('#'+sid).val().length >= maxlimit) {
		$('#'+sid).val() = $('#'+sid).val().substring(0,maxlimit);
		$('#counter'+sid).html('<b style="color:red">stop!</b>');
	} else $('#counter'+sid).html( maxlimit - $('#'+sid).val().length );
}


function saveAndLoad() {
	if ($('#op').val()=='modificaStep2') $('#op').val("modificaStep2reload");
	if ($('#op').val()=='aggiungiStep2') $('#op').val("aggiungiStep2reload");
	checkForm();
}
function checkConStato() {
	if ($('#op').val()=='modificaStep2reload') $('#op').val("modificaStep2");
	if ($('#op').val()=='aggiungiStep2reload') $('#op').val("aggiungiStep2");
	checkForm();
}
function aggiornaGriglia() {
	$('#combotiporeset').val('reset');
	document.getElementById("filtri").submit();
}
