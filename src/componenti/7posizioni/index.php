<?php


//gestione utenti component
$root="../../../";
include($root."src/_include/config.php");
include($root."src/_include/grid.class.php");
include($root."src/_include/formcampi.class.php");
include("_include/posizioni.class.php");

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

//::aggiorno posizione::
print $ambiente->setPosizione( "Positions" );

$obj = new Posizioni();

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
	case "modifica":
	case "duplica":
		$risultato = $obj->getDettaglio( $parameter, $command );
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
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
			if ($command != "modificaStep2reload") $html = returnmsgok("Done.","reload");
				else $html = returnmsgok("Done.","load ".$_SERVER['SCRIPT_NAME']."?op=modifica&id={$parameter}");
		}
		break;
	case "elimina":
		$risultato = $obj->deleteItem($parameter);
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} else {
			if($risultato == "") {
				$html = returnmsgok("Deleted.","load ".$_SERVER['SCRIPT_NAME']."");
			} else {
				$html = returnmsg( $risultato ,"jsback");
			}
		}
		break;
	case "eliminaSelezionati":
		$risultato = $obj->eliminaSelezionati($_POST);
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} else $html = returnmsgok("Deleted.","load ".$_SERVER['SCRIPT_NAME']."");
		break;
	case "aggiungi":
		$risultato = $obj->getDettaglio();
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} else $html = $risultato;
		break;
	case "aggiungiStep2reload":
	case "aggiungiStep2":
		$risultato = $obj->updateAndInsert($_POST,$_FILES);
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} elseif(str_replace(strstr($risultato,"|"),"",$risultato)=="-1") {
			$html = returnmsg(str_replace("|","",strstr($risultato,"|")),"jsback");
		} else {
			$id = str_replace( "|","",stristr( $risultato, "|")) ; 
			if ($command != "aggiungiStep2reload") $html = returnmsgok("Done.","reload");
				else $html = returnmsgok("Done.","load ".$_SERVER['SCRIPT_NAME']."?op=modifica&id=".$id."");
		}
	}

}

if ($html=="") {
	$html = loadTemplateAndParse ("template/elenco.html");
	$html = str_replace("##corpo##", ($obj->elenco($combotipo,$combotiporeset,$keyword)), $html);
	$html = str_replace("##keyword##", $keyword, $html);
	$html = str_replace("##bottoni1##","<a href=\"$obj->linkaggiungi\" title=\"Add new\" class='aggiungi'>$obj->linkaggiungi_label</a>", $html);
	$html = str_replace("##bottoni2##","<a href=\"$obj->linkeliminamarcate\" title=\"Delete selected\" class='elimina'>$obj->linkeliminamarcate_label</a>", $html);
	$html = str_replace("##combotipo##", $obj->getHtmlcombotipo($combotipo), $html);
}


print $html;

?>