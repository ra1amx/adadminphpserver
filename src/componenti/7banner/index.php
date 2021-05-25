<?php

//gestione banner
$root="../../../";
include($root."src/_include/config.php");
include($root."src/_include/grid.class.php");
include($root."src/_include/formcampi.class.php");
include("../php-zip/Zip.php");
include("_include/banner.class.php");

function moremenu($p) {
	global $obj,$root;
	$p2 = explode("|^",$p);

	//$pics = loadgallery( $root."data/dbimg/7banner/" ,$p2[2].'_',"","array");
	////print_r($pics);
	////die;
	//if(isset($pics[0][0])) $n = "<img src='".$pics[0][0]."'/>"; else $n = "";	

	$n = "";

	$pic = '<div class="pic">'.$n.'</div>';
	if($p2[1]=="S") {
		return $pic."<div class=\"td\">".$p2[0]."</div>";
	}
	return $pic . '<div class="td">'.$p2[0]."<div class='more'>
		".($p2[1]=='P' ? "<a href=\"javascript:;\" onclick=\"setStato('go',".$p2[2].")\" class='go'>Go</a>" : "<a href=\"javascript:;\" onclick=\"setStato('pause',".$p2[2].")\" class='pausa'>Pause</a>")."
		</div></div>";
}

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

//::aggiorno posizione::
print $ambiente->setPosizione("Banner");


$obj = new Banner();
$obj->uploadDir = $root."data/dbimg/7banner/";
$obj->maxX= 2000;
$obj->maxY= 1800;
$obj->maxKB= 1000;
$obj->max_files= 1;

$html=""; $command="";

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

if (isset($_GET["combobanner"])) {
	$combobanner = $_GET["combobanner"];
} else $combobanner="";

if (isset($_GET["combobannerreset"])) {
	$combobannerreset = $_GET["combobannerreset"];
} else $combobannerreset="";

if (isset($_GET["enddate"])) {
	$enddate = $_GET["enddate"];
} else $enddate= null;

if (isset($_GET["startdate"])) {
	$startdate = $_GET["startdate"];
} else $startdate= null;




//esegue eventuali comandi passati
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
			$html = returnmsg("You're not authorized.","jsback");
		} elseif(str_replace(strstr($risultato,"|"),"",$risultato)=="-1") {
			$html = returnmsg(str_replace("|","",strstr($risultato,"|")),"jsback");
		} else {
			if ($command != "modificaStep2reload") $html = returnmsgok("Done.","reload");
				else $html = returnmsgok("Done.","load index.php?op=modifica&id={$parameter}");
		}
		break;
	case "elimina":
		$risultato = $obj->deleteItem($parameter);
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} else $html = returnmsgok("Deleted.","load index.php");
		break;
	case "eliminaSelezionati":
		$risultato = $obj->eliminaSelezionati($_POST);
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} else $html = returnmsgok("Deleted.","load index.php");
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
				else $html = returnmsgok("Done.","load index.php?op=modifica&id=".$id."");
		}
		break;
	/*
	case "scripttags":
		$risultato = $obj->getScriptTags();
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} else {
			$html = $risultato;
		}
		break;
	*/
	case "stats":
		$risultato = $obj->getCharts($parameter,$combobanner,$startdate,$enddate);
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} else {
			$html = $risultato;
		}
		break;
	default:
		$elenco = $obj->elenco($combotipo,$combotiporeset,$keyword);
		if($elenco!="0") {
			$html = loadTemplateAndParse ("template/elenco.html");
			$html = str_replace("##corpo##", $elenco, $html);

			$html = str_replace("##keyword##", $keyword, $html);
			$html = str_replace("##bottoni1##","<a href=\"$obj->linkaggiungi\" title=\"Add record\" class='aggiungi'>$obj->linkaggiungi_label</a>", $html);
			if($session->get("idprofilo")>5)
				$html = str_replace("##bottoni2##","<a href=\"$obj->linkeliminamarcate\" title=\"Delete selected records\" class='elimina'>$obj->linkeliminamarcate_label</a>", $html);
			else
				$html = str_replace("##bottoni2##","", $html);

			//$html = str_replace("##bottoni3##","<a href=\"".$_SERVER['REQUEST_URI']."\" title=\"ricarica\" class='ricarica'>Refresh</a>", $html);
			$html = str_replace("##combotipo##", $obj->getHtmlcombotipo($combotipo), $html);
		} else {
			$html = returnmsg("You're not authorized.");
		}

}






print $html;

?>