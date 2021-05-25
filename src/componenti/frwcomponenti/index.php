<?php

//gestione utenti component
$root="../../../";
include($root."src/_include/config.php");
include($root."src/_include/grid.class.php");
include("_include/componenti.class.php");

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

$obj = new componenti();

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
	case "profila":
		$risultato = $obj->profila( $parameter );
		if ($risultato=="0") $html = returnmsg("Non sei autorizzato.");
			else if ($risultato=="1") $html = returnmsg("Il componente non ha funzionalit&agrave;...");
			else if ($risultato=="2") $html = returnmsg("il componente non &egrave; installato in nessun modulo...");
			else if ($risultato=="3") $html = returnmsg("il componente non &egrave; associato a nessun profilo...");
			else $html = returnmsgok("Le funzionalit&agrave; del componente sono state distribuite agli utenti abilitati.<br><br>".$risultato);
		break;
	case "modificaf":
		$risultato = $obj->getDettaglioF( $parameter );
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else $html = $risultato;
		break;
	case "modificafStep2":
		$risultato = $obj->updateAndInsertF($_POST);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else if($risultato=="1") {
			$html = returnmsg ("Il componente a cui questa funzionalit&agrave; appartiene non &egrave; associato ad alcun modulo. La funzionalità non &egrave; stata inserita.","back");
		} else $html = returnmsgok("La funzionalit&agrave; &egrave; stata modificata.","load index.php?op=modifica&id=".$_POST['idcomponente']);
		break;
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
			$html = returnmsg ("Esiste gi&agrave; un componente con il nome specificato.");
		} else $html = returnmsgok("Il componente &egrave; stato modificato.","load index.php");
		break;
	case "eliminac":
		$risultato = $obj->deleteC($parameter);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else $html = returnmsgok("Il componente &egrave; stato rimosso.");
		break;
	case "eliminaf":
		$risultato = $obj->deleteF($parameter);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else $html = returnmsgok("La funzionalit&agrave; &egrave; stata rimossa.","back");
		break;
	case "aggiungif":
		$risultato = $obj->getDettaglioF("",$parameter);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else $html = $risultato;
		
		break;
	case "aggiungifStep2":
		$risultato = $obj->updateAndInsertF($_POST);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else if($risultato=="1") {
			$html = returnmsg ("Il componente a cui questa funzionalit&agrave; appartiene non &egrave; associato ad alcun modulo. La funzionalit&agrave; non &egrave; stata inserita.","back");
		} else $html = returnmsgok("La funzionalit&agrave; &egrave; stata inserita.","back");
		break;
	case "aggiungi":
		$risultato = $obj->getDettaglio();
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else $html = $risultato;
		
		break;
	case "aggiungiStep2":
		$risultato = $obj->updateAndInsert($_POST);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.");
		} else if($risultato=="1") {
			$html = returnmsg ("Esiste gi&agrave; un componente con il nome specificato.");
		} else $html = returnmsgok("Il componente &egrave; stato installato.");
		break;

	}

}

if ($html=="") {
	$html = loadTemplateAndParse("template/elenco.html");
	$html = str_replace("##corpo##", $obj->elenco(), $html);
	$html = str_replace("##aggiungi##", "<a href=\"$obj->linkaggiungi\" class='aggiungi'>$obj->linkaggiungi_label</a>",$html);

}


print $html;

?>