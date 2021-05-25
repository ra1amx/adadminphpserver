<?php

include("_include/user.class.php");

class GestioneUtenti
{

	var $tbdb;	//tabella del database che contiene gli utenti

	var $start;	// posizione del primo record visualizzato
	var $omode;	// asc|desc
	var $oby;	// campo della tabella $tbdb utilizzato per ordinare
	var $ps;	// numero di righe per pagina nell'elenco

	var $linkaggiungi;	//link utilizzato per "aggiungere" un utente
	var $linkmodifica;	//link utilizzato per il comando "modifica"
	var $linkelimina;	//link utilizzato per il comando "elimina"

	var $linkaggiungi_label;
	var $linkaggiungi_icon;
	var $linkmodifica_label;
	var $linkelimina_label;
	var $MAX_USER_LEVEL;
	var $extradatalink;
	var $extradatalink_label;

	var $gestore;

	var $selectedLetter;


	function __construct ($tbdb="frw_utenti",$ps=40,$oby="cognome",$omode="asc",$start=0,$selectedLetter="") {
		global $session,$root,$conn;
		$this->MAX_USER_LEVEL=999999;	//definizione utente superadmin
		$this->gestore = $_SERVER["PHP_SELF"];
		$this->tbdb = $tbdb;
		//se ci sono impostazioni inviate in get o in post usa quelle
		//se non ci sono quelle usa quelle in session
		//se non ci sono neanche in session usa i valori passati.
		$this->start = setVariabile("gridStart",$start,$this->tbdb);
		$this->omode= setVariabile("gridOrderMode",$omode,$this->tbdb);
		$this->oby= setVariabile("gridOrderBy",$oby,$this->tbdb);
		$this->ps = setVariabile("gridPageSize",$ps,$this->tbdb);
		$this->scegliDaInsiemeLabelProfili = array();

		$this->selectedLetter=setVariabile("gridSelectedLetter",$selectedLetter,$this->selectedLetter);

		$this->linkaggiungi = "$this->gestore?op=aggiungi";
		$this->linkaggiungi_label = "Add a new user";

		$this->linkmodifica = "$this->gestore?op=modifica&id=##id##";
		$this->linkmodifica_label = "modifica";

		$this->linkelimina = "javascript:confermaDelete('##id##');";
		$this->linkelimina_label = "elimina";

		$this->extradatalink = $root."src/componenti/".EXTRA_USER_LINK."?op=modifica&id=##id##";
		$this->extradatalink_label = "parametri";

		$this->personifica = "$this->gestore?op=personifica&id=##id##";
		$this->personifica_label = "personifica";

		if ($session->get("GESTIONEUTENTI_READ") == "") {
			/*
				se non c'è GESTIONEUTENTI_READ allora è il primo caricamento della
				classe e devo recuperare i dati dal db e metterli in sessione
				perche' gli accessi successivi utilizzeranno la sessione
			*/

			$sql = "SELECT frw_funzionalita.label, frw_funzionalita.nome
					FROM frw_funzionalita
					JOIN frw_componenti ON frw_funzionalita.idcomponente = frw_componenti.id
					JOIN frw_ute_fun ON idfunzionalita = frw_funzionalita.id
					WHERE frw_componenti.nome =  'GESTIONEUTENTI' AND frw_ute_fun.idutente =  '".$session->get("idutente")."';";

			$rs=$conn->query($sql) or die($conn->error."sql='$sql'<br>");
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

	function elencoUtenti() {
		global $session;

		$t=new grid($this->tbdb,$this->start, $this->ps, $this->oby, $this->omode,0,"GESTIONEUTENTI_elenco",$this->selectedLetter);


		if($session->get("idprofilo")=="999999") {
			//campi da visualizzare
			$t->campi="cognome,nome,username,password,de_label,fl_attivo";
			//titoli dei campi da visualizzare
			$t->titoli="Surname,Name,Username,Password,Profile,Status";
		} else {
			//campi da visualizzare
			$t->campi="cognome,nome,username,de_label,fl_attivo";
			//titoli dei campi da visualizzare
			$t->titoli="Cognome,Nome,Username,Profile,Status";
		}


		//id per fare i link
		$t->chiave="id";

		//query per estrarre i dati
		$t->query="SELECT frw_utenti.cognome,frw_utenti.nome,frw_utenti.id,frw_utenti.username,frw_profili.de_label,frw_utenti.fl_attivo,password from frw_utenti join frw_profili on frw_utenti.cd_profilo=frw_profili.id_profilo where cd_profilo<='".$session->get("idprofilo")."'";

		$t->ABCDmenu="true";
		$t->letterSelectField="cognome";

		//echo $t->query;
		//comandi per ogni riga
		if ($session->get("GESTIONEUTENTI_WRITE")=="true") {
			$t->addComando($this->linkmodifica,$this->linkmodifica_label,"Edit");
			if (EXTRA_USER_LINK!="") {
				/*
					se è configurato il sistema per aggiungere
					altri dati all'utente, lo fa.
				*/
				$t->addComando($this->extradatalink,$this->extradatalink_label,"Edit more data");
			}
			if (DELETE_USER_LINK==true) {
				if( in_array( $session->get("idprofilo"), array(20,999999) )) {
					// cancella solo se il profilo è amministratore
					// o superadmin.
					$t->addComando($this->linkelimina,$this->linkelimina_label,"Delete user");
				}
			}

			if( in_array( $session->get("idprofilo"), array(20,999999) )) {
				// personifica solo se il profilo è amministratore
				// o superadmin.
				$t->addComando($this->personifica,$this->personifica_label,"Login as this user");
			}

		}

		$t->addScegliDaInsieme("fl_attivo",array("0"=>"<span class='labelred'>OFF</span>","1"=>"<span class='labelgreen'>ON</span>"));

		if (count($this->scegliDaInsiemeLabelProfili)>0) $t->addScegliDaInsieme("de_label",$this->scegliDaInsiemeLabelProfili);

		$t->addCampi("password","decrypta");

		$html = $t->show();

		return $html;
	}


	function getDettaglio($id,$html) {
		// id --> id utente
		// html --> template in cui inserire i campi de
		global $session;
		$u = new user($id,$this->MAX_USER_LEVEL);
		$dati = $u->getUserData();
		//echo "(".$session->get("idprofilo")." > ".$dati["cd_profilo"].")";
		/*
			attualmente ce una politica tale per cui un utente puo' editare/inserire (se ha la funz/permesso)
			altri utenti con livello minore del suo e puo' editare se stesso.
		*/
		$condizione = $u->profiloEditabile($dati['cd_profilo']);

		if (($session->get("GESTIONEUTENTI_WRITE")=="true") &&
			($condizione)  ) {
			$cr = new cryptor();
			$html = str_replace("##id##", $dati["id"], $html);
			$html = str_replace("##nome##", ($dati["nome"]), $html);
			$html = str_replace("##cognome##", ($dati["cognome"]), $html);
			$html = str_replace("##username##", ($dati["username"]), $html);
			$html = str_replace("##password##", ($cr->decrypta($dati["password"])), $html);
			$html = str_replace("##cd_profilo##", $u->getProfilo($dati["cd_profilo"],$session->get("idprofilo")) , $html);
			if ($dati["fl_attivo"]==0) $flag=""; else $flag="checked";
			$html = str_replace("##fl_attivo##", $flag, $html);
			$html = str_replace("##gestore##", $this->gestore, $html);
		} else {
			$html = "0";	//codice di errore che vuol dire:
							//	0 -> il tuo profilo non ti consente di modificare questo utente
		}
		return $html;
	}

	function getInsertForm($html) {
		// html --> template html
		global $session;
		$u = new user($session->get("idutente"),$this->MAX_USER_LEVEL);
		$dati = $u->getUserData();
		$html = str_replace("##cd_profilo##", $u->getProfilo(1,$session->get("idprofilo")), $html);
		$html = str_replace("##gestore##", $this->gestore, $html);
		return $html;
	}

	function insertNewUser($arDati) {
		// in:
		// arDati--> array POST del form
		// risultato:
		//	"" --> ok
		//	"1" --> username gia' utilizzata
		//  "0" -->il tuo profilo non ti consente l'inserimento
		global $session,$conn;
		if ($session->get("GESTIONEUTENTI_WRITE")=="true") {
			$u=new user();
			$u->MAX_USER_LEVEL=$this->MAX_USER_LEVEL;

			if (!$u->existUserWithUsername($arDati["lausername"])){
				$sql="INSERT INTO frw_utenti (username,password,nome,cognome,fl_attivo,cd_profilo) values ('##username##','##password##','##nome##','##cognome##','##fl_attivo##','##cd_profilo##')";

				$cr = new cryptor();
				$sql= str_replace("##username##",$arDati["lausername"],$sql);
				$sql= str_replace("##password##",$cr->crypta($arDati["lapassword"]),$sql);
				$sql= str_replace("##nome##",$arDati["nome"],$sql);
				if (!isset($arDati["fl_attivo"])) $arDati["fl_attivo"]="0";
				$sql= str_replace("##fl_attivo##",$arDati["fl_attivo"],$sql);
				$sql= str_replace("##cd_profilo##",$arDati["cd_profilo"],$sql);
				$sql= str_replace("##cognome##",$arDati["cognome"],$sql);
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$u->id= $conn->insert_id;
				$u->setProfilo($arDati["cd_profilo"]);

				$html="";
			} else {
				$html="1";	//username già utilizzata
			}
		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}

	function updateUser($arDati) {
		// in:
		// arDati--> array POST del form
		// risultato:
		//	"" --> ok
		//	"1" --> username gia' utilizzata
		//  "0" -->il tuo profilo non ti consente l'inserimento

		global $session,$conn;
		if ($session->get("GESTIONEUTENTI_WRITE")=="true") {
			$u=new user($arDati["id"],$this->MAX_USER_LEVEL);

			if (!$u->existUserWithUsername($arDati["lausername"],$arDati["id"])){
				$sql="UPDATE frw_utenti set username='##username##',password='##password##',nome='##nome##',cognome='##cognome##',fl_attivo='##fl_attivo##',cd_profilo='##cd_profilo##' where id='##id##'";
				$cr  = new cryptor();
				$sql= str_replace("##username##",$arDati["lausername"],$sql);
				$sql= str_replace("##password##",$cr->crypta($arDati["lapassword"]),$sql);
				$sql= str_replace("##nome##",$arDati["nome"],$sql);
				if (!isset($arDati["fl_attivo"])) $arDati["fl_attivo"]="0";
				$sql= str_replace("##fl_attivo##",$arDati["fl_attivo"],$sql);
				$sql= str_replace("##cd_profilo##",$arDati["cd_profilo"],$sql);
				$sql= str_replace("##cognome##",$arDati["cognome"],$sql);
				$sql= str_replace("##id##",$arDati["id"],$sql);

				//echo $sql;
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$html="";

				$u->setProfilo($arDati["cd_profilo"]);

			} else {
				$html="1";	//username già utilizzata
			}
		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}

	function deleteUser($id) {
		// in:
		// id --> id utente da cancellare
		// risultato:
		//	"" --> ok
		//	"2" --> non puoi cancellare quelli uguali o meglio di te
		//	"1" --> non puoi cancellare te stesso
		//  "0" -->il tuo profilo non ti consente la cancellazione

		global $session,$conn;
		if ($session->get("GESTIONEUTENTI_WRITE")=="true") {
			if ($session->get("idutente")!=$id) {
				$u=new user($id,$this->MAX_USER_LEVEL);
				$dati = $u->getUserData();
				if (($session->get("idprofilo")==$this->MAX_USER_LEVEL)||($dati["cd_profilo"] < $session->get("idprofilo"))) {
					//se e' un superadmin puo' cancellare tutti tranne se stesso
					//se no un utente admin non puo' cancellare un utente di pari livello
					//o di livello superiore
					$sql="DELETE FROM frw_utenti where id='$id'";
					$conn->query($sql) or die($conn->error."sql='$sql'<br>");
					$sql="DELETE FROM frw_ute_fun where idutente='$id'";
					$conn->query($sql) or die($conn->error."sql='$sql'<br>");
					if(table_exists("frw_extrauserdata")) $conn->query("DELETE FROM frw_extrauserdata where cd_user='$id'");
					if(table_exists("7banner_clienti_tbc")) $conn->query("DELETE FROM 7banner_clienti_tbc where cd_utente='$id'");
					if(table_exists("ts_ore")) $conn->query("DELETE FROM ts_ore where cd_utente='$id'");
					$html="";
				} else {
					$html="2";		//non puoi cancellare quelli meglio di te
				}
			} else {
				$html = "1";	//non puoi cancellare te stesso
			}
		} else {
			$html="0";		//il tuo profilo non ti consente di cancellare
		}
		return $html;

	}
}

?>