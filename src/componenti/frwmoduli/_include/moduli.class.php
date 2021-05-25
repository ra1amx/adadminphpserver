<?php
/*
	classe per la gestione degli asili.
*/

class Moduli {

	var $tbdb;	//tabella del database che contiene i dati

	var $start;	// posizione del primo record visualizzato
	var $omode;	// asc|desc
	var $oby;	// campo della tabella $tbdb utilizzato per ordinare
	var $ps;	// numero di righe per pagina nell'elenco

	var $linkaggiungi;	//link utilizzato per "aggiungere"
	var $linkaggiungi_label;
	var $linkaggiungi_icon;

	var $linkmodifica;	//link utilizzato per il comando "modifica"
	var $linkmodifica_label;

	var $linkview;	//link utilizzato per il comando "view"
	var $linkview_label;

	var $linkelimina;	//link utilizzato per il comando "elimina"
	var $linkelimina_label;

	var $gestore;

	function __construct ($tbdb="moduli",$ps=20,$oby="posizione",$omode="asc",$start=0) {
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

		$this->linkmodifica = "$this->gestore?op=modifica&id=##id##";
		$this->linkmodifica_label = "modifica";

		$this->linkelimina = "javascript:confermaDelete('##id##');";
		$this->linkelimina_label = "elimina";

		$this->linksql = "$this->gestore?op=sql&id=##id##";
		$this->linksql_label = "settings";

		$this->linkeliminamarcate = "javascript:confermaDeleteCheck(document.datagrid);";
		$this->linkeliminamarcate_label = "Elimina record marcati";

		checkAbilitazione("FRWCOMPONENTI","SETTA_SOLO_SE_ESISTE");
		checkAbilitazione("FRWMODULI","SETTA_SOLO_SE_ESISTE");

	}

	/*
		mostra l'elenco dei componenti.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'elenco in html.
	*/
	function elenco($combotipo="",$combotiporeset="",$keyword="") {
		global $session;

		$html = "";

		if ($session->get("FRWMODULI")) {
			if($combotiporeset=='reset') {
				//se ho cambiato con la combo del filtro
				//allora resetto la paginazione.
				$this->start = 0;
			}

			$t=new grid($this->tbdb,$this->start, $this->ps, $this->oby, $this->omode);
			$t->checkboxFormAction=$this->gestore;
			$t->checkboxFormName="datagrid";
			$t->checkboxForm=true;
			$t->functionhtml = "";	// se non lo specifico processa l'html altrimenti fa l'htmlspecialchars che è il default
			$t->mostraRecordTotali = true;
			$t->functionhtml="";

			$t->parametriDaPssare = "";
			if($combotipo) {
				$t->parametriDaPssare.="&combotipo=".urlencode($combotipo);
			}
			if($keyword) $t->parametriDaPssare.="&keyword=".urlencode($keyword);

			//campi da visualizzare
			$t->campi="id,nome,label,posizione,visibile";

			//titoli dei campi da visualizzare
			$t->titoli="#ID, Nome (slug), Label menù, Posizione, Visibile";

			//id per fare i link
			$t->chiave="id";

			//query per estrarre i dati
			//$t->debug = true;
			$t->query="SELECT id, nome, label, posizione, visibile FROM frw_moduli ";

			$where = "";
			if($combotipo) {
				if($combotipo=="-999") {

				} else {
					if($where!="") { $where.= " and "; }
					$where.=" visibile='{$combotipo}'";
				}
			}
			if($keyword) {
				if($where!="") { $where.= " and "; }
				$where.="  (nome like '%{$keyword}%' or label like '%{$keyword}%')";
			}
			
			if($where) {
				$t->query.=" where {$where}";
			}

			$t->addScegliDaInsieme("visibile",
				array(
					"1"=>"<span class='labelgreen'>ON</span>",
					"0"=>"<span class='labelred'>OFF</span>"
				)
			);


			$t->addComando($this->linkmodifica,$this->linkmodifica_label,"Modifica questo record");
			$t->addComando($this->linkelimina,$this->linkelimina_label,"Elimina questo record");
			$t->addComando("javascript:abilitaModulo('##id##');","assegna","Assegna i permessi");
			$t->addComando($this->linksql,$this->linksql_label,"Copia sql configurazione");

			$texto = $t->show();

			if (trim($texto)=="") $texto="Nessun record trovato.";
			$html .= $texto."<br/>";

		} else {
			$html = "0";
		}
		return $html;
	}

	function getSql($id="") {
		global $session,$root,$conn;
		$html = "";
		if ($session->get("FRWMODULI")) {
			$sql = "";
			// modulo
			$m = execute_row("select * from frw_moduli where id='".$id."'");
			$sql .= "<h2>MODULO RICHIESTO: #".$id." &laquo;".$m['nome']."&raquo;</h2>";
			$sql .= "<div>";
			$sql .= "INSERT INTO frw_moduli (id,nome,label,visibile,posizione) VALUES (
				'".$m['id']."','".addslashes($m['nome'])."','".addslashes($m['label'])."','".$m['visibile']."','".$m['posizione']."');";
			$sql .= "</div>";
			$IDMODULO = $m['id'];

			$s = "select * from frw_com_mod where idmodulo='".$IDMODULO."'";
			$rs = $conn->query($s) or die($conn->error."sql='$sql'<br>");

			// componenti inclusi nel modulo
			while($r=$rs->fetch_array()) {
				$IDCOMPONENTE = $r['idcomponente'];

				$c = execute_row("select * from frw_componenti where id='".$IDCOMPONENTE."'");
				$sql .= "<h2>Componente: #".$IDCOMPONENTE." &laquo;".$c['nome']."&raquo;</h2>";
				$sql .= "<div>";
				$sql .= "INSERT INTO frw_componenti (id ,nome ,descrizione ,urlcomponente ,label ,urliconamenu) VALUES (
					'".$IDCOMPONENTE."','".addslashes($c['nome'])."','".addslashes($c['descrizione'])."','".addslashes($c['urlcomponente'])."','".addslashes($c['label'])."','".addslashes($c['urliconamenu'])."');";
				$sql .= "</div>";

				$sql .= "<div>";
				$sql .= "INSERT INTO frw_com_mod (idcomponente ,idmodulo,posizione) VALUES (
					'".$IDCOMPONENTE."','".$IDMODULO."','".$r['posizione']."');";
				$sql .= "</div>";


				$s = "select * from frw_funzionalita where idcomponente='".$IDCOMPONENTE."'";
				$rs1 = $conn->query($s);
				// funzionalita
				$sql .= "<h3>funzionalità del componente $IDCOMPONENTE </h3>";
				while($f=$rs1->fetch_array()) {
					$IDFUNZIONALITA = $f['id'];
					$sql .= "<div>";
					$sql .= "INSERT INTO frw_funzionalita (id ,idcomponente ,nome ,descrizione ,label) VALUES (
						'".$IDFUNZIONALITA."','".$f['idcomponente']."','".addslashes($f['nome'])."','".addslashes($f['descrizione'])."','".addslashes($f['label'])."');";
					$sql .= "</div>";
					$s = "select * from frw_profili_funzionalita where cd_funzionalita='".$IDFUNZIONALITA."' AND cd_modulo='".$IDMODULO."'";
					$rs2 = $conn->query($s);
					// funzionalita' profili
					while($p=$rs2->fetch_array()) {
						$IDFUNZIONALITA = $f['id'];
						$sql .= "<div>";
						$sql .= "INSERT INTO frw_profili_funzionalita (cd_profilo ,cd_modulo ,cd_funzionalita) VALUES (
							'".$p['cd_profilo']."','".$IDMODULO."','".$IDFUNZIONALITA."');";
						$sql .= "</div>";
					}

				}

			}
			//print_r($m);
			$html = loadTemplate ("template/sql.html");
			$html = str_replace("##corpo##", $sql, $html);


			
		}
		return $html;

	}
	/*
		mostra il dettaglio.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'html.
	*/
	function getDettaglio($id="") {
		global $session,$root;

		if ($session->get("FRWMODULI")) {
			if ($id!="") {
				/*
					modifica
				*/
				$dati = $this->getDati($id);
				$action = "modificaStep2";

				$t=new grid("frw_com_mod",0, 999, "posizione", "asc");
				$t->functionhtml = "";
				$t->chiave = "id";
				$t->mostraRecordTotali = false;
				$t->flagOrdinatori = false;
				$t->campi="nome,label,posizione";
				$t->titoli="Nome,Label,Posizione";
				$t->query="SELECT * FROM frw_com_mod inner join frw_componenti on id=idcomponente where idmodulo='{$id}'";
				$t->addComando("indexcomponenti.php?op=modifica&id=##idcomponente##&cd_item=##idmodulo##","modifica","Modifica questo record");
				$t->addComando("javascript:confermaDeleteComponente('##idcomponente##','##idmodulo##');","elimina","Elimina questo record");

				$tcollegati = $t->show();
				$taddcollegati = "<div class='panel'><a href='indexcomponenti.php?op=aggiungi&cd_item={$id}' class='aggiungi'> aggiungi un componente</a></div>";

			} else {
				/*
					inserimento
				*/
				$dati = getEmptyNomiCelleAr("frw_moduli") ;
				$action = "aggiungiStep2";
				$tcollegati = "";
				$taddcollegati = "";

			}

			$html = loadTemplateAndParse("template/dettaglio.html");

			//costruzione form
			$objform = new form();
			$objform->pathJsLib = $root."src/template/controlloform.js";

			$nome = new testo("nome",$dati["nome"],100,100);
			$nome->obbligatorio=1;
			$nome->label="'Nome'";
			$objform->addControllo($nome);
			
			$label = new testo("label",$dati["label"],100,100);
			$label->obbligatorio=1;
			$label->label="'label'";
			$objform->addControllo($label);

			$visibile = new optionlist("visibile",$dati["visibile"],array("1"=>"ON" ,"0"=>"OFF") );
			$visibile->obbligatorio=0;
			$visibile->label="'Visibile'";
			$objform->addControllo($visibile);

			$arpos = array(); for($i=0;$i<99;$i++) $arpos[$i]=$i;
			$posizione = new optionlist("posizione",$dati["posizione"],$arpos );
			$posizione->obbligatorio=0;
			$posizione->label="'Posizione'";
			$objform->addControllo($posizione);


			$id = new hidden("id",$dati["id"]);
			$op = new hidden("op",$action);

			$submit = new submit("invia","salva");

			
			$html = str_replace("##STARTFORM##", $objform->startform(), $html);
			$html = str_replace("##id##", $id->gettag(), $html);
			$html = str_replace("##op##", $op->gettag(), $html);
			//$html = str_replace("##submit##", $submit->gettagimage($root."src7images/salva.gif"," Salva"), $html);
			$html = str_replace("##visibile##", $visibile->gettag(), $html);
			$html = str_replace("##posizione##", $posizione->gettag(), $html);
			$html = str_replace("##label##", $label->gettag(), $html);
			$html = str_replace("##nome##", $nome->gettag(), $html);
			$html = str_replace("##TCOLLEGATI##", $tcollegati, $html);
			$html = str_replace("##TADDCOLLEGATI##", $taddcollegati, $html);

			$html = str_replace("##gestore##", $this->gestore, $html);
			$html = str_replace("##ENDFORM##", $objform->endform(), $html);


		} else {
			$html = "0";
		}
		return $html;
	}


	function getDati($id) {
		return execute_row("SELECT * from frw_moduli where id='{$id}'");
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
	
			if ($arDati["id"]!="") {
				$id = $arDati["id"];
				/*
					Modifica
				*/

				$sql="UPDATE frw_moduli set
					nome='##nome##',
					label='##label##',
					posizione='##posizione##',
					visibile='##visibile##'
					where id='##id##'";
				$sql= str_replace("##nome##",$arDati["nome"],$sql);
				$sql= str_replace("##label##",$arDati["label"],$sql);
				$sql= str_replace("##posizione##",$arDati["posizione"],$sql);
				$sql= str_replace("##visibile##",$arDati["visibile"],$sql);
				$sql= str_replace("##id##",$arDati["id"],$sql);
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");

				$html= "ok|".$id;
			} else {
				/*
					Inserimento
				*/

				$sql="INSERT into frw_moduli (nome,label,posizione,visibile) values('##nome##','##label##','##posizione##','##visibile##')";
				$sql= str_replace("##nome##",$arDati["nome"],$sql);
				$sql= str_replace("##label##",$arDati["label"],$sql);
				$sql= str_replace("##posizione##",$arDati["posizione"],$sql);
				$sql= str_replace("##visibile##",$arDati["visibile"],$sql);
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$id = $conn->insert_id;
				$html= "ok|".$id;

			}

		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}




	function deleteItem($id) {
		global $session,$conn;
		if ($session->get("FRWMODULI")) {

			$sql="DELETE FROM frw_moduli where id='$id'";
			$conn->query($sql) or die($conn->error."sql='$sql'<br>");
			$sql="DELETE FROM frw_com_mod where idmodulo='$id'";
			$conn->query($sql) or die($conn->error."sql='$sql'<br>");
			$sql="DELETE from frw_profili_funzionalita where cd_modulo='$id'";
			$conn->query($sql) or die($conn->error."sql='$sql'<br>");
			$sql="DELETE from frw_ute_fun where idmodulo='$id'";
			$conn->query($sql) or die($conn->error."sql='$sql'<br>");
			$html = "|Record rimosso|load index.php";

		} else {
			$html="-1|Il tuo profilo non ti consente di cancellare.|jsback";
		}
		return $html;

	}
	function eliminaSelezionati($dati) {
		global $session,$conn;
		if ($session->get("FRWMODULI")) {
			$html="";
			$idx ="";
			$p=$dati['gridcheck'];
			$idx = implode(", ",$p);
			if ($idx) {
				// controllo integrità referenziale

				$sql="DELETE FROM frw_moduli where id in ($idx)";
				//echo "$sql<br>";
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$sql="DELETE FROM frw_com_mod where idmodulo in ($idx)";
				//echo "$sql<br>";
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$sql="DELETE from frw_profili_funzionalita where cd_modulo in ($idx)";
				//echo "$sql<br>";
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$sql="DELETE from frw_ute_fun where idmodulo in ($idx)";
				//echo "$sql<br>";
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$html = "";

				$html = "|Record rimosi|load index.php";

			}
		} else {
			$html="-1|Non sei autorizzato|jsback";		//il tuo profilo non ti consente di cancellare
		}
		return $html;
	}


	function getHtmlcombotipo($def="") {
		global $conn;
		//------------------------------------------------
		//combo provenienze
		$sql = "select visibile,count(*) as c
			from frw_moduli group by visibile";
		$rs = $conn->query($sql) or die($conn->error."sql='$sql'<br>");
		$arFiltri = array("-999"=>"Tutti");
		while($riga = $rs->fetch_array()) {
			if ($riga['visibile']=="1") $arFiltri[$riga['visibile']] = "Visibili (".$riga['c'].")";
			if ($riga['visibile']=="0") $arFiltri[$riga['visibile']] = "Standby (".$riga['c'].")";
		}
		//------------------------------------------------
		$out = "";
		foreach ($arFiltri as $k => $v) { $out.="<option value='{$k}' ".(($k."x"==$def."x")?"selected":"").">{$v}</option>"; }
		return "<select onchange='aggiornaGriglia()' name='combotipo' id='combotipo'>{$out}</select><input type='hidden' name='combotiporeset' id='combotiporeset'>";

	}

	function profila($idm,$check="YES") {
		global $session;
		/*
			recupero l'elenco dei componenti di questo modulo e per
			ogni componente richiamo il metodo "profila" dell'oggetto di componenti.class.php
			per distribuire le funzionalita' agli utenti.
		*/
		//echo $session->get("FRWCOMPONENTI");
		//die;
		if ($check!="NOSESSION" && $session->get("FRWCOMPONENTI")=="") return "0";

		$idcompo = $this->getElencoId("select idcomponente from frw_com_mod where idmodulo = '$idm'");
		if ($idcompo=="") return "1";
		$arCompo = explode(",",$idcompo);
		$html="";
		for ($i=0;$i<count($arCompo);$i++) {
			$com = new componenti();
			$html.="componente: <b>{$arCompo[$i]}</b><br>".$com->profila($arCompo[$i],$check)."<br>";
			unset($com);
		}
		return $html;
	}

	function profila_service($idc) {
		// metodo aggiunto per utilizzare la distribuzione dei permessi in maniera
		// applicativa senza utilizzare un utente reale e i suoi permessi.
		$html = $this->profila($idc,"NOSESSION");
		return $html!="0" ? "ok" : "ko";
	}

	
	/*
		estrae una stringa con i record sepatati da una virgola.
		prende il primo item dei record estratti.
	*/
	function getElencoId($sql) {
		return concatenaId($sql);
	}

}

?>