<?php
$root="../";
include("_include/config.php");

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();


$flag = "true";
if ($login->externalUserLogout!="") {
	/*
		se e' definito un sistema di logout esterno al framework
		qui viene richiamata la funzione esterna che se ritorna "false"
		blocca il logout
	*/
	$flag = call_user_func($login->externalUserLogout);
}
if ($flag=="true") {
	$logger->addlog( 2 , "{fine sessione utente ".$session->get("username").", id=".$session->get("idutente")."}" );
	$session->finish();
	print $ambiente->loadLogin("See you soon.");
}
?>
