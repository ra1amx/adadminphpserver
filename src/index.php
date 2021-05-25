<?php

$root = "../"; // distanza da aaa
include("_include/config.php");

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

if(PRIMO_COMPONENTE_DA_MOSTRARE=="") {
	$html = loadTemplateAndParse(
		$root."data/".DOMINIODEFAULT."/layout-home.php",
		$defaultReplace
	);

	echo $html;

} else {
	$r = execute_row("select * from frw_componenti where nome='".addslashes(PRIMO_COMPONENTE_DA_MOSTRARE)."'");
	if(isset($r['urlcomponente'])) {
		header("Location: ".$root."src/".$r['urlcomponente']);
		die;
	}
}


?>