<?php
/*
	classe per la gestione degli asili.
*/

class Campagne {

	var $tbdb;	//tabella del database che contiene i dati

	var $uploadDir; //contiene la cartella in cui uploadare i file.
					//percorso dalla root.

	var $start;	// posizione del primo record visualizzato
	var $omode;	// asc|desc
	var $oby;	// campo della tabella $tbdb utilizzato per ordinare
	var $ps;	// numero di righe per pagina nell'elenco

	var $linkaggiungi;	//link utilizzato per "aggiungere"
	var $linkaggiungi_label;

	var $linkmodifica;	//link utilizzato per il comando "modifica"
	var $linkmodifica_label;

	var $linkduplica;	//link utilizzato per il comando "modifica"
	var $linkduplica_label;

	var $linksendmail;	//link utilizzato per il comando "view"
	var $linksendmail_label;

	var $linkelimina;	//link utilizzato per il comando "elimina"
	var $linkelimina_label;

	var $gestore;


	function __construct ($tbdb="7banner_campagne",$ps=20,$oby="id_campagna",$omode="desc",$start=0) {
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

		$this->linkmodifica = "$this->gestore?op=modifica&id=##id_campagna##";
		$this->linkmodifica_label = "modifica";

		$this->linkelimina = "javascript:confermaDelete('##id_campagna##');";
		$this->linkelimina_label = "elimina";

		$this->linkeliminamarcate = "javascript:confermaDeleteCheck(document.datagrid);";
		$this->linkeliminamarcate_label = "Delete selected";

		checkAbilitazione("CAMPAGNE","SETTA_SOLO_SE_ESISTE");

	}

	/*
		mostra l'elenco dei componenti.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'elenco in html.
	*/
	function elenco($combotipo="",$combotiporeset="",$keyword="") {
		global $session;

		$html = "";

		if ($session->get("CAMPAGNE")) {
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

			$t->campi="id_campagna,de_titolo,de_nome,fl_status,q,v";
			//titoli dei campi da visualizzare
			$t->titoli="ID,Campaign title,Client,Status,Banners,Value";

			//id per fare i link
			$t->chiave="id_campagna";

			//query per estrarre i dati
			//$t->debug = true;
			$t->query="SELECT A.id_campagna,A.de_titolo,B.de_nome,fl_status,(SELECT count(*) from 7banner WHERE cd_campagna=id_campagna) q,
					(SELECT SUM(nu_price) from 7banner WHERE cd_campagna=id_campagna) v
					from ".$this->tbdb." as A 
					inner join 7banner_clienti as B on cd_cliente = id_cliente
				";

			$where = " 1=1 ";
			if($combotipo==="0" || $combotipo) {
				if($combotipo=="-999") {

				} else {
					//if($where!="") { $where.= " and "; }
					//$where.=" A.is_active='".$combotipo."'";
				}
			}
			if($keyword) {
				if($where!="") { $where.= " and "; }
				$where.="  ((A.de_titolo like '%{$keyword}%') OR (A.de_nome like '%{$keyword}%'))";
			}
			if($where) {
				$t->query.=" where {$where}";
			}

			$t->addScegliDaInsieme("fl_status",
				array(
					"1"=>"<span class='labelgreen'>ON</span>",
					"0"=>"<span class='labelred'>OFF</span>"
				)
			);
			$t->arFormattazioneTD=array(
				"q" => "numero",
				"v" => "numero",
			);
			$t->addCampi("v","valore_campagna");

			$t->addComando($this->linkmodifica,$this->linkmodifica_label,"Edit");
			$t->addComando("../7banner/index.php?op=stats&combobanner=-999|##id_campagna##","stats","Stats");
			$t->addComando($this->linkelimina,$this->linkelimina_label,"Delete");

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
		global $session,$conn;

		if ($session->get("CAMPAGNE")) {
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

			//------------------------------------------------
			//combo cliente
			$sql = "select id_cliente,de_nome from 7banner_clienti";
			$rs = $conn->query($sql) or die($conn->error.$sql);
			$ar[""]="--choose--";
			while($riga = $rs->fetch_array()) $ar[$riga['id_cliente']]=$riga['de_nome'];
			$cd_cliente = new optionlist("cd_cliente",($dati["cd_cliente"]),$ar);
			$cd_cliente->obbligatorio=1;
			$cd_cliente->label="'Client'";
			$objform->addControllo($cd_cliente);
			//------------------------------------------------

			if($dati['fl_status']=='1') $stati = array("1"=>"ON" ,"0"=>"OFF","2"=>"TOTALLY OFF"); else $stati= array("1"=>"ON" ,"0"=>"OFF");
			$fl_status = new optionlist("fl_status",$dati["fl_status"],$stati );
			$fl_status->obbligatorio=1;
			$fl_status->label="'Status'";
			$objform->addControllo($fl_status);

			$de_titolo = new testo("de_titolo",$dati["de_titolo"],50,50);
			$de_titolo->obbligatorio=1;
			$de_titolo->label="'Title'";
			$objform->addControllo($de_titolo);

			$id_obj = new hidden("id",$dati["id_campagna"]);
			$op = new hidden("op",$action);

			//$q = $id ? execute_scalar("select count(*) from qoq_tbc_tag_scontri WHERE cd_tag='".$id."'") : "n.d.";

			$html = str_replace("##STARTFORM##", $objform->startform(), $html);
			$html = str_replace("##fl_status##", $fl_status->gettag(), $html);
			$html = str_replace("##id##", $id_obj->gettag(), $html);
			$html = str_replace("##op##", $op->gettag(), $html);
			$html = str_replace("##cd_cliente##", $cd_cliente->gettag(), $html);
			//$html = str_replace("##dt_saved##", $dt_saved->gettag(), $html);
			$html = str_replace("##de_titolo##", $de_titolo->gettag(), $html);
			$html = str_replace("##gestore##", $this->gestore, $html);
			$html = str_replace("##ENDFORM##", $objform->endform(), $html);


		} else {
			$html = "0";
		}
		return $html;
	}

	function getDati($id) {
		return execute_row("SELECT * from ".$this->tbdb." where id_campagna='{$id}'");
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
		if ($session->get("CAMPAGNE")) {
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
				if($arDati["fl_status"] == 2) {
					$arDati["fl_status"] = 0;
					$sql = "update 7banner set fl_stato='S' where cd_campagna='".$arDati["id"]."';";
					$conn->query($sql) or die($conn->error.$sql);
				}

				$sql="UPDATE ".$this->tbdb." set
					de_titolo='##de_titolo##',
					cd_cliente='##cd_cliente##', 
					fl_status='##fl_status##'
					where id_campagna='##id##'";
				$sql= str_replace("##cd_cliente##",$arDati["cd_cliente"],$sql);
				$sql= str_replace("##de_titolo##",$arDati["de_titolo"],$sql);
				$sql= str_replace("##fl_status##",$arDati["fl_status"],$sql);
				$sql= str_replace("##id##",$arDati["id"],$sql);
				$conn->query($sql) or die($conn->error.$sql);
				$html= "ok|".$id;
			} else {
				/*
					Inserimento
				*/
				$sql="INSERT into ".$this->tbdb." (cd_cliente,de_titolo,fl_status) values('##cd_cliente##','##de_titolo##','##fl_status##')";
				$sql= str_replace("##cd_cliente##",$arDati["cd_cliente"],$sql);
				$sql= str_replace("##de_titolo##",$arDati["de_titolo"],$sql);
				$sql= str_replace("##fl_status##",$arDati["fl_status"],$sql);
				$conn->query($sql) or die($conn->error.$sql);
				$id = $conn->insert_id;
				$html= "ok|".$id;

			}



		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}


	function getHtmlcombotipo($def="") {
		global $conn;
		//------------------------------------------------
		//combo provenienze
		$sql = "select id_cliente,de_nome,count(*) as c from ".$this->tbdb."
					inner join 7banner_clienti on cd_cliente=id_cliente group by id_cliente";
		$rs = $conn->query($sql) or trigger_error($conn->error);
		$arFiltri = array("-999"=>"All");
		while($riga = $rs->fetch_array()) {
			if ($riga['id_cliente']=="") $riga['c']=0;
			$arFiltri[$riga['id_cliente']]= $riga['de_nome']." (".$riga['c'].")";
		}
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

		global $session,$conn,$root;
		if ($session->get("CAMPAGNE")) {


			$obj = new Banner();
			$obj->uploadDir = $root."data/dbimg/7banner/";
			$obj->max_files= 1;
			$rs = $conn->query("SELECT id_banner from 7banner where cd_campagna='$id'") or trigger_error($conn->error);
			while($riga = $rs->fetch_array()) {
				$obj->deleteItem($riga['id_banner']);
				//echo $riga['id_banner'];

			}
			//die;


			$sql="DELETE FROM ".$this->tbdb." where id_campagna='$id'";
			$conn->query($sql) or trigger_error($conn->error."sql='$sql'<br>");

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
		if ($session->get("CAMPAGNE")) {

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