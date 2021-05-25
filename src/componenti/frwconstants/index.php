<?php
//gestione variabili su db
$root="../../../";
include($root."src/_include/config.php");
include($root."src/_include/grid.class.php");
include($root."src/_include/formcampi.class.php");
include("_include/constants.class.php");

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

$ambiente->setPosizione("Settings");

$obj = new Constants();

$html="";

if (isset($_GET["op"])) {
	$command = $_GET["op"];
	if (isset($_GET["id"])) $parameter = $_GET["id"]; else $parameter="";
} else if (isset($_POST["op"])) {
	$command = $_POST["op"];
	if (isset($_POST["id"]))	$parameter = $_POST["id"]; else $parameter="";
}


if (isset($_GET["keyword"])) {
	$keyword= $_GET["keyword"];
} else $keyword="";
if (isset($_GET["combotiporeset"])) {
	$combotiporeset= $_GET["combotiporeset"];
} else $combotiporeset="";

//esegue eventuali comandi passati
if (isset($command)) {

	switch ($command) {
	case "modifica":
		$risultato = $obj->getDettaglio( $parameter );
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} else $html = $risultato;
		break;
	case "modificaStep2":
		$risultato = $obj->updateAndInsert($_POST,$_FILES);
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} elseif(str_replace(strstr($risultato,"|"),"",$risultato)=="-1") {
			$html = returnmsg(str_replace("|","",strstr($risultato,"|")),"jsback");
		} else $html = returnmsgok("Configuration saved.","reload");
		break;
	/*case "elimina":
		$risultato = $obj->deleteItem($parameter);
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} else $html = returnmsgok("Record rimosso.","load index.php");
		break;
	case "eliminaSelezionati":
		$risultato = $obj->eliminaSelezionati($_POST);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.","jsback");
		} else $html = returnmsgok("Record eliminati","load index.php");
		break;
	case "aggiungi":
		$risultato = $obj->getDettaglio();
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.","jsback");
		} else $html = $risultato;

		break;
	case "aggiungiStep2":
		$risultato = $obj->updateAndInsert($_POST,$_FILES);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato.","jsback");
		} elseif(str_replace(strstr($risultato,"|"),"",$risultato)=="-1") {
			$html = returnmsg(str_replace("|","",strstr($risultato,"|")),"jsback");
		} else $html = returnmsgok("Record inserito.","reload");
	*/
	}

}

if ($html=="") {
	$elenco = $obj->elenco($combotiporeset,$keyword);
	if($elenco!="0") {
		$html = loadTemplateAndParse("template/elenco.html");
		$html = str_replace("##corpo##", $elenco, $html);
		$html = str_replace("##keyword##", $keyword, $html);
		//$html = str_replace("##bottoni1##","<a href=\"$obj->linkaggiungi\" title=\"Aggiungi un record\" class=\"aggiungi\">$obj->linkaggiungi_label</a>", $html);
		//$html = str_replace("##bottoni2##","<a href=\"$obj->linkeliminamarcate\" title=\"Elimina i record selezionati\" class=\"elimina\">$obj->linkeliminamarcate_label</a>", $html);
	} else {
		$html = returnmsg("You're not authorized.");
	}
}


print $html;

?>