<?php
/* modificato per PHP 7 */
//DEFINE ("COLORECAMPOEVIDENZIATO","#D1E0FC");
//DEFINE ("COLORECAMPONORMALE","#FFF");

class pezzoDelForm {
	var $name;
	var $value;
	var $attributes;
	var $obbligatorio;
	var $label;		//utilizzata nei msg d'errore
	var $onFocusString;
	var $onBlurString;

	function __construct() {
		$this->tipo="";
		$this->name="";
		$this->value="";
		$this->attributes="";
		$this->obbligatorio=0;
		$this->label="***";
		$this->onFocusString = "";
		$this->onBlurString = "";
		$this->showValue=false;
	}

	function gettag() {
		$fuori="<input type=\"hidden\" name=\"{$this->name}\" id=\"{$this->name}\" value=\"{$this->value}\" {$this->attributes}/>".($this->showValue?$this->value:"");
		return $fuori;
	}

	function editaEvidenziato(){

		$this->attributes.=" onfocus=\"{$this->onFocusString}\" onblur=\"{$this->onBlurString}\" ";
	}

}













































































class hidden extends pezzoDelForm {

	function __construct ($name='', $value=0) {
		parent::__construct();
		$this->name=$name;
		$this->value=$value;
	}

}

class intero extends pezzoDelForm {

	function __construct ($name='', $value=0, $maxlength=10, $size=10 ) {
		parent::__construct();
		$this->name=$name;
		$this->attributes.=" maxlength=\"{$maxlength}\" size=\"{$size}\"";
		$this->value=$value;
		$this->label=$this->name;
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->editaEvidenziato();
		$fuori="<input type=\"text\" name=\"{$this->name}\" id=\"{$this->name}\" value=\"{$this->value}\" {$this->attributes}/>";
		return $fuori;
	}





}

class testo extends pezzoDelForm {

	function __construct ($name='', $value="", $maxlength=null, $size=null ) {
		parent::__construct();
		$this->name=$name;
		$this->attributes = "";
		$this->value=$value;
		$this->label=$this->name;
		$this->maxlength = $maxlength;
		$this->size = $size;
		$this->maxlimit=0;	//0 = no limits. XX = max XX caratteri
	}

	// crea i tag per generare il form HTML
	function gettag () {
		if($this->maxlength > $this->maxlimit && $this->maxlimit>0 ) $this->maxlength = $this->maxlimit;
		$this->editaEvidenziato();
		$r = ""; $s = "";
		if($this->maxlength) $r.= "maxlength=\"{$this->maxlength}\"";
		if($this->size) $r.= " size=\"{$this->size}\"";
		if($this->maxlimit>0) {$r.=" onkeyup=\"contacaratteri('{$this->name}',{$this->maxlimit})\""; $s="<span id='counter{$this->name}'></span>"; }
		$fuori="<input type=\"text\" id=\"{$this->name}\" name=\"{$this->name}\" value=\"".str_replace("\"","&quot;",$this->value)."\" {$this->attributes}{$r}/>{$s}";
		return $fuori;
	}

}


class autocomplete extends testo {

	function __construct ($name='', $arrayvalori=array(), $maxlength=100, $size=50 ) {
		parent::__construct($name,"", $maxlength, $size);
		$this->name=$name;
		$this->attributes = "";
		$this->value=$arrayvalori; // è un array di coppie chiave valore
		$this->label=$this->name;
	}

	// crea i tag per generare il form HTML
	function gettag () {
		if($this->maxlength > $this->maxlimit && $this->maxlimit>0 ) $this->maxlength = $this->maxlimit;
		$this->editaEvidenziato();
		$r = "";
		if($this->maxlength) $r.= "maxlength=\"{$this->maxlength}\"";
		if($this->size) $r.= " size=\"{$this->size}\"";
		$fuori="<img src='../../icone/find.png'/> <input type='text' id='{$this->name}_picker' name='{$this->name}_picker' {$this->attributes}{$r}/>
			<input type='hidden' id='{$this->name}' name='{$this->name}' value=\"".$this->addvalues()."\"/>
			<p id='{$this->name}_selected'>".$this->addremover()."</p>";
		return $fuori;
	}

	function addremover() {
		// $this->value contiene una stringa di elementi separati da virgola e trimmati. ogni elemento viene incapsulato
		// con un controllo javascript che permette di rimuoverlo dal campo hidden
		$out = "";
		reset($this->value);
		//while ( list($val,$avideo) = each($this->value) ) {
		foreach($this->value as $val=>$avideo) {

			$out.="<a id='{$this->name}_el_$val' href='javascript:void()' onclick=\"autocomplete_remove(this)\" rel=\"{$val}\"";
			$out.=">$avideo</a>\n";
		}
		return $out;
	}

	function addvalues() {
		// $this->value contiene una stringa di elementi separati da virgola e trimmati. ogni elemento viene incapsulato
		// con un controllo javascript che permette di rimuoverlo dal campo hidden
		$out = "";
		reset($this->value);
		//while ( list($val,$avideo) = each($this->value) ) {
		foreach($this->value as $val=>$avideo) {
			$out.="{$val},";
		}
		return preg_replace("/,$/","",$out);
	}


}


class password extends pezzoDelForm {

	function __construct ($name='', $value="", $maxlength=10, $size=10 ) {
		parent::__construct();
		$this->name=$name;
		$this->attributes.=" maxlength=\"{$maxlength}\" size=\"{$size}\"";
		$this->value=$value;
		$this->label=$this->name;
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->editaEvidenziato();

		$fuori="<input type=\"password\" name=\"{$this->name}\" value=\"{$this->value}\" {$this->attributes}/>";
		return $fuori;
	}

}




class associator extends pezzoDelForm {

	function __construct ($prefix, $arVociInc, $arVociExc) {
		parent::__construct();
		$this->prefix=$prefix;
		$this->arVociInc=$arVociInc;
		$this->arVociExc=$arVociExc;
		//$this->h = (count($this->arVociInc)+count($this->arVociExc))*24;
	}



	function gettag() {
		$fuori="
		<table class='associator' id='{$this->prefix}table'><tr><td>Selected:<div id=\"{$this->prefix}IN\"><ul>";
		$avi="";
		$ave="";
		//while (list($key,$val) = each($this->arVociInc)) {
		foreach($this->arVociInc as $key=>$val) {
			$fuori.="<li id='{$this->prefix}{$key}' rel='{$key}'\">".myHtmlspecialchars($val)."</li>";
			$ave.= ($ave=="" ? "" : "," ) . "{$this->prefix}{$key}";
		}

		$fuori.="</ul></td>
		<td>
			<br><br>
			<input type='button' value='<< Add' class='add'/><br><br>
			<input type='button' value='Remove >>' class='remove'/><br>
		</td>
		<td>Available:<div id=\"{$this->prefix}OUT\"><ul>";

		foreach($this->arVociExc as $key=>$val) {
		//while (list($key,$val) = each($this->arVociExc)) {
			$fuori.="<li id='{$this->prefix}{$key}' rel='{$key}'\">".myHtmlspecialchars($val)."</li>";
		}

		$fuori.="</ul>";
		$fuori.="</div></td></tr></table><input type=\"hidden\" id=\"{$this->prefix}_vals\" name=\"{$this->prefix}_vals\" value=\"{$ave}\" /><script type=\"text/javascript\">
			$('#{$this->prefix}table ul li').click(function(){
				if($(this).hasClass('on')) $(this).removeClass('on'); else $(this).addClass('on');
			});
			function associator_{$this->prefix}() {
				$('#{$this->prefix}_vals').val('');
				$('#{$this->prefix}IN ul li').each(function() {
					var o = $('#{$this->prefix}_vals').val();
					$('#{$this->prefix}_vals').val( (o=='' ? '' : o + ',' ) + $(this).attr('rel') );
				});
			}
			$('#{$this->prefix}table input.add').click(function(){
				$('#{$this->prefix}OUT ul li.on').appendTo('#{$this->prefix}IN ul').removeClass('on');
				associator_{$this->prefix}();
			});
			$('#{$this->prefix}table input.remove').click(function(){
				$('#{$this->prefix}IN ul li.on').appendTo('#{$this->prefix}OUT ul').removeClass('on');
				associator_{$this->prefix}();
			});
		</script>";

		return $fuori;
	}

}


class numerointero extends pezzoDelForm {

	function __construct ($name='', $value="", $maxlength=10, $size=10 ) {
		parent::__construct();
		$this->name=$name;
		$this->attributes.=" maxlength=\"{$maxlength}\" size=\"{$size}\"";
		$this->value=$value;
		$this->label=$this->name;
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->editaEvidenziato();

		$fuori="<input type=\"text\" name=\"{$this->name}\" id=\"{$this->name}\" value=\"{$this->value}\" {$this->attributes}/>";
		return $fuori;
	}

}

class numerodecimale extends pezzoDelForm {

	function __construct ($name='', $value="", $maxlength=10, $size=10, $decimali=2 ) {
		parent::__construct();
		$this->name=$name;
		$this->attributes.=" maxlength=\"{$maxlength}\" size=\"{$size}\"";
		$this->value=$value;
		$this->decimali=$decimali;
		$this->label=$this->name;
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->onFocusString .= "this.value=parseFloatString(this.value,{$this->decimali});";
		$this->onBlurString .= "this.value=parseFloatString(this.value,{$this->decimali});";
		$this->editaEvidenziato();

		$fuori="<input type=\"text\" id='{$this->name}' name=\"{$this->name}\" value=\"{$this->value}\" {$this->attributes}/>";
		return $fuori;
	}

}


class email extends testo {

	function __construct ($name='', $value="", $maxlength=10, $size=10) {
		parent::__construct($name,$value, $maxlength, $size);
	}

}

class url extends testo {
	/* deprecato */
	function __construct ($name='', $value="", $maxlength=10, $size=10,$formname="") {
		parent::__construct($name,$value, $maxlength, $size);
		$this->formname = $formname;
	}

	function gettag() {
		$this->editaEvidenziato();

		$fuori="<input type=\"text\" name=\"{$this->name}\" value=\"{$this->value}\" {$this->attributes}/>";
		if ($this->formname) {
			$fuori.= "<script language='javascript'>function f{$this->name}() {
			if (BrowserDetect.browser.indexOf('Explorer')!=-1) {
				var c = window.showModalDialog('../webftp/selfilemain.php',0,'dialogHeight: 500px; dialogWidth: 400px; dialogTop: 100px; dialogLeft: 150px; center: Yes; help: No; resizable: Yes; status: No;');
				if (c) {
					document.{$this->formname}.{$this->name}.value=c[0];
				}
			} else {
			alert('Mi spiace, questo controllo funziona solo con Internet Explorer');
			}
			}
			</script> <a href=\"javascript:f{$this->name}()\"><img src='../../images/pickfile.gif' align='absmiddle' border='0'></a>";
		}
		$fuori.=" <a href=\"javascript:
			if (document.{$this->formname}.{$this->name}.value!='') 		window.open(document.{$this->formname}.{$this->name}.value);
			void(0);\" title=\"apri\"><img class='browse' src='../../images/transparent.gif' align='absmiddle' border='0'></a>";
		return $fuori;
	}

}


class urllink extends testo {

	function __construct ($name='', $value="", $maxlength=10, $size=10,$formname="") {
		parent::__construct($name,$value, $maxlength, $size);
		$this->formname = $formname;
	}

	function gettag() {
		$this->editaEvidenziato();

		$fuori="<input type=\"text\" name=\"{$this->name}\" maxlength=\"{$this->maxlength}\" size=\"{$this->size}\" id=\"{$this->name}\" onkeypress=\"
			if (document.getElementById('{$this->name}').value!='') document.getElementById('img{$this->name}').style.display=''; else document.getElementById('img{$this->name}').style.display='none';\"  value=\"{$this->value}\" {$this->attributes}/>";
		$fuori.=" <a onclick=\"
			if (document.getElementById('{$this->name}').value!='')
				window.open(document.getElementById('{$this->name}').value);

			void(0);\" title=\"apri link\" href='javascript:void(0);'><img class='browse' src='../../images/transparent.gif' id='img{$this->name}'
			onload=\"
				if (document.getElementById('{$this->name}').value!='') document.getElementById('img{$this->name}').style.display=''; else document.getElementById('img{$this->name}').style.display='none'; \"  align='absmiddle' border='0'></a>";
		return $fuori;
	}

}

class popsceltacomune extends testo {

	function __construct ($name='', $value="", $maxlength=10, $size=10, $formname="") {
		parent::__construct($name,$value, $maxlength, $size);
		$this->formname = $formname;
	}

	function gettag() {
		$this->editaEvidenziato();

		$fuori="<input type=\"text\" name=\"{$this->name}\" value=\"{$this->value}\" {$this->attributes}/>";

		if ($this->formname) {
			/*
				se c'e' formname e externalfunction linko il bottone di scelta alla funzione esterna
			*/
			$fuori.= "<script language='javascript'>function f{$this->name}() {
				if (BrowserDetect.browser.indexOf('Explorer')!=-1) {
				var c = window.showModalDialog('../anaprincipale/selcomunemain.php',0,'dialogHeight: 100px; dialogWidth: 350px; dialogTop: 100px; dialogLeft: 150px; center: Yes; help: No; resizable: No; status: No;');
				if (c) {
					document.{$this->formname}.{$this->name}.value=c[0];
				}
				} else {
				alert('Mi spiace, questo controllo funziona solo con Internet Explorer');
				}
				}
			</script> <a href=\"javascript:f{$this->name}()\"><img src='../../images/find.gif' align='absmiddle' border='0'></a>";
		}

		return $fuori;
	}
}


class areatesto extends pezzoDelForm {

	function __construct ($name='', $value="", $rows=null, $columns=null ) {
		parent::__construct();
		$this->name=$name;
		$this->attributes.= "";
		$this->rows=$rows;
		$this->columns=$columns;
		$this->label=$this->name;
		$this->value=$value;
		$this->maxlimit=0;	//0 = no limits. XX = max XX caratteri
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->editaEvidenziato();
		$r = ""; $s = "";
		if($this->rows) $r.= "rows=\"{$this->rows}\"";
		if($this->columns) $r.= " cols=\"{$this->columns}\"";
		if($this->maxlimit>0) {$r.=" onkeyup=\"contacaratteri('{$this->name}',{$this->maxlimit})\""; $s="<span id='counter{$this->name}'></span>"; }
		$fuori="<textarea id=\"{$this->name}\" name=\"{$this->name}\" {$this->attributes} {$r}\">{$this->value}</textarea>{$s}";
		return $fuori;
	}

}

/*class richtext2 extends pezzoDelForm { // OLD FCKEDITOR

	function richtext2 ($name='', $value="", $width="", $height="", $toolbar="") {
		parent::pezzoDelForm();
		$this->name=$name;
		$this->width=$width;
		$this->height=$height;
		$this->toolbar=$toolbar;
		$this->label=$this->name;
		// patch per sequenza caratteri che rompe l'editor.
		$value = str_replace(chr(226).chr(128).chr(168)," ",$value);
		$this->value=$value;
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->value=addslashes(preg_replace("/[\n\r]/","",$this->value));

		$fuori="<script type=\"text/javascript\">var oFCKeditor = new FCKeditor('{$this->name}');\noFCKeditor.BasePath = \"../../fckeditor/\";\noFCKeditor.Value='{$this->value}';\noFCKeditor.Width={$this->width};\noFCKeditor.Height={$this->height};\noFCKeditor.ToolbarSet='{$this->toolbar}';\noFCKeditor.Create();\n</script>";

		return $fuori;
	}

}*/

class richtext extends pezzoDelForm {

	function __construct ($name='', $value="", $width="", $height="", $toolbar="") {
		parent::__construct();
		$this->name=$name;
		$this->width=$width;
		$this->height=$height;
		$this->toolbar=$toolbar;
		$this->label=$this->name;
		// patch per sequenza caratteri che rompe l'editor.
		$value = str_replace(chr(226).chr(128).chr(168)," ",$value);
		$this->value=$value;
	}

	// crea i tag per generare il form HTML
	function gettag () {
		global $root;
		$this->value=(preg_replace("/[\n\r]/","",$this->value));

		if(!$this->toolbar || $this->toolbar=="BasicExt") 
			$tools = "plugins: 'link image code responsivefilemanager textcolor paste',
			toolbar: 'undo redo | bold italic forecolor backcolor | style-p style-h2 style-h3 | link image responsivefilemanager | code |  alignleft aligncenter alignright alignjustify',";
		else $tools = $this->toolbar;

		$fuori="<script>tinymce.init({
			width:{$this->width},
			height:{$this->height},
			menubar : false,
			selector:'textarea.class".$this->name."',
			".$tools."
			external_filemanager_path:'".$root."src/filemanager/',
			filemanager_title:\"Files\" ,
			/*extended_valid_elements: 'span',*/
			external_plugins: { 'filemanager' : '".$root."src/filemanager/plugin.min.js' }
			
		});</script><textarea class='class{$this->name}' id='#{$this->name}' name='{$this->name}'>{$this->value}</textarea>";



		return $fuori;
	}

}
class fileupload extends pezzoDelForm {

	function __construct ($name='', $size=30, $value="") {
		parent::__construct();
		$this->name=$name;
		$this->attributes.=" size='{$size}' ";
		$this->label=$this->name;
		$this->value=$value;
		$this->showlink=true;	//se true e c'e' value mostra il link
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->editaEvidenziato();
		$fuori="<input type='file' name=\"{$this->name}\" {$this->attributes} /><input type='hidden' name='{$this->name}_val' value='{$this->value}' />";
		if($this->showlink && $this->value!='') {
			$fuori.=" <span id='span_hide_{$this->name}'>(<a href='{$this->value}?x=".rand(1,1000)."'>apri</a>";
			$fuori.=" | <a href=\"javascript:elimina('{$this->value}','{$this->name}')\">elimina</a>)</span>";
		}
		return $fuori;
	}
}

class data extends pezzoDelForm {
	var $gg;
	var $mm;
	var $aaaa;
	var $formato;
	var $formname;	//per linkare il calendario


	function __construct ($name='', $value="", $formatoIN="gg-mm-aaaa",$formname = "") {
		//"formato" indica il formato della data in ingresso. a video ho sempre gg-mm-aaaa
		parent::__construct();
		$value = substr($value,0,10);
		$this->name=$name;
		$this->formato = $formatoIN;
		$this->formname = $formname;
		$token = "-";
		if (!stristr($formatoIN,"-")) $token="/";
		$arData = explode($token,$value);
		if ($formatoIN=="gg{$token}mm{$token}aaaa") {
			$g = $arData[0];	$m = $arData[1];	$a = $arData[2];
		} else if ($formatoIN=="mm{$token}gg{$token}aaaa") {
			$g = $arData[1];	$m = $arData[0];	$a = $arData[2];
		} else if ($formatoIN=="aaaa{$token}mm{$token}gg") {
			$g = $arData[2];	$m = $arData[1];	$a = $arData[0];
		}
		if (strlen($g)<2)$g="0".$g;
		if (strlen($m)<2)$m="0".$m;
		$this->gg = new intero($name."_gg",$g,2,2);
		$this->mm = new intero($name."_mm",$m,2,2);
		$this->aaaa = new intero($name."_aaaa",$a,4,4);

		$this->label=$this->name;
		$this->value=$value;

		//		onblur e onchangericompone il campo hidden con la data corretta
		//		------------------------------------------------------------
		$this->gg->onBlurString .= "Ricomponi{$this->name}()";
		$this->mm->onBlurString .= "Ricomponi{$this->name}()";
		$this->aaaa->onBlurString .= "Ricomponi{$this->name}()";

		$this->gg->attributes.="onchange=\"Ricomponi{$this->name}()\"";
		$this->mm->attributes.="onchange=\"Ricomponi{$this->name}()\"";
		$this->aaaa->attributes.="onchange=\"Ricomponi{$this->name}()\"";

	}

	// crea i tag per generare il form HTML
	function gettag () {

		$this->editaEvidenziato();
		$g=$this->gg;
		$m=$this->mm;
		$a=$this->aaaa;
		if(DATEFORMAT=="dd/mm/yyyy") {
			$fuori=$g->gettag()."/".$m->gettag()."/".$a->gettag();
		} else {
			$fuori=$m->gettag()."/".$g->gettag()."/".$a->gettag();
		}


		$veradata = new hidden($this->name,$this->value);
		$fuori.=$veradata->gettag();
		if ($this->formname) $fuori.=" ".$this->getCalendarLink($this->formname);

		$fuori.="<script language='javascript'>
		function Ricomponi{$this->name}() {
			var g = document.{$this->formname}.{$this->gg->name};
			var m = document.{$this->formname}.{$this->mm->name};
			var a = document.{$this->formname}.{$this->aaaa->name};

			document.{$this->formname}.{$this->name}.value = a.value+'-'+m.value+'-'+g.value;

		}
		</script>
		";

		return $fuori;
	}

	function getCalendarLink($formaname) {
		$g=$this->gg;
		$m=$this->mm;
		$a=$this->aaaa;

		return "
		<script>
			$(function() {
				$( \"#{$this->name}\" ).datepicker({
				  showOn: \"button\",
				  buttonImage: \"../../images/transparent.gif\",
				  buttonImageOnly: true,
				  buttonText: \"Select date\",
				  defaultDate: \"".$m->value."/".$g->value."/".$a->value."\"
				});
				$( \"#{$this->name}\").change(function(){
					c = $( \"#{$this->name}\").val().split('/');
					document.{$formaname}.{$g->name}.value=c[1];
					document.{$formaname}.{$m->name}.value=c[0];
					document.{$formaname}.{$a->name}.value=c[2];
				});
			  });
		</script>
		

		";

	}

}

class dataOra extends pezzoDelForm {
	var $gg;
	var $mm;
	var $aaaa;
	var $h;
	var $m;
	var $formato;
	var $formname;	//per linkare il calendario


	function __construct ($name='', $value="", $formatoIN="gg-mm-aaaa",$formname = "") {
		//"formato" indica il formato della data in ingresso. a video ho sempre gg-mm-aaaa
		//l'ora in ingresso è hh:mm (su 24 ore), quindi arriva 31-12-2007 19:03
		parent::__construct();
		$this->name=$name;
	    $this->formato = $formatoIN;
		$this->formname = $formname;
		
		if ($value && $value!=""){
			$value = substr($value,0,16); //"31-12-2007 19:03"
			
			$token = "-";
			if (!stristr($formatoIN,"-")) $token="/";
			$arDataOra = explode(" ",$value);
			$arOra = explode(":",$arDataOra[1]);
			$arData = explode($token,$arDataOra[0]);
			if ($formatoIN=="gg{$token}mm{$token}aaaa") {
				$g = $arData[0];	$m = $arData[1];	$a = $arData[2];
			} else if ($formatoIN=="mm{$token}gg{$token}aaaa") {
				$g = $arData[1];	$m = $arData[0];	$a = $arData[2];
			} else if ($formatoIN=="aaaa{$token}mm{$token}gg") {
				$g = $arData[2];	$m = $arData[1];	$a = $arData[0];
			}
			if (strlen($g)<2)$g="0".$g;
			if (strlen($m)<2)$m="0".$m;
			$this->gg = new intero($name."_gg",$g,2,2);
			$this->mm = new intero($name."_mm",$m,2,2);
			$this->aaaa = new intero($name."_aaaa",$a,4,4);

			$h2 = $arOra[0];
			$m2 = $arOra[1];
			if (strlen($h2)<2)$h2="0".$h2;
			if (strlen($m2)<2)$m2="0".$m2;
			$this->h = new intero($name."_h",$h2,2,2);
			$this->m = new intero($name."_m",$m2,2,2);
		}else {
			$this->gg = new intero($name."_gg","",2,2);
			$this->mm = new intero($name."_mm","",2,2);
			$this->aaaa = new intero($name."_aaaa","",4,4);
			$this->h = new intero($name."_h","",2,2);
			$this->m = new intero($name."_m","",2,2);
		}	
		$this->label=$this->name;
		$this->value=$value;

		//		onblur e onchangericompone il campo hidden con la data corretta
		//		------------------------------------------------------------
		$this->gg->onBlurString .= "Ricomponi{$this->name}()";
		$this->mm->onBlurString .= "Ricomponi{$this->name}()";
		$this->aaaa->onBlurString .= "Ricomponi{$this->name}()";
		$this->h->onBlurString .= "Ricomponi{$this->name}()";
		$this->m->onBlurString .= "Ricomponi{$this->name}()";

		$this->gg->attributes.="onchange=\"Ricomponi{$this->name}()\"";
		$this->mm->attributes.="onchange=\"Ricomponi{$this->name}()\"";
		$this->aaaa->attributes.="onchange=\"Ricomponi{$this->name}()\"";
		$this->h->attributes.="onchange=\"Ricomponi{$this->name}()\"";
		$this->m->attributes.="onchange=\"Ricomponi{$this->name}()\"";

	}

	// crea i tag per generare il form HTML
	function gettag () {

		$this->editaEvidenziato();
		$g=$this->gg;
		$m=$this->mm;
		$a=$this->aaaa;
		$h2=$this->h;
		$m2=$this->m;

		if(DATEFORMAT=="dd/mm/yyyy") {
			$fuori=$g->gettag()."/".$m->gettag()."/".$a->gettag();
		} else {
			$fuori=$m->gettag()."/".$g->gettag()."/".$a->gettag();
		}


		$veradata = new hidden($this->name,$this->value);
		$fuori.=$veradata->gettag();
		if ($this->formname) $fuori.=" ".$this->getCalendarLink($this->formname);
		$fuori.=" ".$h2->gettag().":".$m2->gettag();

		$fuori.="<script language='javascript'>
		function Ricomponi{$this->name}() {
			var g = document.{$this->formname}.{$this->gg->name};
			var m = document.{$this->formname}.{$this->mm->name};
			var a = document.{$this->formname}.{$this->aaaa->name};
			var h = document.{$this->formname}.{$this->h->name};
			var i = document.{$this->formname}.{$this->m->name};

			document.{$this->formname}.{$this->name}.value = a.value+'-'+m.value+'-'+g.value+' '+h.value+':'+i.value+':00';

		}
		</script>
		";

		return $fuori;
	}

	function getCalendarLink($formaname) {
		$g=$this->gg;
		$m=$this->mm;
		$a=$this->aaaa;

		return "
		<script>
			$(function() {
				$( \"#{$this->name}\" ).datepicker({
				  showOn: \"button\",
				  buttonImage: \"../../images/transparent.gif\",
				  buttonImageOnly: true,
				  buttonText: \"Select date\"
				});
				$( \"#{$this->name}\").change(function(){
					c = $( \"#{$this->name}\").val().split('/');
					document.{$formaname}.{$g->name}.value=c[1];
					document.{$formaname}.{$m->name}.value=c[0];
					document.{$formaname}.{$a->name}.value=c[2];
					Ricomponi{$this->name}(); /*ADDED*/
			});
			  });
		</script>";

	}



}

class submit extends pezzoDelForm {
	var $onclick;

	function __construct ($name='', $value="submit", $onclick="checkForm()" ) {
		parent::__construct();
		$this->name=$name;
		$this->value=$value;
		$this->label=$this->name;
		$this->onclick = $onclick;
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$fuori =  "<input type=\"submit\" name=\"{$this->name}\" value=\"{$this->value}\" onClick=\"{$this->onclick}\"/>";
		return $fuori;
	}
	function gettagimage ($imgurl,$text,$imghref="javascript:checkForm()",$extratags="align=\"absmiddle\" border=\"0\"") {
		$fuori =  "<a href=\"{$imghref}\"><img src=\"{$imgurl}\" {$extratags}/>{$text}</a>";
		return $fuori;
	}
}






class optionlist extends pezzoDelForm {

	var $arrayvalori;

	function __construct ($name, $valore='', $arrayvalori ) {
		parent::__construct();
		$this->name=$name;
		$this->value=$valore;
		$this->label=$this->name;
		$this->arrayvalori=$arrayvalori;
		$this->extraHtml="";
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->editaEvidenziato();
		$out="";
		$out.="<select {$this->attributes} size=\"1\" name=\"{$this->name}\" id=\"{$this->name}\">";

		foreach($this->arrayvalori as $val=>$avideo) {
		//while ( list($val,$avideo) = each($this->arrayvalori) ) {
			$out.="<option value=\"$val\"";
			if ($this->value==$val) $out.=" selected ";
			$out.=">$avideo</option>\n";
		}
		$out.="</select>".$this->extraHtml;

		return $out;
	}

}

class checkboxlist extends pezzoDelForm {

	var $arrayvalori;

	function __construct ($name, $valore='', $arrayvalori ) {
		parent::__construct();
		$this->name=$name;
		$this->value=$valore;
		$this->label=$this->name;
		$this->arrayvalori=$arrayvalori;
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->editaEvidenziato();
		$out="";
		//while ( list($val,$avideo) = each($this->arrayvalori) ) {
		foreach($this->arrayvalori as $val=>$avideo) {
			$out.="<label><input {$this->attributes} type=\"checkbox\" name=\"{$this->name}[]\" value=\"$val\"";
			if ($this->value==$val) $out.=" checked ";
			$out.="/>$avideo</label>\n";
		}

		return $out;
	}

}

//
// nuova checkboxlist che supporta piu' valori selezionati
// su campo database "set"
class checkboxlist2 extends pezzoDelForm {

	var $arrayvalori;

	function __construct ($name, $arrayvaloriSelezionati, $arrayvalori ) {
		parent::__construct();
		$this->name=$name;
		$this->value=$arrayvaloriSelezionati;
		$this->label=$this->name;
		$this->arrayvalori=$arrayvalori;
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->editaEvidenziato();
		$out="";
		//while ( list($val,$avideo) = each($this->arrayvalori) ) {
		foreach($this->arrayvalori as $val=>$avideo) {
			//echo $val." - ".$avideo." - in array: ". in_array($val,$this->value)."<hr>";
			$out.="<div style='display:inline;border:1px solid #ccc;padding:2px;vertical-align:middle;margin-right:10px;background-color:#fff;'><label><input {$this->attributes} type=\"checkbox\" name=\"{$this->name}[]\" value=\"$val\"";
			if (in_array($val,$this->value)) $out.=" checked ";
			$out.="/>$avideo</label>\n</div>";
		}

		return $out;
	}

}

class radiolist extends pezzoDelForm {

	var $arrayvalori;

	function __construct ($name, $valore='', $arrayvalori ) {
		parent::__construct();
		$this->name=$name;
		$this->value=$valore;
		$this->label=$this->name;
		$this->arrayvalori=$arrayvalori;
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->editaEvidenziato();
		$out="";
		//while ( list($val,$avideo) = each($this->arrayvalori) ) {
		foreach($this->arrayvalori as $val=>$avideo) {
			$out.="<input {$this->attributes} type=\"radio\" name=\"{$this->name}\" value=\"$val\"";
			if ($this->value==$val) $out.=" selected ";
			$out.="/>$avideo\n";
		}

		return $out;
	}

}



class checkbox extends pezzoDelForm {

	//var $arrayvalori;

	function __construct ($name, $valore='', $checked=true ) {
		parent::__construct();
		$this->name=$name;
		$this->value=$valore;
		$this->label=$this->name;
		$this->checked=$checked;
		$this->avideo="";
	}

	// crea i tag per generare il form HTML
	function gettag () {
		$this->editaEvidenziato();
		$out="";
		$out.="<label><input {$this->attributes} type=\"checkbox\" name=\"{$this->name}\" value=\"{$this->value}\"";
		if ($this->checked) $out.=" checked ";
		$out.="/> ".$this->avideo."</label>";

		return $out;
	}

}



class form {

	var $jsCheckList;
	var $name;
	var $pathJsLib;
	var $action;
	var $method;

	function __construct($name="dati"){
		global $root;
		$this->jsCheckList="";
		$this->name=$name;
		$this->pathJsLib=$root."src/template/controlloform.js";
		$this->action=$_SERVER["PHP_SELF"];
		$this->extraAttributes="";
		$this->extraJsFunction="";
		$this->method="POST";
	}
	function endform(){
		return "</form>";
	}

	function startform(){
		$s = "<script language=\"Javascript\" src=\"{$this->pathJsLib}\"></script>";
		$s.="<script language=\"javascript\">\n";
		$s.="function checkForm() {\n";
		$s.="{$this->extraJsFunction}\n";
		$s.="with(document.{$this->name}) {\n";
		$s.="{$this->jsCheckList}\n";
		$s.="submit();\n";
		$s.="}\n";
		$s.="}\n";
		$s.="</script>\n";
		//$s.="<form name=\"{$this->name}\" method=\"post\" {$this->extraAttributes} action=\"{$this->action}\" onsubmit=\"return false;\">";
		$s.="<form {$this->extraAttributes} action=\"{$this->action}\" method=\"{$this->method}\" name=\"{$this->name}\">";
		return $s;
	}


	function addControllo($obj){
		/*
			aggiunge i controlli javascript per gli elementi
			del form specificati
		*/
		$classe = strtolower(get_class($obj));

		if($classe == "fileupload") {
			if(!stristr($this->extraAttributes,'enctype="multipart/form-data"')) {
				$this->extraAttributes.= "enctype=\"multipart/form-data\"";
			}
		}
		if($classe=="intero"){
			$this->jsCheckList.="if (!testNumerico({$obj->name},{$obj->obbligatorio})) {";
			$this->jsCheckList.="alert (\"The field {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="testo"){
			$this->jsCheckList.="if (!testCampoTesto({$obj->name},{$obj->obbligatorio})) {";
			$this->jsCheckList.="alert (\"The field {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="numerointero"){ // positivo
			$this->jsCheckList.="if (!testNumericoIntPos({$obj->name},{$obj->obbligatorio})) {";
			$this->jsCheckList.="alert (\"The number {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="numerodecimale"){
			$this->jsCheckList.="if (!testNumericoDecimale({$obj->name},{$obj->obbligatorio},{$obj->decimali})) {";
			$this->jsCheckList.="alert (\"The number {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="email"){
			$this->jsCheckList.="if (!testEmail({$obj->name},{$obj->obbligatorio})) {";
			$this->jsCheckList.="alert (\"The email {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="url"){
			$this->jsCheckList.="if (!testUrl({$obj->name},{$obj->obbligatorio})) {";
			$this->jsCheckList.="alert (\"The url {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="autocomplete"){
			$this->jsCheckList.="if (!testCampoTesto({$obj->name},{$obj->obbligatorio})) {";
			$this->jsCheckList.="alert (\"The field {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="areatesto"){
			$this->jsCheckList.="if (!testCampoTesto({$obj->name},{$obj->obbligatorio})) {";
			$this->jsCheckList.="alert (\"The text area {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="optionlist"){
			$this->jsCheckList.="if (!testCombobox({$obj->name},{$obj->obbligatorio})) {";
			$this->jsCheckList.="alert (\"The select {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="checkboxlist"){
			$this->jsCheckList.="if (!testSerieDiCheckbox(document.{$this->name}.elements['{$obj->name}[]'],{$obj->obbligatorio})) {";
			$this->jsCheckList.="alert (\"The checkbox {$obj->label} is wrong.\");";
			$this->jsCheckList.="return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="checkbox"){
			$this->jsCheckList.="if (!testCheckbox({$obj->name},{$obj->obbligatorio})) {";
			$this->jsCheckList.="alert (\"The checkbox {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="radiolist"){
			$this->jsCheckList.="if (!testRadio({$obj->name},{$obj->obbligatorio})) {";
			$this->jsCheckList.="alert (\"The radio checkbox {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="data"){
			if ($obj->formato=='gg-mm-aaaa')	$this->jsCheckList.="{$obj->name}.value={$obj->name}_gg.value+'-'+{$obj->name}_mm.value+'-'+{$obj->name}_aaaa.value;";
			if ($obj->formato=='aaaa-mm-gg')	$this->jsCheckList.="{$obj->name}.value={$obj->name}_aaaa.value+'-'+{$obj->name}_mm.value+'-'+{$obj->name}_gg.value;";
			$this->jsCheckList.="if (!testData({$obj->name},{$obj->obbligatorio},'{$obj->formato}')) {";
			$this->jsCheckList.="alert (\"The date {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}_gg.focus();return;";
			$this->jsCheckList.="}\n";
		}
		if($classe=="dataora"){
			if ($obj->formato=='gg-mm-aaaa')	$this->jsCheckList.="{$obj->name}.value={$obj->name}_gg.value+'-'+{$obj->name}_mm.value+'-'+{$obj->name}_aaaa.value+' '+{$obj->name}_h.value+':'+{$obj->name}_m.value;";
			if ($obj->formato=='aaaa-mm-gg')	$this->jsCheckList.="{$obj->name}.value={$obj->name}_aaaa.value+'-'+{$obj->name}_mm.value+'-'+{$obj->name}_gg.value+' '+{$obj->name}_h.value+':'+{$obj->name}_m.value;";
			$this->jsCheckList.="if (!testDataOra({$obj->name},{$obj->obbligatorio},'{$obj->formato}')) {";
			$this->jsCheckList.="alert (\"The date time {$obj->label} is wrong.\");";
			$this->jsCheckList.="{$obj->name}_gg.focus();return;";
			$this->jsCheckList.="}\n";
		}

	}

}



?>