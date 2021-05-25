<?php

$root="../"; $public=true;
include($root."src/_include/config.php");


if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

if(!table_exists("frw_vars")) {trigger_error("AdAdmin not installed. Please run install.php"); die; }

//$login = new Login();

$msg = setVariabile("msg","");
$login_form = "";

//
// prova a fare la login con i dati che forse ci sono nel post
if (!$login->logged()) {
	//.se non riesce allora genera la form
	$session->finish();
	$login_form = $login->getLoginForm($msg);
} else {
	header("Location: index.php");
	die;
}

echo $login_form;

?>