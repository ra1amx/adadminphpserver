<?php

class Profili {

	var $tbdb;	//tabella del database che contiene gli utenti

	var $start;	// posizione del primo record visualizzato
	var $omode;	// asc|desc
	var $oby;	// campo della tabella $tbdb utilizzato per ordinare 
	var $ps;	// numero di righe per pagina nell'elenco

	var $linkaggiungi;	//link utilizzato per "aggiungere"
	var $linkaggiungi_label;

	var $linkmodifica;	//link utilizzato per il comando "modifica"
	var $linkmodifica_label;

	var $linkelimina;	//link utilizzato per il comando "elimina"
	var $linkelimina_label;

	var $gestore;


	function __construct ($tbdb="frw_profili",$ps=20,$oby="de_label",$omode="asc",$start=0) {
		global $session,$root,$conn;
		$this->gestore = $_SERVER["PHP_SELF"];
		$this->tbdb = $tbdb;
		//se ci sono impostazioni inviate in get o in post usa quelle
		//se non ci sono quelle usa quelle in session
		//se non ci sono neanche in session usa i valori passati.
		$this->start = setVariabile("gridStart",$start,$this->tbdb);
		$this->omode= setVariabile("gridOrderMode",$omode,$this->tbdb);
		$this->oby= setVariabile("gridOrderBy",$oby,$this->tbdb);
		$this->ps = setVariabile("gridPageSize",$ps,$this->tbdb);

		$this->linkaggiungi = "$this->gestore?op=aggiungi";
		$this->linkaggiungi_label = "Aggiungi un nuovo profilo";

		$this->linkmodifica = "$this->gestore?op=modifica&id=##id_profilo##";
		$this->linkmodifica_label = "modifica";

		$this->linkelimina = "javascript:confermaDelete('##id_profilo##');";
		$this->linkelimina_label = "elimina";

		if ($session->get("FRWPROFILI") == "") {
			/*
				controllo che l'utente specificato abbia la funzionalita' attivata.
				se non c'e' nella sessione cerco questa funzionalita' nel db.
			*/

			$sql = "SELECT  frw_funzionalita.label, frw_funzionalita.nome 
					FROM  frw_funzionalita 
					JOIN frw_componenti ON frw_funzionalita.idcomponente = frw_componenti.id
					JOIN frw_ute_fun ON idfunzionalita = frw_funzionalita.id
					WHERE frw_componenti.nome =  'FRWPROFILI' AND frw_ute_fun.idutente =  '".$session->get("idutente")."';";

			$rs=$conn->query($sql) or die($conn->error."sql='$sql'<br>");
			$session->register("FRWPROFILI","false");

			while($row = $rs->fetch_array()){
				if ($row['nome']=='FRWPROFILI') {
					$session->register("FRWPROFILI","true");
				}
			}
			$rs->free();
		}


	}

	/*
		mostra l'elenco dei componenti.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'elenco in html.
	*/
	function elenco() {
		global $session;
		$html = "";
		if ($session->get("FRWPROFILI")=="true") {
			$t=new grid($this->tbdb,$this->start, $this->ps, $this->oby, $this->omode);

			//campi da visualizzare
			$t->campi="id_profilo,de_label,de_descrizione";

			//titoli dei campi da visualizzare
			$t->titoli="ID,Label,Descrizione";

			//id per fare i link
			$t->chiave="id_profilo";

			//query per estrarre i dati
			$t->query="SELECT id_profilo, de_label,de_descrizione from frw_profili";

			$t->addComando($this->linkmodifica,$this->linkmodifica_label);
			$t->addComando($this->linkelimina,$this->linkelimina_label);

			$html =""; // <a href=\"$this->linkaggiungi\" class='aggiungi'>$this->linkaggiungi_label</a><br><br>";

			$html .= $t->show();

		} else {
			$html = "0";
		}
		return $html;
	}

	/*
		estrae una stringa con i record sepatati da una virgola.
		prende il primo item dei record estratti.
	*/
	function getElencoId($sql) {
		return concatenaId($sql);
	}


	function getListaCheckboxChiEdita($strValoriSelezionati="") {
		global $conn;
		$sql = "select id_profilo as idprofili,de_label from frw_profili";
		$rs = $conn->query($sql) or die($conn->error."sql='$sql'<br>");
		$html="";
		while ($r=$rs->fetch_array()) {
			//echo $r["idprofili"]." in ".$strValoriSelezionati."<br>";
			$html.="<input type=\"checkbox\" name=\"idprofili[]\" value=\"{$r['idprofili']}\"";
			if (stristr($strValoriSelezionati, ",".$r["idprofili"].",")) $html.=" checked";
			$html.=">{$r['de_label']}";
			$html.="\r\n";
		}
		return $html;
	}


	/*
		mostra il dettaglio del componente.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'html.
	*/
	function getDettaglio($id="") {
		global $session;

		if ($session->get("FRWPROFILI")=="true") {
			if ($id!="") {
				/*
					modifica
				*/
				$html = loadTemplateAndParse("template/dettaglio.html");
				$dati = $this->getDatiProfilo($id);

				$html = str_replace("##id##", $dati["id_profilo"], $html);
				$html = str_replace("##idprofilo##", $dati["id_profilo"], $html);
				$html = str_replace("##action##", "modificaStep2", $html);
				$html = str_replace("##descrizione##", htmlspecialchars($dati["de_descrizione"]), $html);
				$html = str_replace("##label##", htmlspecialchars($dati["de_label"]), $html);
				$html = str_replace("##gestore##", $this->gestore, $html);
				$profilichecckati = $dati["chiedita"];
				$chiedita = $this->getListaCheckboxChiEdita($profilichecckati);

				$html = str_replace("##elencoprofili##", nl2br($chiedita), $html);

			} else {
				/*
					Inserimento
				*/
				$html = loadTemplateAndParse("template/dettaglio.html");
				$html = str_replace("##id##", "", $html);
				$html = str_replace("##idprofilo##", "", $html);
				$html = str_replace("##action##", "aggiungiStep2", $html);
				$html = str_replace("##descrizione##", "", $html);
				$html = str_replace("##label##", "", $html);
				$html = str_replace("##gestore##", $this->gestore, $html);
				$chiedita = $this->getListaCheckboxChiEdita("");
				$html = str_replace("##elencoprofili##", nl2br($chiedita), $html);

			}

		} else {
			$html = returnmsg("Non sei autorizzato.");
		}
		return $html;
	}

	function getDatiProfilo($id) {
		$sql = "SELECT * from frw_profili where id_profilo='{$id}'";
		return execute_row($sql);
	}


	function checkExistIn($nometabella,$nomecampo,$valore,$giaesistente,$campoidunivoco) {
		global $conn;
		$sql = "select $nomecampo from $nometabella where $nomecampo='$valore'";
		if ($giaesistente!="") $sql.=" and $campoidunivoco<>'$giaesistente'";
		//echo $sql."<br>";
		$rs = $conn->query($sql) or die($conn->error."sql='$sql'<br>");
		if ($rs->num_rows>0) $risultato = true; else $risultato = false;
		$rs->free();
		return $risultato;
	}


	function updateAndInsert($arDati) {
		// in:
		// arDati--> array POST del form
		// risultato:
		//	"" --> ok
		//	"1" --> label gia' utilizzato da un altro profilo
		//	"3" --> id_profilo gia' utilizzato da un altro profilo
		//  "0" --> il tuo profilo non ti consente l'inserimento/modifica

		global $session,$conn;
		if ($session->get("FRWPROFILI")=="true") {
			if ($arDati["id"]!="") {
				/*
					Modifica
				*/
				if (!$this->checkExistIn("frw_profili","de_label",$arDati["label"],$arDati["id"],"id_profilo")){ 
					if (!$this->checkExistIn("frw_profili","id_profilo",$arDati["idprofilo"],$arDati["id"],"id_profilo")){ 
						$sql="UPDATE frw_profili set chiedita='##chiedita##',de_descrizione='##descrizione##',de_label='##label##' where id_profilo='##id##'";
						$sql= str_replace("##descrizione##",$arDati["descrizione"],$sql);
						$sql= str_replace("##label##",$arDati["label"],$sql);
						$sql= str_replace("##id##",$arDati["id"],$sql);
						if (!isset($arDati["idprofili"])) $arDati["idprofili"] = array();
						$chiedita=",";
						for ($i=0;$i<count($arDati["idprofili"]);$i++) {
							$chiedita.=$arDati["idprofili"][$i].",";
						}
						if ($chiedita==",") $chiedita="";
						$sql= str_replace("##chiedita##",$chiedita,$sql);

						$conn->query($sql) or die($conn->error."sql='$sql'<br>");
						

						$html= "";

					} else {
						$html="3";	//id_profilo gia' utilizzato
					}

				} else {
					$html="1";	//label già utilizzato
				}
			} else {
				/*
					Inserimento
				*/
				if (!$this->checkExistIn("frw_profili","de_label",$arDati["label"],$arDati["id"],"id_profilo")){ 
					if (!$this->checkExistIn("frw_profili","id_profilo",$arDati["idprofilo"],$arDati["id"],"id_profilo")){ 
						$sql="INSERT into frw_profili (id_profilo,de_descrizione,de_label,chiedita) values('##idprofilo##','##descrizione##','##label##','##chiedita##')";

						$sql= str_replace("##descrizione##",$arDati["descrizione"],$sql);
						$sql= str_replace("##idprofilo##",$arDati["idprofilo"],$sql);
						$sql= str_replace("##label##",$arDati["label"],$sql);

						$chiedita=",";
						if (!isset($arDati["idprofili"])) $arDati["idprofili"]=array();
						for ($i=0;$i<count($arDati["idprofili"]);$i++) {
							$chiedita.=$arDati["idprofili"][$i].",";
						}
						if ($chiedita==",") $chiedita="";
						$sql= str_replace("##chiedita##",$chiedita,$sql);

						$conn->query($sql) or die($conn->error."sql='$sql'<br>");

						$html= "";
					} else {
						$html="3"; //id_profilo gia' utilizzato
					}
				} else {
					$html="1";	//label già utilizzato
				}

			}

		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}



	function deleteP($id) {
		// in:
		// id --> id modulo da cancellare
		// risultato:
		//	"" --> ok
		//  "0" -->il tuo profilo non ti consente la cancellazione
		//  "1" ---> ci sono degli utenti con questo profilo, non puoi cancellarlo.

		global $session,$conn;
		if ($session->get("FRWPROFILI")=="true") {
			$userCollegati = $this->getElencoId("select id from frw_utenti where cd_profilo='$id'");
			if ($userCollegati=="") {
				$sql="DELETE FROM frw_profili where id_profilo='$id'";
				//echo "$sql<br>";
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");

				$sql="DELETE from frw_profili_funzionalita where cd_profilo='$id'";
				//echo "$sql<br>";
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");

				$html = "";

			} else {
				$html = "1";
			}

		} else {
			$html="0";		//il tuo profilo non ti consente di cancellare
		}
		return $html;

	}
}

?>