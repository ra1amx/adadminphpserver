<?php
//gestione utenti component
$root="../../../";
include($root."src/_include/config.php");
include($root."src/_include/formcampi.class.php");
include("_include/mioprofilo.class.php");

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

//::aggiorno posizione::
print $ambiente->setPosizione( "My profile" );

$io = new mioprofilo();

$html="";

if (isset($_GET["op"])) {
	$command = $_GET["op"];
	if (isset($_GET["id"])) $parameter = $_GET["id"]; else $parameter="";
} else if (isset($_POST["op"])) {
	$command = $_POST["op"];
	if (isset($_POST["id"]))	$parameter = $_POST["id"]; else $parameter="";
}

if(!isset($command) || $command=="") {$command = "modifica"; }
if(!isset($parameter) || $parameter=="") {$parameter = $session->get("idutente"); }


//esegue eventuali comandi passati
if (isset($command)) {

	switch ($command) {
	case "modifica":
		$risultato = $io->getDettaglio();
		if ($risultato=="0") {
			$html = returnmsg("You're not authorized.","jsback");
		} else $html = $risultato;
		break;
	case "modificaStep2":
		if($_SERVER['HTTP_HOST']!="www.barattalo.it") {
			$risultato = $io->update($_POST,$_FILES);
			if ($risultato=="0") {
				$html = returnmsg("You're not authorized.","jsback");
			} else $html = returnmsgok("Done.","reload");
			break;
		} else {
			$html = returnmsg("This is a demo version, you can't do that.","jsback");
		}
	}

}

//::aggiorno posizione::

print $html;
?>