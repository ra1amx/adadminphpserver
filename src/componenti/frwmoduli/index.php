<?php
//gestione strumentario
$root="../../../";
include($root."src/_include/config.php");
include($root."src/_include/grid.class.php");
include($root."src/_include/formcampi.class.php");
include("_include/moduli.class.php");
include("../frwcomponenti/_include/componenti.class.php");

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();


//::aggiorno posizione::

$obj = new Moduli();

$html="";

if (isset($_GET["op"])) {
	$command = $_GET["op"];
	if (isset($_GET["id"])) $parameter = $_GET["id"]; else $parameter="";
} else if (isset($_POST["op"])) {
	$command = $_POST["op"];
	if (isset($_POST["id"]))	$parameter = $_POST["id"]; else $parameter="";
}

if (isset($_GET["combotipo"])) {
	$combotipo = $_GET["combotipo"];
} else $combotipo="";

if (isset($_GET["combotiporeset"])) {
	$combotiporeset = $_GET["combotiporeset"];
} else $combotiporeset="";

if (isset($_GET["keyword"])) {
	$keyword= $_GET["keyword"];
} else $keyword="";


//esegue eventuali comandi passati
if (isset($command)) {

	switch ($command) {
	case "sql":
		$risultato = $obj->getSql( $parameter );
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato 1.","jsback");
		} else $html = $risultato;
		break;
	case "modifica":
		$risultato = $obj->getDettaglio( $parameter );
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato 1.","jsback");
		} else $html = $risultato;
		break;
	case "modificaStep2reload" :
	case "modificaStep2" :
		$risultato = $obj->updateAndInsert($_POST,$_FILES);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato 2.","jsback");
		} elseif(str_replace(strstr($risultato,"|"),"",$risultato)=="-1") {
			$html = returnmsg(str_replace("|","",strstr($risultato,"|")),"jsback");
		} else {
			if ($command != "modificaStep2reload") $html = returnmsgok("Record modificato.","reload");
				else $html = returnmsgok("Record modificato.","load index.php?op=modifica&id={$parameter}");
		}
		break;
	case "elimina":
		$risultato = explode("|", $obj->deleteItem($parameter));
		if ($risultato[0]<0) $html = returnmsg($risultato[1],$risultato[2]);
			else $html = returnmsgok($risultato[1],$risultato[2]); 
		break;
	case "eliminaSelezionati":
		$risultato = explode("|", $obj->eliminaSelezionati($_POST));
		if ($risultato[0]<0) $html = returnmsg($risultato[1],$risultato[2]);
			else $html = returnmsgok($risultato[1],$risultato[2]); 
		break;
	case "aggiungi":
		$risultato = $obj->getDettaglio();
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato 5.","jsback");
		} else $html = $risultato;
		break;
	case "aggiungiStep2reload":
	case "aggiungiStep2":
		$risultato = $obj->updateAndInsert($_POST,$_FILES);
		if ($risultato=="0") {
			$html = returnmsg("Non sei autorizzato 6.","jsback");
		} elseif(str_replace(strstr($risultato,"|"),"",$risultato)=="-1") {
			$html = returnmsg(str_replace("|","",strstr($risultato,"|")),"jsback");
		} else {
			$id = str_replace( "|","",stristr( $risultato, "|")) ; 
			if ($command != "aggiungiStep2reload") $html = returnmsgok("Record inserito.","reload");
				else $html = returnmsgok("Record inserito.","load index.php?op=modifica&id=".$id."");
		}
		break;
	case "profila":
		$risultato = $obj->profila( $parameter );
		if ($risultato=="0") $html = returnmsg("Non sei autorizzato.");
			else if ($risultato=="1") $html = returnmsg("Il modulo non ha componenti...");
			else $html = returnmsgok("Le funzionalit&agrave; dei componenti di questo modulo sono state distribuite agli utenti abilitati.<br><br>".$risultato);
		break;
	}

}

if ($html=="") {
	$html = loadTemplateAndParse ("template/elenco.html");
	$html = str_replace("##corpo##", ($obj->elenco($combotipo,$combotiporeset,$keyword)), $html);
	$html = str_replace("##keyword##", $keyword, $html);
	$html = str_replace("##bottoni1##","<a href=\"$obj->linkaggiungi\" class='aggiungi' title=\"Aggiungi un record\">$obj->linkaggiungi_label</a>", $html);
	$html = str_replace("##bottoni2##","<a href=\"$obj->linkeliminamarcate\" class='elimina' title=\"Elimina i record selezionati\">$obj->linkeliminamarcate_label</a>", $html);
	$html = str_replace("##combotipo##", $obj->getHtmlcombotipo($combotipo), $html);
}


print $html;

?>