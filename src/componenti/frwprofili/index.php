<?php

//gestione utenti component
$root="../../../";
include($root."src/_include/config.php");
include($root."src/_include/grid.class.php");
include("../frwcomponenti/_include/componenti.class.php");
include("_include/profili.class.php");

$ambiente->setPosizione("Profili");
$html = "";

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

$obj = new profili();

//$html="";

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
		} else if($risultato=="1") {
			$html = returnmsg ("Esiste già un profilo con la label specificata.");
		} else if($risultato=="3") {
			$html = returnmsg ("Esiste già un profilo con l'ID specificato.");
		} else $html = returnmsgok("Il profilo è stato modificato.");
		break;
	case "eliminap":
		$risultato = $obj->deleteP($parameter);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else if($risultato=="1") {
			$html = returnmsg ("Ci sono degli utenti con questo profilo, non puoi cancellarlo.");
		} else $html = returnmsgok("Il profilo è stato rimosso.");
		break;
	case "aggiungi":
		$risultato = $obj->getDettaglio();
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		}  else $html = $risultato;
		break;
	case "aggiungiStep2":
		$risultato = $obj->updateAndInsert($_POST);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else if($risultato=="1") {
			$html = returnmsg ("Esiste già un profilo con la label specificata.");
		} else if($risultato=="3") {
			$html = returnmsg ("Esiste già un profilo con l'ID specificato.");
		} else $html = returnmsgok("Il profilo è stato aggiunto.");

		break;

	}

}

if ($html=="") {
	$html = loadTemplateAndParse("template/elenco.html");
	$html = str_replace("##aggiungi##","<a href=\"$obj->linkaggiungi\" title=\"Aggiungi un record\" class=\"aggiungi\">$obj->linkaggiungi_label</a>", $html);

	$html = str_replace("##corpo##", $obj->elenco(), $html);
}


print $html;

?>