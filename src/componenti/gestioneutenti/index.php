<?php
//gestione utenti component
$root="../../../";
include($root."src/_include/config.php");
include($root."src/_include/grid.class.php");
include("_include/gestioneutenti.class.php");


if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

//::aggiorno posizione::
print $ambiente->setPosizione( "Users" );


$gu = new gestioneutenti("frw_utenti",40,"cognome","asc",0);
if (isset($ARRAY_EXTRA_USER_LABELS)) $gu->scegliDaInsiemeLabelProfili=$ARRAY_EXTRA_USER_LABELS;

function decrypta($s) { $cr = new cryptor(); return $cr->decrypta($s); }

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
		$risultato = $gu->getDettaglio( $parameter, loadTemplateAndParse ("template/layout-utente-form-update.html"));
		if ($risultato=="0") {
			$html = returnmsg ("Your profile can't edit this user.","link index.php");
			$html = str_replace("##msg##","Your profile can't edit this user.",$html);

		} else $html = $risultato;
		break;
	case "modificaStep2":
		$risultato = $gu->updateUser($_POST);
		if ($risultato=="0") {
			$html = returnmsg ("Your profile can't edit this user.","link index.php");
		} else if($risultato=="1") {
			$html = returnmsg ("The username you've choose is already used.","jsback");
		} else $html="";
		break;
	case "elimina":
		$risultato = $gu->deleteUser($parameter);
		if ($risultato=="0") {
			$html = returnmsg ("Your user can't delete this user.","link index.php");
		} else if ($risultato=="1") {
			$html = returnmsg ("You can't delete your user.","link index.php");
		} else if ($risultato=="2") {
			$html = returnmsg ("Your user can't delete this user.","link index.php");
		} else $html="";
		break;

	case "aggiungi":
		$risultato = $gu->getInsertForm( loadTemplateAndParse ("template/layout-utente-form-insert.html") );
		if ($risultato=="0") {
			$html = returnmsg ("Your user can't add a user.","link index.php");
		} else $html = $risultato;
		
		break;
	case "aggiungiStep2":
		$risultato = $gu->insertNewUser($_POST);
		if ($risultato=="0") {
			$html = returnmsg ("Your user can't add a user.","link index.php");
		} else if($risultato=="1") {
			$html = returnmsg ("The username you've choose is already used.","link index.php");
		} else $html="";
		break;

	case "personifica":
			$user = execute_row("SELECT username, password FROM frw_utenti where id='$parameter'");
		if($user && in_array( $session->get("idprofilo"), array(20,999999) )) {
			$cr = new cryptor();
			$login->actionurl = $root."src/login.php";
			$out = $login->getLoginForm("Autologin");
			$out = str_replace('<input name="password" type="password"','<input name="password" type="password" value="'.$cr->decrypta($user['password']).'"',$out);
			$out = str_replace('<input name="utente"','<input name="utente" value="'.$user['username'].'"',$out);
			$out = str_replace('</form>','</form><script>document.getElementById("loginform").submit();</script>',$out);

			$logger->addlog( 2 , "{fine sessione utente ".$session->get("username").", id=".$session->get("idutente")."}" );
			$session->finish();

			echo $out;
		} 
		die;

	}

}

if ($html=="") {
	$html = loadTemplateAndParse ("template/layout-gestioneutenti-main.html");
	$html = str_replace("##corpo##", $gu->elencoUtenti(), $html);
	if ($session->get("GESTIONEUTENTI_WRITE")=="true") {
		if( in_array( $session->get("idprofilo"), array(20,999999) )) {
			$html = str_replace("##aggiungi##","<a href=\"$gu->linkaggiungi\" class='aggiungi'>$gu->linkaggiungi_label</a>",$html);
		} else {
			$html = str_replace("##aggiungi##","",$html);
		}
	}
}

//::aggiorno posizione::
//print $ambiente->setPosizione("Configurazione / Gestione Utenti");

print $html;
?>