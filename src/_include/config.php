<?php
//phpInfo();
//die;

// per far funzionare file_get_contents
// talvolta questo problema richiede di inserire questo settaggio in php.ini
ini_set("allow_url_fopen", 1);

$conn = false;
if(!isset($root)) die("no root");

$public = isset($public) ? $public : false;  // $public = false (default, for pages that need login)

include($root."pons-settings.php");


if( RESTRICT_MODULO == "BANNER" ) {
	die("<pre>Old configuration found.\nPlease, open yuur pons-settings.php file and change line 39 from this:\n\n\t\tdefine(\"RESTRICT_MODULO\",\"BANNER\");\n\nto this:\n\n\t\tdefine(\"RESTRICT_MODULO\",\"Ad Server\");\n\n</pre>");
}


/*
	se nel php.ini non è abilitato il "magic_quotes_gpc" questo sistema lo emula
	aggiungendo gli slashes davanti a tutte le variabili che entrano i get e post
*/
//if (!get_magic_quotes_gpc()) {
	// funzione ricorsiva per l'aggiunta degli slashes ad un array  
	//function magicSlashes($element) {
		//if (is_array($element)) return array_map("magicSlashes", $element); else return addslashes($element);  
	//}
	// Aggiungo gli slashes a tutti i dati GET/POST/COOKIE  
	//if (isset ($_GET)     && count($_GET))    $_GET    = array_map("magicSlashes", $_GET);  
	//if (isset ($_POST)    && count($_POST))   $_POST   = array_map("magicSlashes", $_POST);  
	//if (isset ($_COOKIES) && count($_COOKIES))$_COOKIE = array_map("magicSlashes", $_COOKIE);  
//}

if (function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Rome');
if( phpversion() >= '5.0' ) @ini_set('zend.ze1_compatibility_mode', '0');// for PHP 5 compatibility



/*
v 3.81 aggiunto ini_set allow_url_fopen 1 che certe volte previene problemi

*/

// array che contiene le sostituzioni di default per utilizzare rapidamente alcuni template
// (e' utilizzato nelle chiamate a loadTemplateAndParse)
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°
$defaultReplace=array(
	"##root##"=>$root,
	"##DOMINIO##"=>DOMINIODEFAULT,
	"##JQUERYINCLUDE##"=>JQUERYINCLUDE,
	"##PONSDIR##"=>PONSDIR,
	"##rand##"=>rand(1,111111),
	"##VER##"=>"3.82"
);


/*
°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°
	FILE DI CONFIGURAZIONE GENERALE PER TUTTI I DOMINI, E' INCLUSO SEMPRE
°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°
*/
include($root."src/_include/comode.php");
include($root."src/_include/cryptor.class.php");
include($root."src/_include/logger.class.php");

if(phpversion()<"5") {
	include($root."src/_include/php4_session.class.php");
} else {
	include($root."src/_include/session.class.php");
}
include($root."src/_include/ambiente.class.php");

$ambiente = new ambiente();
$logger = new logger();

$session=new session();

header('Content-type: text/html; charset=utf-8');

if(!$session->get("CONST_LOGO")) {
	// leggo le variabili CONST_ da frwars e le metto in constanti
	if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

	$sql = "select * from frw_vars WHERE de_nome like 'CONST_%'";
	$rs = $conn->query($sql) or trigger_error($conn->error);
	while($riga = $rs->fetch_array()) {
		$NAME =str_replace("CONST_","",$riga['de_nome']);
		define($NAME, $riga['de_value']);
		//echo $NAME . ":::" . $riga['de_value']."<br>";
		$session->register($riga['de_nome'],$riga['de_value']);
	}

	//print_r(var_export(get_defined_constants(true)['user'], true));
	//die ("OK");

} else {
	foreach($_SESSION as $k=>$v) {
		if(preg_match("/^CONST\_/",$k)) {
			$NAME =str_replace("CONST_","",$k);
			define($NAME, $v);
		}
	}
}


include($root."src/_include/login.class.php");
$login = new login();


// controllo login
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°
if (!$public && !$login->logged()) {

	//se non riesce allora genera la form
	/*
		questo "if" è necessario se no continua a ricaricarsi la pagina di login!
	*/
	$session->finish();

	print $ambiente->loadLogin("Session expired.");
	die;

}

// ---------------------------------------------------------------------------------
// verifica moduli da installare nel php per far funzionare
// il framework.
if(!ini_get("short_open_tag")) trigger_error("You need to activate 'short open tag'.");
if(!function_exists("mb_detect_encoding")) trigger_error("You need to activate php 'MBSTRING'.");

// ---------------------------------------------------------------------------------


// logga i file chiamati.
//$logger->addlog( $session->get("username")."(id=".$session->get("idutente").")" );
?>