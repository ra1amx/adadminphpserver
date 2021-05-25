<?php

if (function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Rome');

$root="";
include($root."pons-settings.php");
include($root."src/_include/comode.php");


$idbanner=isset($_GET['b']) ? (integer)$_GET['b'] : 0;
if (!$idbanner) die("no banner");

if (!Connessione()) die(); else CollateConnessione();

redirectBanner($idbanner);

/*
	funzioni
*/

function redirectBanner($idbanner) {
	global $conn;
	$sql = "UPDATE 7banner SET nu_clicks = nu_clicks + 1 where id_banner={$idbanner}";
	$conn->query($sql) or die("errore redirect banner (1)");
	$r = execute_row("SELECT de_url FROM 7banner WHERE id_banner={$idbanner}");
	// statistiche
	$conn->query("update 7banner_stats set nu_click=nu_click+1 where id_day='".date("Y-m-d")."' and cd_banner='".$idbanner."'");
	$s = str_replace("[timestamp]",date("YmdHis"),$r['de_url']);
	$s = str_replace("[RANDOM]",rand(10000,99999).date("YmdHis"),$s);
	$s = str_replace("[randnum]",rand(10000,99999).date("YmdHis"),$s);
	$s = str_replace("[rand]",rand(10000,99999).date("YmdHis"),$s);
	if(!$s) {
		$s="/";
	}
	header("Location: {$s}");
	die;
}




?>