<?php
/*
	classe per la gestione degli asili.
*/

class Posizioni {

	var $tbdb;	//tabella del database che contiene i dati

	var $uploadDir; //contiene la cartella in cui uploadare i file.
					//percorso dalla root.

	var $start;	// posizione del primo record visualizzato
	var $omode;	// asc|desc
	var $oby;	// campo della tabella $tbdb utilizzato per ordinare
	var $ps;	// numero di righe per pagina nell'elenco

	var $linkaggiungi;	//link utilizzato per "aggiungere"
	var $linkaggiungi_label;
	var $linkaggiungi_icon;

	var $linkmodifica;	//link utilizzato per il comando "modifica"
	var $linkmodifica_label;

	var $linkduplica;	//link utilizzato per il comando "modifica"
	var $linkduplica_label;

	var $linksendmail;	//link utilizzato per il comando "view"
	var $linksendmail_label;

	var $linkelimina;	//link utilizzato per il comando "elimina"
	var $linkelimina_label;

	var $gestore;


	function __construct ($tbdb="7banner_posizioni",$ps=20,$oby="id_posizione",$omode="desc",$start=0) {
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

		$this->linkaggiungi = "$this->gestore?op=aggiungi";
		$this->linkaggiungi_label = "Add new";

		$this->linkmodifica = "$this->gestore?op=modifica&id=##id_posizione##";
		$this->linkmodifica_label = "modifica";

		//$this->linkduplica = "$this->gestore?op=duplica&id=##id_posizione##";
		//$this->linkduplica_label = "<img src=\"../../icone/page_paste.png\" align=\"absmiddle\" alt=\"modifica\" border=\"0\">";

		$this->linkelimina = "javascript:confermaDelete('##id_posizione##');";
		$this->linkelimina_label = "elimina";

		$this->linkeliminamarcate = "javascript:confermaDeleteCheck(document.datagrid);";
		$this->linkeliminamarcate_label = "Delete selected";

		checkAbilitazione("POSIZIONI","SETTA_SOLO_SE_ESISTE");

	}

	/*
		mostra l'elenco dei componenti.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'elenco in html.
	*/
	function elenco($combotipo="",$combotiporeset="",$keyword="") {
		global $session;

		$html = "";

		if ($session->get("POSIZIONI")) {
			if($combotiporeset=='reset') {
				//se ho cambiato con la combo del filtro
				//allora resetto la paginazione.
				$this->start = 0;
			}

			$t=new grid($this->tbdb,$this->start, $this->ps, $this->oby, $this->omode);
			$t->checkboxFormAction=$this->gestore;
			$t->checkboxFormName="datagrid";
			$t->checkboxForm=true;
			$t->functionhtml = "";
			$t->mostraRecordTotali = true;

			$t->parametriDaPssare = "";
			if($combotipo) {
				$t->parametriDaPssare.="&combotipo=".urlencode($combotipo);
			}
			if($keyword) $t->parametriDaPssare.="&keyword=".urlencode($keyword);

			$t->campi="id_posizione,de_posizione,v,c";
			//titoli dei campi da visualizzare
			$t->titoli="ID,Name,Daily views,Daily clicks";


			//id per fare i link
			$t->chiave="id_posizione";

			//query per estrarre i dati
			//$t->debug = true;

			$t->query="SELECT id_posizione,de_posizione,ROUND( sum(7banner_stats.nu_pageviews) / 7) as v, ROUnD(sum(7banner_stats.nu_click)/7) as c
			FROM 7banner_posizioni  left outer join 7banner on cd_posizione=id_posizione
				left outer join  `7banner_stats` on `7banner_stats`.cd_banner=id_banner and 7banner_stats.id_day >= '".date("Y-m-d",strtotime("-7 days"))."'
				#where#
			group by id_posizione" ;

			//$t->query="SELECT A.id_posizione,A.de_posizione from ".$this->tbdb." as A
				//";

			$where = "1 = 1";
			if($combotipo==="0" || $combotipo) {
				if($combotipo=="-999") {

				} else {
					//if($where!="") { $where.= " and "; }
					//$where.=" A.is_active='".$combotipo."'";
				}
			}
			if($keyword) {
				if($where!="") { $where.= " and "; }
				$where.="  (A.de_posizione like '%{$keyword}%')";
			}
			$t->query = str_replace("#where#", " where {$where}",$t->query);
			

			$t->addComando($this->linkmodifica,$this->linkmodifica_label,"Edit");
			$t->addComando($this->linkelimina,$this->linkelimina_label,"Delete");
			//$t->addCampiDate("dt_saved",'mm/dd/yyyy hh:ii');
			$t->arFormattazioneTD=array(
				"v" => "numero",
				"c" => "numero",

			);
			/*$t->addScegliDaInsieme("is_active",array(
				"1"=>"<div style='width:26px;margin:0 auto;display:block;padding:3px;background-color:#66CC33;color:#fff;text-align:center;font-weight:bold;'>ON</div>",
				"0"=>"<div style='width:26px;margin:0 auto;display:block;padding:3px;background-color:#FF0000;color:#fff;text-align:center;font-weight:bold;'>OFF</div>"
			));*/

			$texto = $t->show();

			if (trim($texto)=="") $texto="No records.";

			$html .= $texto."<br/>";

		} else {

			$html = "0";
		}
		return $html;
	}


	/*
		mostra il dettaglio.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'html.
	*/
	function getDettaglio($id="",$duplica='no') {
		global $session,$root;

		if ($session->get("POSIZIONI")) {
			if ($id!="") {
				/*
					modifica
				*/
				$dati = $this->getDati($id);
				//$dati["id_author"] = execute_scalar("select id_author from ".DB_TABLE_PREFIX."rel_contents_authors where id_content='".$id."'");

				$action = "modificaStep2";



			} else {
				/*
					inserimento
				*/
				$dati = getEmptyNomiCelleAr($this->tbdb) ;
				//$dati["id_author"] = "";

				$action = "aggiungiStep2";

			}

			$html = loadTemplateAndParse("template/dettaglio.html");

			//costruzione form
			$objform = new form();
			//$objform->pathJsLib = $root."template/controlloform.js";

			//$valore = $dati["dt_saved"];
			//if ($valore=="") $valore = date("Y-m-d H:i:s");
			//$dt_saved = new dataora("dt_saved",$valore,"aaaa-mm-gg",$objform->name);
			//$dt_saved->obbligatorio=1;
			//$dt_saved->label="'Data'";
			//$objform->addControllo($dt_saved);

			$de_posizione = new testo("de_posizione",$dati["de_posizione"],50,50);
			$de_posizione->obbligatorio=1;
			$de_posizione->label="'Name'";
			$objform->addControllo($de_posizione);

			$nu_width = new intero("nu_width",$dati["nu_width"],5,5);
			$nu_width->obbligatorio=1;
			$nu_width->label="'Width'";
			$objform->addControllo($nu_width);

			$nu_height = new intero("nu_height",$dati["nu_height"],5,5);
			$nu_height->obbligatorio=1;
			$nu_height->label="'Height'";
			$objform->addControllo($nu_height);


			$id_obj = new hidden("id",$dati["id_posizione"]);
			$op = new hidden("op",$action);

			//$q = $id ? execute_scalar("select count(*) from qoq_tbc_tag_scontri WHERE cd_tag='".$id."'") : "n.d.";

			$html = str_replace("##STARTFORM##", $objform->startform(), $html);
			//$html = str_replace("##q##", $q, $html);
			$html = str_replace("##id##", $id_obj->gettag(), $html);
			//print_r($_SERVER);

			$html = str_replace("##WWW##", WEBURL, $html);
			$html = str_replace("##LABEL##", $dati["de_posizione"], $html);
			$html = str_replace("##IDPOS##", $id ? $id : "(prima salva)", $html);
			$html = str_replace("##op##", $op->gettag(), $html);
			$html = str_replace("##nu_height##", $nu_height->gettag(), $html);
			$html = str_replace("##nu_width##", $nu_width->gettag(), $html);
			//$html = str_replace("##dt_saved##", $dt_saved->gettag(), $html);
			$html = str_replace("##de_posizione##", $de_posizione->gettag(), $html);
			$html = str_replace("##gestore##", $this->gestore, $html);
			$html = str_replace("##ENDFORM##", $objform->endform(), $html);


		} else {
			$html = "0";
		}
		return $html;
	}

	function getDati($id) {
		$sql = "SELECT * from ".$this->tbdb." where id_posizione='{$id}'";
		return execute_row($sql);
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
		if ($session->get("POSIZIONI")) {
			//$arDati['url'] = ricavaNomePuro($arDati['url']);
			/*
				prima di salvare verifico che non ci sia 
				un altro utente con lo stesso username o con la stessa email.
			*/


			if ($arDati["id"]!="") {
				$id = $arDati["id"];
				/*
					Modifica
				*/

				$sql="UPDATE ".$this->tbdb." set
					de_posizione='##de_posizione##',nu_width='##nu_width##',nu_height='##nu_height##'
					where id_posizione='##id##'";
				//$sql= str_replace("##dt_saved##",$arDati["dt_saved"],$sql);
				$sql= str_replace("##de_posizione##",$arDati["de_posizione"],$sql);
				$sql= str_replace("##id##",$arDati["id"],$sql);
				$sql= str_replace("##nu_width##",$arDati["nu_width"],$sql);
				$sql= str_replace("##nu_height##",$arDati["nu_height"],$sql);
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$html= "ok|".$id;
			} else {
				/*
					Inserimento
				*/
				$sql="INSERT into ".$this->tbdb." (de_posizione,nu_width,nu_height) values('##de_posizione##','##nu_width##','##nu_height##')";
				//$sql= str_replace("##dt_saved##",$arDati["dt_saved"],$sql);
				$sql= str_replace("##de_posizione##",$arDati["de_posizione"],$sql);
				$sql= str_replace("##nu_width##",$arDati["nu_width"],$sql);
				$sql= str_replace("##nu_height##",$arDati["nu_height"],$sql);
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$id = $conn->insert_id;
				$html= "ok|".$id;

			}



		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}


	function getHtmlcombotipo($def="") {
		//------------------------------------------------
		//combo filtro
		//$sql = "select YEAR(dt_saved) as A,count(*) as c from ".$this->tbdb." group by YEAR(dt_saved)";
		//$rs = mysql_query($sql) or trigger_error(mysql_error());
		$arFiltri = array("-999"=>"All");
		//while($riga = mysql_fetch_array($rs)) {
			//if ($riga['A']=="") $riga['c']=0;
			//$arFiltri[$riga['A']]= $riga['A']." (".$riga['c'].")";
		//}
		//------------------------------------------------
		$out = "";
		foreach ($arFiltri as $k => $v) { $out.="<option value='{$k}' ".(($k."x"==$def."x")?"selected":"").">{$v}</option>"; }
		return "<select onchange='aggiornaGriglia()' name='combotipo' id='combotipo'>{$out}</select><input type='hidden' name='combotiporeset' id='combotiporeset'>";

	}


	function deleteItem($id) {
		// in:
		// id --> id tipo da cancellare
		// risultato:
		//	"" --> ok
		//  "0" -->il tuo profilo non ti consente la cancellazione

		global $session,$conn;
		if ($session->get("POSIZIONI")) {

			$sql="DELETE FROM ".$this->tbdb." where id_posizione='$id'";
			$conn->query($sql) or die($conn->error."sql='$sql'<br>");
			if($conn->affected_rows>0) {

				//$sql="DELETE FROM qoq_tbc_tag_scontri where cd_tag='$id'";
				//mysql_query($sql) or trigger_error(mysql_error()."sql='$sql'<br>");

			}

			$html = "";
		} else {
			$html="0";		//il tuo profilo non ti consente di cancellare
		}
		return $html;

	}
	function eliminaSelezionati($dati) {
		// in:
		// dati --> $_POST
		// risultato:
		//	"" --> ok
		//  "0" -->il tuo profilo non ti consente la cancellazione

		global $session;
		if ($session->get("POSIZIONI")) {

			$html="0";
			//$idx ="";

			$p=$dati['gridcheck'];
			for ($i=0;$i<count($p);$i++) $this->deleteItem($p[$i]);
			$html = "";
		} else {
			$html="0";		//il tuo profilo non ti consente di cancellare
		}
		return $html;
	}


}

?>