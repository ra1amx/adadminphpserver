<?php
/*
	classe per la gestione degli asili.
*/

class Commod {

	var $tbdb;	//tabella del database che contiene i dati

	var $start;	// posizione del primo record visualizzato
	var $omode;	// asc|desc
	var $oby;	// campo della tabella $tbdb utilizzato per ordinare
	var $ps;	// numero di righe per pagina nell'elenco

	var $gestore;

	function __construct ($tbdb="frw_com_mod",$ps=20,$oby="posizione",$omode="asc",$start=0) {
		global $session,$root;
		$this->gestore = $_SERVER["PHP_SELF"];
		$this->tbdb = $tbdb;
		//se ci sono impostazioni inviate in get o in post usa quelle
		//se non ci sono quelle usa quelle in session
		//se non ci sono neanche in session usa i valori passati.
		$this->start = setVariabile("gridStart",$start,$this->tbdb);
		$this->omode= setVariabile("gridOrderMode",$omode,$this->tbdb);
		$this->oby= setVariabile("gridOrderBy",$oby,$this->tbdb);
		$this->ps = setVariabile("gridPageSize",$ps,$this->tbdb);
		checkAbilitazione("FRWMODULI","SETTA_SOLO_SE_ESISTE");
	}




	/*
		mostra il dettaglio.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'html.
	*/
	function getDettaglio($id="",$cd="") {
		global $session,$root,$conn;

		if ($session->get("FRWMODULI")) {
			if ($id!="") {
				/*
					modifica
				*/
				$dati = $this->getDati($id,$cd);
				$action = "modificaStep2";
			} else {
				/*
					inserimento
				*/
				$dati = getEmptyNomiCelleAr("frw_com_mod") ;
				$dati['idmodulo']=$cd;
				$action = "aggiungiStep2";
			}

			$html = loadTemplateAndParse("template/dettagliocomponenti.html");

			//costruzione form
			$objform = new form();
			//$objform->pathJsLib = $root."template/controlloform.js";

			$arpos = array(); for($i=0;$i<99;$i++) $arpos[$i]=$i;
			$posizione = new optionlist("posizione",$dati["posizione"],$arpos );
			$posizione->obbligatorio=0;
			$posizione->label="'Posizione'";
			$objform->addControllo($posizione);

			$cd_item = new hidden("cd_item",$dati["idmodulo"]);
			if ($id!="") {
				/*
					modifica
				*/
				$idcomponente = new hidden("id",$dati["idcomponente"]);
				$nome = execute_scalar("select concat(label,' - ',nome) as nome from frw_componenti where id =".$id."");
			
			} else {
				$nome = "";
				/*
					inserimento
				*/
				//------------------------------------------------
				//combo componenti
				$sql = "SELECT id, label, nome
					FROM frw_componenti
					WHERE id not in (SELECT idcomponente FROM frw_com_mod WHERE idmodulo = ".$dati["idmodulo"].")
					ORDER BY nome";
				$rs = $conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$arComp[""]="--scegli--";
				while($riga = $rs->fetch_array()) {
					$arComp[$riga['id']]=$riga['nome'].' - '.$riga['label'];
				}
				//------------------------------------------------
				$idcomponente = new optionlist("id",'',$arComp);
				$idcomponente->obbligatorio=1;
				$idcomponente->label="'Template'";
				$objform->addControllo($idcomponente);
			}
			$op = new hidden("op",$action);

			$submit = new submit("invia","salva");

			$html = str_replace("##STARTFORM##", $objform->startform(), $html);
			$html = str_replace("##id##", $idcomponente->gettag(), $html);
			$html = str_replace("##cd_item##", $cd_item->gettag(), $html);
			$html = str_replace("##idopener##", $cd, $html);
			$html = str_replace("##ANNULLALINK##", "./index.php?op=modifica&id=".$cd, $html);
			$html = str_replace("##op##", $op->gettag(), $html);
			//$html = str_replace("##submit##", $submit->gettagimage($root."src/images/salva.gif"," Salva"), $html);
			$html = str_replace("##posizione##", $posizione->gettag(), $html);
			$html = str_replace("##gestore##", $this->gestore, $html);
			$html = str_replace("##nomecomponente##", $nome, $html);
			$html = str_replace("##ENDFORM##", $objform->endform(), $html);
		} else {
			$html = "0";
		}
		return $html;
	}


	function getDati($idc,$idm) {
		return execute_row("SELECT * from frw_com_mod where idcomponente='{$idc}' and idmodulo='$idm'");
	}


	function updateAndInsert($arDati,$files) {
		// in:
		// arDati--> array _POST del form
		// files --> array _FILES
		// risultato:
		//	"" --> ok
		//  "0" --> il tuo profilo non ti consente l'inserimento/modifica
		//  "2|messaggio" --> errore file

		global $session,$conn;
		if ($session->get("FRWMODULI")) {
	
			if (execute_scalar("select count(*) from frw_com_mod where idcomponente='".$arDati["id"]."' and idmodulo='".$arDati["cd_item"]."'")>0) {
				$id = $arDati["id"];
				/*
					Modifica
				*/

				$sql="UPDATE frw_com_mod set
					posizione='##posizione##'
					where idcomponente='##idcomponente##' and idmodulo='##idmodulo##'";
				$sql= str_replace("##posizione##",$arDati["posizione"],$sql);
				$sql= str_replace("##idmodulo##",$arDati["cd_item"],$sql);
				$sql= str_replace("##idcomponente##",$arDati["id"],$sql);
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$html= "ok|".$id;
			} else {
				/*
					Inserimento
				*/

				$sql="INSERT into frw_com_mod (posizione,idcomponente,idmodulo) values('##posizione##','##idcomponente##','##idmodulo##')";
				$sql= str_replace("##idmodulo##",$arDati["cd_item"],$sql);
				$sql= str_replace("##idcomponente##",$arDati["id"],$sql);
				$sql= str_replace("##posizione##",$arDati["posizione"],$sql);
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$id = $conn->insert_id;
				$html= "ok|".$id;

			}



		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}




	function deleteItem($idc,$idm) {
		// in:
		// id --> id tipo da cancellare
		// risultato:
		//	"" --> ok
		//  "0" -->il tuo profilo non ti consente la cancellazione

		global $session,$conn;
		if ($session->get("FRWMODULI")) {
			$sql="DELETE FROM frw_com_mod where idcomponente='$idc' and idmodulo='$idm'";
			$conn->query($sql) or die($conn->error."sql='$sql'<br>");
			$html = $idm;	//restituisce l'id dell'item di ritorno
		} else {
			$html="0";		//il tuo profilo non ti consente di cancellare
		}
		return $html;

	}

}

?>