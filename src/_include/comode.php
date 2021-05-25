<?php


if(!ini_get('allow_url_fopen')) {
	echo "Please, set <code>allow_url_fopen=1</code> in your php.ini";
	die;
}


function Connessione() { global $conn; if ($conn = @new mysqli(WEBDOMAIN, DEFUSERNAME, DEFDBPWD, DEFDBNAME)) return 1; else return 0; }

function CollateConnessione() {
	global $conn;
	if (table_exists("frw_vars")) {
		$v = getVarSetting("COLLATIONCONNECTIONQUERY");
		if ($v!="") @$conn->query($v);
	} else {
		die("Database not properly installed.");
	}
}
function getVarSetting($var,$def="") {
	// get var settings and remove comments
	$value = execute_scalar("select de_value from frw_vars where de_nome='".addslashes($var)."'",$def);
	return preg_replace("/( +)?\/\*(.*)\*\//","",$value);
}

/*function loadTemplate($filename) {
	$handle = fopen ($filename, "rb");
	$contents = fread ($handle, filesize ($filename)); fclose ($handle);
	return $contents;
}*/

function loadTemplate($sFilename, $sCharset = 'UTF-8') {
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") $sFilename = str_replace("http://","https://",$sFilename);
	if (floatval(phpversion()) >= 4.3) {
		if(!file_exists($sFilename)) return "";
		$sData = file_get_contents($sFilename);
	} else {
		if (!file_exists($sFilename)) return "";
		$rHandle = fopen($sFilename, 'r');
		if (!$rHandle) return "";

		$sData = '';
		while(!feof($rHandle))
			$sData .= fread($rHandle, filesize($sFilename));
		fclose($rHandle);
	}
	$sEncoding = mb_detect_encoding($sData, 'auto', true);
	if ( $sEncoding != $sCharset) {
		if($sEncoding != false) {
			$sData = mb_convert_encoding($sData, $sCharset, $sEncoding);
		} else {
			$sData = mb_convert_encoding($sData, $sCharset);
		}
	}
	return $sData;
}

function loadTemplateAndParse($filename,$ar = array()) { // passare URL se si vuole eseguire PHP, accetta sia https che http che chiamate relative
	global $defaultReplace;
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") $filename = str_replace("http://","https://",$filename);
	if(empty($ar)) $ar = $defaultReplace;
	$contents = file_get_contents($filename);
	foreach($ar as $key=>$val) $contents = str_replace($key,$val,$contents);

	
	return $contents;
}

function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (is_dir($dir."/".$object))
           rrmdir($dir."/".$object);
         else
           unlink($dir."/".$object); 
       } 
     }
     rmdir($dir); 
   } 
 }

function is_emptydir($which){
	$dh=dir($which);
	$emptydir=true;
	while ($file=$dh->read()) {
		if(substr($file,0,1)==".") continue;
		if(!is_dir($which."/".$file)) {
			$emptydir=false;
			break;
		}
	}
	$dh->close();
	return $emptydir;
}

function is_email($Address) { /* verifica la correttezza di una mail*/
	if(stristr($Address,"@yopmail.com")) return false;
	if(stristr($Address,"@rmqkr.net")) return false;
	if(stristr($Address,"@emailtemporanea.net")) return false;
	if(stristr($Address,"@sharklasers.com")) return false;
	if(stristr($Address,"@guerrillamail.com")) return false;
	if(stristr($Address,"@guerrillamailblock.com")) return false;
	if(stristr($Address,"@guerrillamail.net")) return false;
	if(stristr($Address,"@guerrillamail.biz")) return false;
	if(stristr($Address,"@guerrillamail.org")) return false;
	if(stristr($Address,"@guerrillamail.de")) return false;
	if(stristr($Address,"@sina.com")) return false;
	if(stristr($Address,"@fakeinbox.com")) return false;
	if(stristr($Address,"@tempinbox.com")) return false;
	if(stristr($Address,"@guerrillamail.de")) return false;
	if(stristr($Address,"@guerrillamail.de")) return false;
	if(stristr($Address,"@opayq.com")) return false;
	if(stristr($Address,"@mailinator.com")) return false;
	if(stristr($Address,"@notmailinator.com")) return false;
	if(stristr($Address,"@getairmail.com")) return false;
	if(stristr($Address,"@meltmail.com")) return false;
	if(stristr($Address,"@gmail4u.eu")) return false;
	if(stristr($Address,"@blulapka.pl")) return false;
	if(stristr($Address,"@free-mail4u.eu")) return false;
	if(stristr($Address,"@bestmail365.eu")) return false;
	if(stristr($Address,"@ue90x.com")) return false;
	if(stristr($Address,"@xmaill.com")) return false;
	if(stristr($Address,"@jedna.co.pl")) return false;
	if(preg_match("/@mail([0-9]*)\.top$/",$Address)) return false; 
	if(preg_match("/e90\.biz$/",$Address)) return false; 
	
	return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$/i", $Address);
}
function setVariabile($nome,$valore="",$sessionbase="") {
	global $session;
	//cerca la variabile nel get, se non c'e' la cerca nel post,
	//se non c'e' la cerca nella sessione, se non c'e' gli mette il valore specificato
	if (isset($_GET[$nome])) {
		$start = $_GET[$nome];
	} else if (isset($_POST[$nome])) {
		$start = $_POST[$nome];
	} else if ($session->get($sessionbase.$nome)!="") {
		$start=$session->get($sessionbase.$nome);
	} else {
		$start=$valore;
	}
	return $start;
}

function postget($nome,$valore="") {
	if (isset($_POST[$nome])) $start= $_POST[$nome];
		elseif (isset($_GET[$nome])) $start= $_GET[$nome];
		else $start=$valore;
	return $start;
}
function getpost($nome,$valore="") {
	if (isset($_GET[$nome])) $start= $_GET[$nome];
		elseif (isset($_POST[$nome])) $start= $_POST[$nome];
		else $start=$valore;
	return $start;
}
function addslashesonlyquote($s) {
	return str_replace('"','\"',$s);
}

function returnmsg($msg,$op="",$class="err") {
	global $root,$defaultReplace,$session;
	$file = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on"   ? "https" : "http" ) . "://".$_SERVER["HTTP_HOST"].PONSDIR."/data/".DOMINIODEFAULT."/layout-msg.php";

	$html = loadTemplateAndParse( $file, $defaultReplace );
	if ($op=="back" || $op=="session") {
		$msg .= $session->get("backbutton");
	}elseif ($op=="reload") {
		$msg.="<br><span class='loading'>loading...</span>
			<script language='javascript'>setTimeout(\"document.location.href=document.location.href;\",1000)</script>";
	}elseif ($op=="jsback") {
		$msg.="<br>(<a href='javascript:history.go(-1)'>back</a>)";
	}elseif (preg_match("/^(load) /i",$op)) {
		$pageToLoadAr=explode(" ",$op);
		$msg.="<br><span class='loading'>loading...</span>
			<script language='javascript'>setTimeout(\"document.location.href='{$pageToLoadAr[1]}';\",1000)</script>";
	}elseif (preg_match("/^(link) /i",$op)) {
		$pageToLoadAr=explode(" ",$op);
		$msg.="<br>(<a href='{$pageToLoadAr[1]}'>go on</a>)";
	}
	$html = str_replace("##msg##",$msg,$html);
	$html = str_replace("##class##",$class,$html);
	return $html;
}

function returnmsgok($msg,$op="") {
	return returnmsg($msg,$op,"ok");
}


function todayadd($g) {
	return dayadd($g,date("Y-m-d H:i:s"));
}

function dayadd($g,$dayYmd) {
	$d = strtotime($dayYmd);
	$cc = 24*60*60*$g + 60*60 + $d; //ho aggiunto 1 ora perche' con le domeniche cannava
	return date("Y-m-d",$cc);
}

function date_diff2($d1, $d2) { 
	$q = strtotime($d2) - strtotime($d1);
	$d = $q / (60*60*24);
	return $d;
}

function TOymd($d='') {
	if ( !$d ) $d = date( "Y-m-d", time());
		else {
				/* From d.m.Y to Ymd */
				$d = substr($d,6,4)."-".substr($d,3,2)."-".substr($d,0,2);
			}
	return $d;
 }

/* converte la stringa della data da YYYY-mm-dd to dd.mm.YYYY */
function TOdmy($d='',$sep="-") {
	if ( !$d ) $d = date( "Y{$sep}m{$sep}d", time());
	return substr($d,8,2).$sep.substr($d,5,2).$sep.substr($d,0,4);
}
/* converte la stringa della data da YYYY-mm-dd hh:ii:ss to dd.mm.YYYY hh:ii:ss*/
function TOdmyhis($d='',$sep="-") {
	if ( !$d ) $d = date( "Y{$sep}m{$sep}d H:i:s", time());
	return substr($d,8,2).$sep.substr($d,5,2).$sep.substr($d,0,4)." ".substr($d,11,5);
}


function DateAdd($v,$d=null , $f="d/m/Y"){
  $d=($d?$d:date("Y-m-d"));
  return date($f,strtotime($v." days",strtotime($d)));
}
//Then use it:
//echo DateAdd(2);  // 2 days after
//echo DateAdd(-2,0,"Y-m-d");  // 2 days before with gigen format
//echo DateAdd(3,"01/01/2000");  // 3 days after given date




function makeBackButton($s) {
	global $root;
	$o = "<form name=\"makeBackButton\" action=\"$s\" method=\"post\">\n";
	//while ( $element = each($_POST))
	foreach($_POST as $key=>$val) 
	{
	  $o.="<input type=\"hidden\" name=\"{$key}\" value=\"{$val}\">\n";
	}
	//while ( $element = each($_GET))
	foreach($_GET as $key=>$val) 
	{
	  $o.="<input type=\"hidden\" name=\"{$key}\" value=\"{$val}\">\n";
	}
	$o.="<a href=\"javascript:document.makeBackButton.submit()\"><img border=\"0\" src=\"$root"."images/back.gif\" alt=\"indietro\"> indietro</a>\n";
	$o.="</form>";
	return $o;
}



function getListaForm($nomeCampoChiave, $valoreSelezionato="", $nomeCampoPerLista="label", $sql="", $ancheQuelloVuoto="tutte") {
	/*
		ritorna l'html per la tendina della scelta:
		genereca l'elenco html <option value="...">....</option>
		utilizzando i dati in ingresso.
	*/
	if ($sql=="") return "";
	global $conn;
	$rs = $conn->query ($sql);
	$html="";
	if ($ancheQuelloVuoto!="") $html.="<option value=\"\">$ancheQuelloVuoto</option>\r\n";
	while ($r=$rs->fetch_array()) {
		$html.="<option value=\"{$r[$nomeCampoChiave]}\"";
		if ($r[$nomeCampoChiave]==$valoreSelezionato) $html.=" selected";
		$html.=">$r[$nomeCampoPerLista]";
		$html.="</option>\r\n";
	}
	return $html;
}

function getListaCheckboxForm($nomeCampoChiave, $strValoriSelezionati="", $nomeCampoPerLista="label", $sql="") {
	/*
		ritorna l'html per le checkbox della scelta:
		genere l'elenco html <input type="checkbox" name="..." value="...">....
		utilizzando i dati in ingresso.
	*/
	if ($sql=="") return "";
	global $conn;
	$rs = $conn->query ($sql);
	$html="";
	while ($r=$rs->fetch_array()) {
		$html.="<input type=\"checkbox\" name=\"$nomeCampoChiave"."[]\" value=\"{$r[$nomeCampoChiave]}\"";
		if (stristr($strValoriSelezionati,$r[$nomeCampoChiave])) $html.=" checked";
		$html.=">$r[$nomeCampoPerLista]\r\n";
	}
	return $html;
}

function myHtmlspecialchars($s) {
	//per italiano
	$s = str_replace(chr(242),"&ograve;",$s);
	$s = str_replace(chr(243),"&oacute;",$s);
	$s = str_replace(chr(232),"&egrave;",$s);
	$s = str_replace(chr(233),"&eacute;",$s);
	$s = str_replace(chr(224),"&agrave;",$s);
	$s = str_replace(chr(225),"&aacute;",$s);
	$s = str_replace(chr(236),"&igrave;",$s);
	$s = str_replace(chr(237),"&iacute;",$s);
	$s = str_replace(chr(249),"&ugrave;",$s);
	$s = str_replace(chr(250),"&uacute;",$s);
	$s = str_replace(chr(210),"&Ograve;",$s);
	$s = str_replace(chr(211),"&Oacute;",$s);
	$s = str_replace(chr(200),"&Egrave;",$s);
	$s = str_replace(chr(201),"&Eacute;",$s);
	$s = str_replace(chr(192),"&Agrave;",$s);
	$s = str_replace(chr(193),"&Aacute;",$s);
	$s = str_replace(chr(204),"&Igrave;",$s);
	$s = str_replace(chr(205),"&Iacute;",$s);
	$s = str_replace(chr(217),"&Ugrave;",$s);
	$s = str_replace(chr(218),"&Uacute;",$s);


	//per tedesco
	$s = str_replace(chr(223),"&szlig;",$s);
	$s = str_replace(chr(214),"&Ouml;",$s);
	$s = str_replace(chr(246),"&ouml;",$s);
	$s = str_replace(chr(220),"&Uuml;",$s);
	$s = str_replace(chr(252),"&uuml;",$s);
	$s = str_replace(chr(228),"&auml;",$s);
	$s = str_replace(chr(196),"&Auml;",$s);
	$s = str_replace(chr(203),"&Euml;",$s);
	$s = str_replace(chr(235),"&euml;",$s);
	$s = str_replace(chr(207),"&Iuml;",$s);
	$s = str_replace(chr(239),"&iuml;",$s);
	//...metere le mancanti...


	$s = str_replace(chr(244),"&ocirc;",$s);
	$s = str_replace(chr(212),"&Ocirc;",$s);


	//generiche
	$s = str_replace(chr(174),"&reg;",$s);
	$s = str_replace(chr(169),"&copy;",$s);
	$s = str_replace(chr(145),"&#39;",$s);
	$s = str_replace(chr(146),"&#39;",$s);
	$s = str_replace(chr(147),"&quot;",$s);
	$s = str_replace(chr(148),"&quot;",$s);
	//$s = str_replace(chr(176),"&deg;",$s);
	$s = str_replace(chr(234),"&#234",$s);
	$s = str_replace(chr(171),"&#171",$s);
	$s = str_replace(chr(187),"&#187",$s);
	$s = str_replace(chr(945),"&#945",$s);
	return $s;
}

function mail_utf8($to, $subject = '(No subject)', $message = '', $fromheader,$menof=null) {
	$header = 'MIME-Version: 1.0' . "\n" . 'Content-type: text/plain; charset=UTF-8'
	. "\n" . $fromheader."\n";
	mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header,$menof);
} 

function linkEmail($mail,$label="<img src='##ROOT##images/email.gif' border='0' align='absbottom' alt='invia mail'>") {
	$mail = trim($mail);
	if ($mail=="") return "";
	global $root;
	$link="<a href=\"mailto:$mail\" title=\"$mail\">$mail ".str_replace("##ROOT##",$root,$label)."</a>";
	return $link;
}

function GetTimeStamp($MySqlDate) {
	/* da yyyy-mm-dd hh ii ssa timestamp */
	$MySqlDate = $MySqlDate." 00:00:00";
	$ar = preg_split("/[ \:\-]/i",$MySqlDate); // split the array
	return mktime((integer)$ar[3],$ar[4],$ar[5],(integer)$ar[1],(integer)$ar[2],(integer)$ar[0]);
}

function DataDiOggiEstesa($lingua) {
	if ($lingua=="1") {
		// For the italians, the days of the week
		$giorno_num = date("w");
							switch ($giorno_num) {
								case 0: $giorno="Domenica"; break;
								case 1: $giorno="Lunedi"; break;
								case 2: $giorno="Martedi"; break;
								case 3: $giorno="Mercoledi"; break;
								case 4: $giorno="Giovedi"; break;
								case 5: $giorno="Venerdi"; break;
								case 6: $giorno="Sabato"; break;
							}
		// For the italians, the mounths of the year
		$mesi_en = date("F");
				   switch ($mesi_en) {
						   case "January": $mese="Gennaio";
								   break;
						   case "February": $mese="Febbraio";
								   break;
						   case "March": $mese="Marzo";
								   break;
						   case "April": $mese="Aprile";
								   break;
						   case "May": $mese="Maggio";
								   break;
						   case "June": $mese="Giugno";
								   break;
						   case "July": $mese="Luglio";
								   break;
						   case "August": $mese="Agosto";
								   break;
						   case "September": $mese="Settembre";
								   break;
						   case "October": $mese="Ottobre";
								   break;
						   case "November": $mese="Novembre";
								   break;
						   case "December": $mese="Dicembre";
								   break;
				   }
		return $giorno." ".date("j")." ".$mese." ".date("Y");

	} else {
		return date("l jS F Y");
	}
}

function timeBetween($time1,$time2,$formato="hh:mm")
{
	//ritorna l'ora in HH:MM tra due orari
	// -> timeBetween("12:30","13:37");

	$time1 = explode(":",$time1);
	$time2 = explode(":",$time2);

	// convert everything to minutes

	$minutes1 = ($time1[0] * 60) + $time1[1];
	$minutes2 = ($time2[0] * 60) + $time2[1];

	$difference = $minutes2 - $minutes1;

	$time = $difference / 60;
	$time = explode(".",$time);

	$duration['hour'] = $time[0];
	if (isset($time[1])) $minute = ".$time[1]" * 60; else $minute=0;
	$minute = explode(".",$minute);
	$duration['minute'] = $minute[0];
	if ($formato=="hh:mm") {
		if (strlen($duration['minute'])==1) $duration['minute']="0".$duration['minute'];
		if (strlen($duration['hour'])==1) $duration['hour']="0".$duration['hour'];
		// lets push back several formats
		return $duration['hour'].":".$duration['minute'];
	} else return $duration['hour']."h ".$duration['minute']."m";

}

function timeBetweenNumber($time1,$time2)
{
	// ritorna l'ora in un numero con la virgola, per fare i conti.
	// timeBetweenNumber("12:30","13:37");

	$time1 = explode(":",$time1);
	$time2 = explode(":",$time2);

	// convert everything to minutes

	$minutes1 = ($time1[0] * 60) + $time1[1];
	$minutes2 = ($time2[0] * 60) + $time2[1];

	$difference = $minutes2 - $minutes1;

	$time = $difference / 60;
	$time = explode(".",$time);

	$duration['hour'] = $time[0];
	if (isset($time[1])) $minute = ".$time[1]" * 60; else $minute=0;
	$minute = explode(".",$minute);
	$duration['minute'] = $minute[0];
	// lets push back several formats

	return number_format(($duration['hour']+$duration['minute']/60),2);
}

function arrotondaEuro($x) {
	// $x è in euro, cioe' tipo 10.32232323 euro
/* terzo decimale:
	0 o 1 o 2 -->0
	3 o 4 o 5 o 6 -->5
	7 o 8 o 9 -->10
*/
	$x = number_format($x,2);
	$ago = substr($x,-1);
	if ($ago<=2) $x = $x- $ago*0.01;
	if ($ago>2 && $ago<=6) $x = $x- $ago*0.01 + 0.05;
	if ($ago>6) $x = $x- $ago*0.01 + 0.1;
	return number_format($x,2);
}


function checkAbilitazione($componente, $settasempre="SETTA_SEMPRE") {
	global $session,$conn;
	if ($session->get($componente) == "") {
		/*
			se non c'è $componente allora è il primo caricamento della
			classe e devo recuperare i dati dal db e metterli in sessione
			perche' gli accessi successivi utilizzeranno la sessione
		*/
		$sql = "SELECT frw_componenti.nome as componente, frw_funzionalita.label, frw_funzionalita.nome
				FROM frw_funzionalita
				JOIN frw_componenti ON frw_funzionalita.idcomponente = frw_componenti.id
				JOIN frw_ute_fun ON idfunzionalita = frw_funzionalita.id
				WHERE (frw_componenti.nome =  '{$componente}') AND frw_ute_fun.idutente =  '".$session->get("idutente")."';";
		$rs=$conn->query($sql) or trigger_error($rs->error);
		if($settasempre=="SETTA_SEMPRE") {
			/*
				setta sempre vuol dire che comunque sia io setto il valore false
				e poi se trovo quello che cerco lo setto con il valore
				che arriva dal db. Questo influenza poi come si controlla
				l'esistenza della variabile in sessione, se controllo solo se esiste
				allora è meglio NON passare SETTA_SEMPRE, ma mettere SETTA_SOLO_SE_ESISTE
			*/
			$session->register($componente,"false");
		}
		while($row = $rs->fetch_array()){
			if ($row['componente']==$componente) {
				$session->register($row['nome'],$row['label']);
			} else {
				if($settasempre=="SETTA_SEMPRE") {
					$session->register($row['nome'],"");
				}
			}
		}
		$rs->free();
	}
}


/*
Small function to Alphabetically sort Multidimensional arrays by index values of an n dimension array.

I have only tested this for sorting an array of up to 6 dimensions by a value within the second dimension. This code is very rough and works for my purposes, but has not been tested beyond my needs.

Although a little clunky and not a mathematical type algorithm, it get's the job done. It theoretically overcomes many of the problems I have seen with multidimensional arrays in that it is possible to specify within the function, not by reference :-(, which index you wish to sort by, no matter how many dimensions down.

call function by assigning it to a new / existing array:

$row_array = multidimsort($row_array);
*/

function array_qsort2 (&$array, $column=0, $order=SORT_ASC, $first=0, $last= -2)
{
	// $array  - the array to be sorted
	// $column - index (column) on which to sort
	//          can be a string if using an associative array
	// $order  - SORT_ASC (default) for ascending or SORT_DESC for descending
	// $first  - start index (row) for partial array sort
	// $last  - stop  index (row) for partial array sort

	if($last == -2) $last = count($array) - 1;
	if($last > $first) {
		$alpha = $first;
		$omega = $last;
		$guess = $array[$alpha][$column];
		while($omega >= $alpha) {
			if($order == SORT_ASC) {
			while($array[$alpha][$column] < $guess) $alpha++;
			while($array[$omega][$column] > $guess) $omega--;
		} else {
			while($array[$alpha][$column] > $guess) $alpha++;
			while($array[$omega][$column] < $guess) $omega--;
		}
		if($alpha > $omega) break;
		$temporary = $array[$alpha];
		$array[$alpha++] = $array[$omega];
		$array[$omega--] = $temporary;
	}
	array_qsort2 ($array, $column, $order, $first, $omega);
	array_qsort2 ($array, $column, $order, $alpha, $last);
	}
}

function array_key_multi_sort($arr) {
	//usort($arr, create_function('$a, $b', "return $f(\$a['$l'], \$b['$l']);"));
	usort($arr, "unatcmp");
	return($arr);
}

function unatcmp($a,$b,$l) {
	return strnatcasecmp($a[2], $b[2]);
}

function smartsub($text,$maxTextLenght,$modo) {
	/* non tronca le parole */
   $aspace=" ";
   if(strlen($text) > $maxTextLenght ) {
     $text = substr(trim($text),0,$maxTextLenght);
     if ($modo=="donttrun") $text = substr($text,0,strlen($text)-strpos(strrev($text),$aspace));
     $text = $text.'...';
   }
   return $text;
}

function mysql_scalar($sql) {
	return execute_scalar($sql);
}
function NomeImmagine($s) { /*cerca file gif, jpg, swf. Usata nei banner*/
	$ext="";
	if (file_exists("$s.jpg")) { $ext=".jpg"; }
		elseif (file_exists("$s.jpeg")) { $ext=".jpeg"; }
		elseif (file_exists("$s.gif")) { $ext=".gif"; }
		elseif (file_exists("$s.swf")) { $ext=".swf"; }
		elseif (file_exists("$s.png")) { $ext=".png"; }
		elseif (file_exists("$s.zip")) { $ext=".zip"; }
	if (!$ext) { return ""; } else { return $s.$ext; } }


function execute_scalar($sql,$def="") {
	global $conn; if ( $rs = $conn->query($sql) ) { $r = $rs->fetch_array(); $rs->free(); return $r[0]; } return $def;
}

function execute_row($sql) {
	global $conn; if ( $rs = $conn->query($sql) ) { $r = $rs->fetch_array(); $rs->free(); return $r; } return "";
}

function concatenaId($sql,$sep = ",") {
	global $conn; $o = "";if ($rs = $conn->query($sql)) while($r=$rs->fetch_row())$o.=($o?$sep:"").$r[0]; else die($conn->error);return $o;
}


if (!function_exists("stripos")) {
	//compatibilità php 4
	function stripos($haystack, $needle){return strpos($haystack, stristr( $haystack, $needle ));}
}

function getDbField($table,$field) { /* quando è usata? */
	$query = "SHOW COLUMNS FROM `$table` LIKE '$field'";
	$result = mysql_query( $query ) or die( 'error getting enum field ' . mysql_error() );
	$row = mysql_fetch_array($result);
	if(stristr($row['Type'],"varchar")) {
		preg_match("/varchar ?\(([0-9]+)\)/", $row['Type'], $f);
		return array("Type"=>"varchar","Size"=>$f[1]);
	}
	return $row ;
}

function set_and_enum_values( $table , $field ){
	// dato il nome tabella e il campo
	// preleva l'enum per costruire l'array da passare
	// all'oggetto optionlist
	global $conn;
	$query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DEFDBNAME."' AND TABLE_NAME = '".$table."' and COLUMN_NAME='".$field."'";
	$result = $conn->query( $query ) or trigger_error( 'error getting enum field ' . $conn->error );
	$row = $result->fetch_array();
	if(stripos(".".$row[1],"enum(") > 0) {
		$row[1]=str_replace("enum('","",$row[1]);
	} else {
		$row[1]=str_replace("set('","",$row[1]);
	}
	$row[1]=str_replace("','","\n",$row[1]);
	$row[1]=str_replace("')","",$row[1]);
	$ar = explode("\n",$row[1]);
	for ($i=0;$i<count($ar);$i++) $arOut[str_replace("''","'",$ar[$i])]=str_replace("''","'",$ar[$i]);
	return $arOut ;
}
function getEmptyNomiCelleAr( $table ){
	// restituisce l'array dei nomi dei campi di una tabella
	// per la funzione getdettaglio.
	global $conn;
	$query = "SELECT COLUMN_NAME
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE TABLE_SCHEMA =  '".DEFDBNAME."'
		AND TABLE_NAME =   '".$table."'";
	$result = $conn->query( $query ) or trigger_error( 'error: ' . $conn->error );
	$outAr = array();
	while($row = $result->fetch_array()) {
		//print_r($row);
		$outAr += array( $row['COLUMN_NAME'] => "");
	}
	return $outAr ;
}




/*
	mostra la gallery degli oggetti uploadati indipendentemente dal
	tipo di oggetto (gif jpg swf flv mp3 png)
	$dir = posto dove sono salvati i files
	$prenome = xxxx_ dove xxxx è l'id del record corrispondente sul db dopo _ ci saranno i numeri 0, 1 a seconda della posizione
	$div = stringa da mettere davanti agli id numerici che contengono le immagini
	$return = html | array
	$SPOSTA = true | false (se true mette fuori anche le frecce per muovere le immagini, serve js giusto per rename e delete)
	$TRIGGER_ERROR = true | false (se true trigga l'errore in assenza di cartella e permessi)
*/
function loadgallery($dir,$prenome,$div="div",$return="html",$SPOSTA=false,$TRIGGER_ERROR=true) {
	if($TRIGGER_ERROR && !is_dir($dir)) trigger_error("Non trovo la cartella: ".$dir);
	if($TRIGGER_ERROR && !is_writeable($dir)) trigger_error("Non posso scrivere sulla cartella: ".$dir);
	$c = 0;
	$out = "";
	if (is_dir($dir)) {
		$a=array();
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(!preg_match("/\.info$/",$file)) {
					$tipo = "";
					$tipo = ( str_replace(".","", strrchr($file, '.') ) );
					if($tipo!="") {
						if(strpos(" ".$file,$prenome)==1) {
							$a[$c][0]=$dir.$file;
							$a[$c][1]=$file;
							$p = (integer)preg_replace("/[^0-9]/","",stristr($file,"_"));
							$a[$c][2]=$p;
							$a[$c][3]=$tipo;
							$c++;
						}
					}
				}
			}
			closedir($dh);

			$a = array_key_multi_sort($a);	//da testare

			//$a = array_key_multi_sort($a,2);	//da testare
			$prec = "";
			for($i=0;$i<count($a);$i++) {
				
				$fsize=number_format(filesize($a[$i][0])/1024,2);
				if (in_array($a[$i][3],array('jpg','gif','png','jpeg'))) {
					// thumb immagine
					$size=GetImageSize($a[$i][0]);
					if($size[0]>$size[1]) $outsize = "width:50px;"; else $outsize = "height:50px;";
					$filename = $a[$i][0];
					$descr = "$fsize KB, ".strtoupper($a[$i][3])." {$size[0]}x{$size[1]}px";
					//$ar = stat($a[$i][0]);
					//$ar = date("Y-m-d H:i:s",$ar['ctime']);
					//if(date("Y-m-d H:i:s")<=$ar) 
					$filename=$filename.'?'.rand(0,10000); // fuck cache

				} else {
					// file generico per altre estensioni
					$outsize = "width:48px;";
					$filename = "".PONSDIR."/src/icons/square53.png";
					$descr = "$fsize KB, ".strtoupper($a[$i][3]);
					//$descr.= print_r($a[$i][3],true);
				}
				$nome = loadTemplate($a[$i][0].".info")." ";	// se c'e' il .info contiene il nome originale del file uploadato.
				
				//preg_match("/([0-9]+)_([0-9]+)\.([a-z0-9]+)/i",$a[$i][0],$pos);
				$out .= "<div id='{$div}{$i}' class='divthumbs".($i==0?" first":"")."'>";
				if($i>0 && $SPOSTA) $out.="<a class='msx' href=\"javascript:movefromto('".$a[$i][0]."','".$prec."','{$div}','{$i}')\">&nbsp;</a>";
				$out.= "<div class='divinternothumb'>
						<a title='Click to open {$nome}({$descr})' target='_blank' href=\"".$a[$i][0]."\"><img src='".$filename."' style='$outsize'/><br/><span>{$a[$i][3]}</span></a>";
				if($SPOSTA) 
					$out.="<a title='Click to delete file.' href=\"javascript:elimina('{$a[$i][0]}','{$div}','{$i}')\">";
				else
					$out.="<a title='Click to delete file.' href=\"javascript:elimina('{$a[$i][0]}','{$div}{$i}')\">";
				$out.="delete</a></div>";
				$prec = $a[$i][0]; 
				if($i>0) $out = str_replace("#SUC#",$prec,$out);
				if($i<count($a)-1 && $SPOSTA) $out.="<a class='mdx' href=\"javascript:movefromto('".$a[$i][0]."','#SUC#','{$div}','{$i}')\">&nbsp;</a>";
				$out.="</div>";
			}
		}
	}
	if ($c==0) $out = "<i>0 files</i>";
	return ($return=='html'?$out:$a);
}

/* rimuove il file dalla cartella indicata, cerca anche il .info, $f senza estensione */
function deldbimg($f) {
	$n = NomeImmagine($f);
	if (file_exists($n)) unlink($n);
	if (file_exists($n.'.info')) unlink($n.'.info');
}

/* gestisce spostamento posizioni dell'oggetto gallery, gestisce anche il .info */
function spostafilegallery($da,$a,$div0) {
	preg_match("/([0-9a-z-]+)_([0-9]+)\.([a-z0-9]+)/i",$da,$pos);
	rename($da,str_replace(basename($da),"temp",$da));
	rename($a,$da);
	rename(str_replace(basename($da),"temp",$da),$a);
	if(file_exists($da.".info")) {
		// potrebbe non esserci sui vecchi "ambiente"
		rename($da.".info",str_replace(basename($da.".info"),"temp.info",$da.".info"));
		rename($a.".info",$da.".info");
		rename(str_replace(basename($da.".info"),"temp.info",$da.".info"),$a.".info");
	}
	$uploadDir = str_replace(basename($da),"",$da);
	$out = loadgallery($uploadDir,$pos[1]."_",$div0,"html",true);
	return "ok|".$out;
}

/* gestisce cancellazione di un item della gallery, gestisce anche la cancellazione del .info */
function deletefilegallery($f,$div0) {
	if(file_exists($f)) {


		// nel caso di file ZIP devo rimuovere anche la cartella che li ha scompattati
		//echo $f."\n";
		//../../../adadmindata/dbimg/7banner/17_0.zip
		if(preg_match("/\.zip$/i",$f)) {
			$uploadDir = str_replace(basename($f),"",$f);
			$filename = str_replace($uploadDir,"",$f);
			$id = explode("_",$filename);
			$zipFolder = $uploadDir.$id[0];
			rrmdir($uploadDir.$id[0]);
		}
		
		if(file_exists($f.".info")) unlink($f.".info"); // potrebbe non esserci sui vecchi "ambiente"
		$uploadDir = str_replace(basename($f),"",$f);
		unlink($f);


		preg_match("/([0-9a-z-]+)_([0-9]+)\.([a-z0-9]+)/i",$f,$pos);
		$out = loadgallery($uploadDir,$pos[1]."_",$div0,"html",true);
		return "ok|".$out;
	}
}

function uploadFile($files,$campo,$uploadfile,$allowedArrayExt,$x=0,$y=0,$kb=0,$max=1) {
	/* ----------------------------------------------------- */
	$msg = ""; //output


	if($files[$campo]['type']!="") {

		$ext = strtolower( str_replace(".","", strrchr($_FILES[$campo]['name'], '.') ) );

		if( !in_array($ext,$allowedArrayExt) ) {
			/*
				tipo file errato
			*/
			$msg = "Only file with these formats: ".implode(", ",$allowedArrayExt)." (your file is {$ext}, mime type: {$files[$campo]['type']}).";

		} else {

			/*
				tipo ok, completo upload
			*/
			if (in_array($ext, array("gif","png","jpg","jpeg")) && $x>0 && $y>0) {
				// è immagine e devo verificare dimensioni immagini
				$arDatiImg = GetImageSize($files[$campo]['tmp_name']);
				if($arDatiImg[0]>round($x*1.1) || $arDatiImg[1]>round($y*1.1)) {	//10% tolleranza
					/*
						dimensioni errate
					*/
					$msg = "Only images with max this dimensions: {$x}x{$y}.";
				}

			}


			if ($msg=="") {

				if(is_uploaded_file($files[$campo]['tmp_name'])) {
					if(filesize($files[$campo]['tmp_name'])>$kb*1024) {
						/*
							troppo pesante
						*/
						$msg = "File too big, max {$kb}kb.";
					} else {
						/*
							ok
						*/
						$num = 0;
						$av = false;
						while (!$av){
							for($i=0;$i<count($allowedArrayExt);$i++) {
								if (file_exists($uploadfile.$num.".".$allowedArrayExt[$i])) {
									$av = false;
									$i = count($allowedArrayExt);
									break;
								} else {
									$av = true;
								}
							}
							if (!$av) $num++;
						}

						if ($max > $num) {

							if (move_uploaded_file($files[$campo]['tmp_name'], $uploadfile.$num.".".$ext)) {
								// tutto ok
								chmod($uploadfile.$num.".".$ext, 0755);
								$msg = "";
								$nomefile = $uploadfile.$num.".".$ext.".info";
								$f = fopen($nomefile,'w');
								fwrite($f,$files[$campo]['name']);
								fclose($f);
							} else {
								//attack
								$msg = "File not uploaded (2).";
							}
						
						} else {
							$msg = "Too many files, max {$max}.";

						}
					}
				} else {
					/*
					ko
					*/
					$msg = "File not uploaded (1).";
				}
			}
		}
		if($msg!="") {
			$msg = "-1|{$msg}";
		}
	}
	/* ------------------------------------------------------ */

	return $msg;
}

function table_exists($t) {
	$sql = "SELECT COUNT(*)
	FROM information_schema.tables 
	WHERE table_schema = '".DEFDBNAME."' 
	AND table_name = '".$t."'";
	if( execute_scalar($sql) > 0 ) return true; else return false;
	/*
	mysql >5.5
	$tables = mysql_query ("SHOW TABLES FROM ".DEFDBNAME); 
	while (list ($temp) = mysql_fetch_array ($tables)) if ($temp == $t) return true;
	return false;
	*/
}

function ricavaNomePuro($s,$removestopwords=false) { /* funzione per gli url integra htaccess */
	$s = str_replace("<br/>", "-", $s);
	$s=strip_tags($s);
	$s=strtolower($s);
	// non esiste una funzione per questo?
	$s=str_replace("ò","o",$s);
	$s=str_replace("ó","o",$s);
	$s=str_replace("è","e",$s);
	$s=str_replace("é","e",$s);
	$s=str_replace("à","a",$s);
	$s=str_replace("á","a",$s);
	$s=str_replace("ì","i",$s);
	$s=str_replace("í","i",$s);
	$s=str_replace("ù","u",$s);
	$s=str_replace("ú","u",$s);
	$s = str_replace("'","' ",$s);

	if($removestopwords) {
		$stopwords = array("il","la","le","gli","i","the","lo","del","dei","degli","della","dello","a","e","o","di","in","al","un","uno","una","alla","l","all","per","con","dell","dell'","all'","l'");
		foreach($stopwords as $w) {
			$s = preg_replace("/^".$w." /"," ",$s);
			if(stristr($w,"'")) $s = preg_replace("/ ".$w." ?/"," ",$s);
			$s = str_replace(" ".$w." "," ",$s);
		}
	}

	$s = preg_replace("/ +/", "-", $s);
	$s = str_replace("&", "-e-", $s);
	$s = preg_replace("/[^0-9a-z_\-]/i", '', $s);
	$s = preg_replace("/^-+/", '', $s);
	$s = preg_replace("/\_+/", "_", $s);
	$s = preg_replace("/\-+/", "-", $s);
	$s = preg_replace("/\-+$/", "", $s);
	return $s;
}
?>