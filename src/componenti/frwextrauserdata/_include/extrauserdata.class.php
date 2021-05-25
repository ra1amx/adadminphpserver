<?php

class Extrauserdata {

	var $tbdb;	//tabella del database che contiene gli utenti

	var $start;	// posizione del primo record visualizzato
	var $omode;	// asc|desc
	var $oby;	// campo della tabella $tbdb utilizzato per ordinare
	var $ps;	// numero di righe per pagina nell'elenco

	var $linkaggiungi;	//link utilizzato per "aggiungere"
	var $linkaggiungi_label;
	var $linkaggiungi_icon;

	var $linkmodifica;	//link utilizzato per il comando "modifica"
	var $linkmodifica_label;

	var $linkelimina;	//link utilizzato per il comando "elimina"
	var $linkelimina_label;

	var $gestore;
	var $MAX_USER_LEVEL;


	function __construct ($tbdb="cnt_tipi_pagine",$ps=20,$oby="de_tipo",$omode="asc",$start=0) {
		global $session,$root,$conn;
		$this->MAX_USER_LEVEL=999999;
		$this->gestore = $_SERVER["PHP_SELF"];
		$this->tbdb = $tbdb;
		//se ci sono impostazioni inviate in get o in post usa quelle
		//se non ci sono quelle usa quelle in session
		//se non ci sono neanche in session usa i valori passati.

		$this->linkaggiungi = "$this->gestore?op=aggiungi";
		$this->linkaggiungi_icon = "";
		$this->linkaggiungi_label = "Aggiungi un nuovo tipo";

		$this->linkmodifica = "$this->gestore?op=modifica&id=##id_tipo##";
		$this->linkmodifica_label = "";

		$this->linkelimina = "javascript:confermaDelete('##id_tipo##');";
		$this->linkelimina_label = "<img src=\"images/delete.gif\" alt=\"elimina\" align=\"absmiddle\" border=\"0\">";

		if ($session->get("GESTIONEUTENTI_READ") == "") {
			/*
				controllo che l'utente specificato abbia la funzionalita' attivata.
				se non c'e' nella sessione cerco questa funzionalita' nel db.
			*/

			$sql = "SELECT frw_funzionalita.label, frw_funzionalita.nome
					FROM frw_funzionalita
					JOIN frw_componenti ON frw_funzionalita.idcomponente = frw_componenti.id
					JOIN frw_ute_fun ON idfunzionalita = frw_funzionalita.id
					WHERE frw_componenti.nome =  'GESTIONEUTENTI' AND frw_ute_fun.idutente =  '".$session->get("idutente")."';";

			$rs->$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));
			$session->register("GESTIONEUTENTI_READ","false");
			$session->register("GESTIONEUTENTI_WRITE","false");

			while($row = $rs->fetch_array()){
				if ($row['nome']=='READ') {
					$session->register("GESTIONEUTENTI_READ","true");
				}
				if ($row['nome']=='WRITE') {
					$session->register("GESTIONEUTENTI_WRITE","true");
				}
			}
			$rs->free();
		}


	}



	/*
		mostra il dettaglio.
	*/
	function getDettaglio($id="") {
		global $session,$root;

		$u = new user($id,$this->MAX_USER_LEVEL);
		$dati = $u->getUserData();

		/*
			attualmente ce una politica tale per cui un utente puo' editare/inserire (se ha la funz/permesso)
			altri utenti con livello minore del suo e puo' editare se stesso.

			questa politica in realtà per certe applicazioni non va bene, in quandoo magari ci sono
			tanti utenti (tipo i diversi operatori di biblioteca) che non devono poter editarsi/inserire
			perche' solo l'amministratore puo' inserire

		*/

		$condizione = $u->profiloEditabile($dati['cd_profilo']);

		if (($session->get("GESTIONEUTENTI_WRITE")=="true") &&
			($condizione) ) {
			$dati = $this->getDatiTipo($id);
			if ($dati['cd_user']!=0) {
				/*
					modifica
				*/
				$action = "modificaStep2";
			} else {
				/*
					inserimento
				*/
				$dati = array("cd_user"=>"{$id}","de_email"=>"","dt_datacreazione"=>date("Y-m-d"));
				$action = "aggiungiStep2";
			}
			$html = loadTemplateAndParse("template/dettaglio.html");

			//costruzione form
			$objform = new form();
			//$objform->pathJsLib = $root."template/controlloform.js";

			$de_email = new testo("de_email",htmlspecialchars($dati["de_email"]),200,30);
			$de_email->obbligatorio=1;
			$de_email->label="'Indirizzo e-mail'";
			$objform->addControllo($de_email);

			$dt_datacreazione = new data("dt_datacreazione", TOdmy($dati["dt_datacreazione"]), "gg-mm-aaaa", "dati");
			$objform->addControllo($dt_datacreazione);

			$id_user = new hidden("id",$dati["cd_user"]);
			$op = new hidden("op","modificaStep2");

			$submit = new submit("invia","salva");

			$html = str_replace("##STARTFORM##", $objform->startform(), $html);
			$html = str_replace("##id##", $id_user->gettag(), $html);
			$html = str_replace("##op##", $op->gettag(), $html);
			$html = str_replace("##submit##", $submit->gettagimage($root."src/images/salva.gif"," Salva"), $html);
			$html = str_replace("##de_email##", $de_email->gettag(), $html);
			$html = str_replace("#nome#", execute_scalar("select CONCAT(nome,' ',cognome) from frw_utenti where id='".$id."'"), $html);
			$html = str_replace("##dt_datacreazione##", $dt_datacreazione->gettag(), $html);
			$html = str_replace("##gestore##", $this->gestore, $html);
			$html = str_replace("##ENDFORM##", $objform->endform(), $html);

		} else {
			$html = returnmsg("Non sei autorizzato.");
		}
		return $html;
	}

	function getDatiTipo($id) {
		$sql = "SELECT * from frw_extrauserdata where cd_user='{$id}'";
		$dati = execute_row($sql);
		if(!isset($dati['cd_user'])) $dati['cd_user'] = 0;
		return $dati;
	}


	function updateAndInsert($arDati) {
		// in:
		// arDati--> array POST del form
		// risultato:
		//	"" --> ok
		//	"1" --> nome gia' utilizzato da un altro componente
		//  "0" --> il tuo profilo non ti consente l'inserimento/modifica

		global $session,$conn;
		if ($session->get("GESTIONEUTENTI_WRITE")=="true") {

			$sql="delete from frw_extrauserdata where cd_user={$arDati['id']}";
			$conn->query($sql) or die($conn->error."sql='$sql'<br>");

			$sql="INSERT into frw_extrauserdata (de_email,cd_user,dt_datacreazione) values('##de_email##',##id_user##,'##dt_datacreazione##')";
			$sql= str_replace("##de_email##",$arDati["de_email"],$sql);
			$sql= str_replace("##dt_datacreazione##",TOymd($arDati["dt_datacreazione"]),$sql);
			$sql= str_replace("##id_user##",$arDati["id"],$sql);

			$conn->query($sql) or die($conn->error."sql='$sql'<br>");
			$html= "";

		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}

}

?>
