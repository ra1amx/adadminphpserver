<?php

//----------------------------------------------------------------------------
// AdAdmin Config file
// This software it's not free, you can buy it on CodeCanyon. Thank you.
// Author page: http://codecanyon.net/user/ginoplusio
//----------------------------------------------------------------------------

// USER SETTINGS
//----------------------------------------------------------------------------

// database configuration
define("WEBDOMAIN",		"adadmin.ru");
define("DEFDBNAME",		"adadmin");
define("DEFUSERNAME",	"adadmin");
define("DEFDBPWD",		"adadmin123");
//----------------------------------------------------------------------------
// these are more settings, you should not change them!
//----------------------------------------------------------------------------

//header('Content-type: text/html; charset=utf-8'); // 07/04/2019 moved after session in config
ini_set('default_charset', 'UTF-8');
setlocale(LC_CTYPE, 'ru_RU.UTF-8');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

// mainfolder
DEFINE("DOMINIODEFAULT","tema");// graphic theme folder

// first componente to open
define("PRIMO_COMPONENTE_DA_MOSTRARE","BANNER");
define("RESTRICT_MODULO","Ad Server");

// extra user panel informations
define("EXTRA_USER_LINK","frwextrauserdata/index.php");
define("DELETE_USER_LINK",true); // if false block user deletion

// error logs
define("LOGS_FILENAME", "data/logs/log.txt");	// log file (store some informations, andrà usato con $root + LOGS_FILENAME)
define("SHOW_ERRORS", true);	// show errors
define("SEND_ERRORS_MAIL", "");	// if filled with an email send error to this email
define("STOP_ON_ERROR", false);	// if true die after an error

// jquery inclusion
define("JQUERYINCLUDE",'
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
	<link href="//fonts.googleapis.com/css?family=Titillium+Web" rel="stylesheet">
');


//
// auto detect folder with / at beginning
$currentdir = __FILE__;
$currentdirAr = explode("/",$currentdir);
//$currentdir = $currentdirAr[ count($currentdirAr) - 2];
$currentdir = "/".ltrim($currentdir,"/");
if( !stristr($_SERVER['REQUEST_URI'] , $currentdir."/"))  $currentdir = ".";
DEFINE("PONSDIR",$currentdir);

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') $tmpurl = "https";  else $tmpurl = "http"; 
$tmpurl .= "://"; 
$tmpurl .= $_SERVER['HTTP_HOST']; 
$tmpurl .= $currentdir != "." ? $currentdir : ""; 
DEFINE("WEBURL",$tmpurl); 


/*
// URL WEB without final /
if(!isset($_SERVER['SCRIPT_URI'])) {
	$tmppath = str_replace("/pons-settings.php","",__FILE__);
	$tmppath = str_replace(rtrim($_SERVER["DOCUMENT_ROOT"],"/"),"",$tmppath);
	DEFINE("WEBURL", (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on" ? "https://" : "http://" ) . rtrim($_SERVER["HTTP_HOST"],"/").$tmppath);
} else {
	// SECOND METHOD
	$tmppath = str_replace("/pons-settings.php","",__FILE__);
	$artemp = explode("/",$tmppath);
	$folder = $artemp[count($artemp)-1];
	$artemp = explode("/",$_SERVER['SCRIPT_URI']);
	$url = ""; 
	$flag = false;
	for($i=count($artemp)-1;$i>=0;$i--) {
		if($artemp[$i] == $folder || $flag) {
			$url = $artemp[$i] . "/" .$url;
			$flag=true;
		}
	}
	DEFINE("WEBURL", rtrim($url,"/"));
}
*/


?>