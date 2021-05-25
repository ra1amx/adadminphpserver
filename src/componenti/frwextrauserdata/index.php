<?php

//gestione utenti component
$root="../../../";
include($root."src/_include/config.php");
include($root."src/_include/formcampi.class.php");
include($root."src/componenti/gestioneutenti/_include/user.class.php");
include("_include/extrauserdata.class.php");

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

//::aggiorno posizione::
print $ambiente->setPosizione("Users");


$obj = new Extrauserdata();

$html="";

if (isset($_GET["op"])) {
	$command = $_GET["op"];
	if (isset($_GET["id"])) $parameter = $_GET["id"]; else $parameter="";
} else if (isset($_POST["op"])) {
	$command = $_POST["op"];
	if (isset($_POST["id"]))	$parameter = $_POST["id"]; else $parameter="";
}

//esegue eventuali comandi passati
if (isset($command)) {
	switch ($command) {
	case "modifica":
		$risultato = $obj->getDettaglio( $parameter );
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else $html = $risultato;
		break;
	case "modificaStep2":
		$risultato = $obj->updateAndInsert($_POST);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else $html = returnmsgok("Dati aggiuntivi modificati.","load ../gestioneutenti/index.php");
		break;
	case "aggiungiStep2":
		$risultato = $obj->updateAndInsert($_POST);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else $html = returnmsgok("Dati aggiuntivi modificati.","load ../gestioneutenti/index.php");
		break;
	}
}

print $html;

?>