<?php
/*
	classe per la gestione degli asili.
*/

class Constants {

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

	var $linkview;	//link utilizzato per il comando "view"
	var $linkview_label;

	var $linkelimina;	//link utilizzato per il comando "elimina"
	var $linkelimina_label;

	var $gestore;


	function __construct($tbdb="frw_vars",$ps=20,$oby="de_nome",$omode="desc",$start=0) {
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
		$this->linkaggiungi_label = "Aggiungi";

		$this->linkmodifica = "$this->gestore?op=modifica&id=##id_var##";
		$this->linkmodifica_label = "modifica";

		//		$this->linkview = "javascript:popofferta(##id_viaggio##);";
		//		$this->linkview_label = "<img src=\"images/layout_content.png\" align=\"absmiddle\" alt=\"visualizza la locandina\" border=\"0\">";

		$this->linkelimina = "javascript:confermaDelete('##id_var##');";
		$this->linkelimina_label = "elimina";

		$this->linkeliminamarcate = "javascript:confermaDeleteCheck(document.datagrid);";
		$this->linkeliminamarcate_label = "Elimina record marcati";

		checkAbilitazione("CONSTANTSSETTINGS","SETTA_SOLO_SE_ESISTE");


	}

	/*
		mostra l'elenco dei componenti.
		ritorna 0 se l'utente non ?? abilitato, altrimenti restituisce l'elenco in html.
	*/
	function elenco($combotiporeset="",$keyword="") {
		global $session;

		$html = "";

		if ($session->get("CONSTANTSSETTINGS")) {

			if($combotiporeset=='reset') {
				//se ho cambiato con la combo del filtro
				//allora resetto la paginazione.
				$this->start = 0;
			}

			$t=new grid($this->tbdb,$this->start, $this->ps, $this->oby, $this->omode);
			$t->checkboxFormAction=$this->gestore;
			$t->checkboxFormName="datagrid";
			$t->checkboxForm=false;
			$t->functionhtml = "";
			$t->mostraRecordTotali = true;

			$t->parametriDaPssare = "";
			if($keyword) $t->parametriDaPssare.="&keyword=".urlencode($keyword);

			//campi da visualizzare
			$t->campi="de_nome,de_value";

			//titoli dei campi da visualizzare
			$t->titoli="Setting name, Value";

			//id per fare i link
			$t->chiave="id_var";

			//query per estrarre i dati
			//$t->debug = true;
			$t->query="SELECT id_var,REPLACE(de_nome, 'CONST_', '') as de_nome,de_value from frw_vars";

			$where = " de_nome like 'CONST_%'";
			
			if($keyword) {
				if($where!="") { $where.= " and "; }
				$where.="  (de_nome like '%{$keyword}%' or de_value like '%{$keyword}%')";
			}
			if($where) {
				$t->query.=" where {$where}";
			}

			$t->addComando($this->linkmodifica,$this->linkmodifica_label,"Modifica questo record");
			//$t->addComando($this->linkelimina,$this->linkelimina_label,"Elimina questo record");

			$texto = $t->show();

			if (trim($texto)=="") $texto="Nothing.";
			$html .= $texto."<br/>";

		} else {
			$html = "0";
		}
		return $html;
	}


	/*
		mostra il dettaglio.
		ritorna 0 se l'utente non ?? abilitato, altrimenti restituisce l'html.
	*/
	function getDettaglio($id="") {
		global $session,$root;

		if ($session->get("CONSTANTSSETTINGS")) {
			if ($id!="") {
				/*
					modifica
				*/
				$dati = $this->getDati($id);
				$action = "modificaStep2";
			} else {
				/*
					inserimento
				*/
				$dati = getEmptyNomiCelleAr("frw_vars") ;
				$action = "aggiungiStep2";
			}

			$html = loadTemplateAndParse("template/dettaglio.html");

			//costruzione form
			$objform = new form();

			$de_value = new testo("de_value", $dati["de_value"],150,50 );
			$de_value->obbligatorio=1;
			$de_value->label="'Valore'";
			$objform->addControllo($de_value);

			if($dati["de_nome"] == "CONST_DATEFORMAT") {

				$de_value = new optionlist("de_value", DATEFORMAT, array("dd/mm/yyyy"=>"DD/MM/YYYY","mm/dd/yyyy"=>"MM/DD/YYYY") );
				$de_value->obbligatorio=1;
				$de_value->label="'Date format'";
				$objform->addControllo($de_value);

			}

			if($dati["de_nome"] == "CONST_MONEY") {

				$de_value = new optionlist("de_value", MONEY, array("???"=>"??? - Euro currency","$"=>"$ - USD currency") );
				$de_value->obbligatorio=1;
				$de_value->label="'Currency'";
				$objform->addControllo($de_value);

			}

			$de_nome = new hidden("de_nome",$dati["de_nome"]);


			$id_var = new hidden("id",$dati["id_var"]);
			$op = new hidden("op",$action);

			$submit = new submit("invia","salva");

			$html = str_replace("##STARTFORM##", $objform->startform(), $html);
			$html = str_replace("##id##", $id_var->gettag(), $html);
			$html = str_replace("##op##", $op->gettag(), $html);
			$html = str_replace("##const##", str_replace("CONST_","",$dati["de_nome"]), $html);
			$html = str_replace("##de_nome##", $de_nome->gettag(), $html);
			$html = str_replace("##de_value##", $de_value->gettag(), $html);
			$html = str_replace("##gestore##", $this->gestore, $html);
			$html = str_replace("##ENDFORM##", $objform->endform(), $html);


		} else {
			$html = "0";
		}
		return $html;
	}

	function getDati($id) {
		return execute_row( "SELECT * from frw_vars where id_var='{$id}' AND de_nome like 'CONST_%'" );
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
		if ($session->get("CONSTANTSSETTINGS")) {
			$session->register("CONST_LOGO","");

			if ($arDati["id"]!="") {
				$id = $arDati["id"];
				/*
					Modifica
				*/

				$sql="UPDATE frw_vars set
					de_nome='##de_nome##',
					de_value='##de_value##'
					where id_var='##id_var##' AND de_nome like 'CONST_%'";
				$sql= str_replace("##de_nome##",$arDati["de_nome"],$sql);
				$sql= str_replace("##de_value##",$arDati["de_value"],$sql);
				$sql= str_replace("##id_var##",$arDati["id"],$sql);
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$html= "";
			} else {
				/*
					Inserimento
				*/
				$sql="INSERT into frw_vars (de_value,de_nome) values('##de_value##','##de_nome##')";
				$sql= str_replace("##de_value##",$arDati["de_value"],$sql);
				$sql= str_replace("##de_nome##",$arDati["de_nome"],$sql);
				$conn->query($sql);
				if($conn->errno==1062) {
					return "-1|Record gi?? inserito";
				}
				//or 
				$html= "";
				$id = $conn->insert_id;

			}

			

		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}




	function deleteItem($id) {
		// in:
		// id --> id tipo da cancellare
		// risultato:
		//	"" --> ok
		//  "0" -->il tuo profilo non ti consente la cancellazione

		global $session,$conn;
		if ($session->get("CONSTANTSSETTINGS")) {
			$sql="DELETE FROM frw_vars where id_var='$id' AND de_nome like 'CONST_%'";
			//echo "$sql<br>";
			$conn->query($sql) or die($conn->error."sql='$sql'<br>");
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

		global $session,$conn;
		if ($session->get("CONSTANTSSETTINGS")) {

			$html="0";
			$idx ="";

			$p=$dati['gridcheck'];
			for ($i=0;$i<count($p);$i++) {
				if ($idx) $idx.=", ";
				$idx .= $p[$i];
				$id = $p[$i];

			}
			if ($idx) {

				$sql = "delete from frw_vars where id_var in ($idx) AND de_nome like 'CONST_%'";
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");


			}
			$html = "";
		} else {
			$html="0";		//il tuo profilo non ti consente di cancellare
		}
		return $html;
	}



}

?>