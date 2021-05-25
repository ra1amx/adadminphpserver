<?php
/*
	classe per la gestione degli asili.
*/

class Banner {

	var $tbdb;	//tabella del database che contiene i dati

	var $start;	// posizione del primo record visualizzato
	var $omode;	// asc|desc
	var $oby;	// campo della tabella $tbdb utilizzato per ordinare
	var $ps;	// numero di righe per pagina nell'elenco

	var $linkaggiungi;	//link utilizzato per "aggiungere"
	var $linkaggiungi_label;

	var $linkmodifica;	//link utilizzato per il comando "modifica"
	var $linkmodifica_label;

	var $linkstats;	//link utilizzato per il comando "stats"
	var $linkstats_label;

	var $linkelimina;	//link utilizzato per il comando "elimina"
	var $linkelimina_label;

	var $gestore;


	function __construct ($tbdb="7banner",$ps=20,$oby="dt_giorno1",$omode="desc",$start=0) {
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

		// link sopra, nel panel
		$this->linkaggiungi = "$this->gestore?op=aggiungi";
		$this->linkaggiungi_label = "Add";

		$this->linkeliminamarcate = "javascript:confermaDeleteCheck(document.datagrid);";
		$this->linkeliminamarcate_label = "Delete selected";

		// link in griglia
		$this->linkduplica = "$this->gestore?op=duplica&id=##id_banner##";
		$this->linkduplica_label = "duplica";

		$this->linkmodifica = "$this->gestore?op=modifica&id=##id_banner##";
		$this->linkmodifica_label = "modifica";

		$this->linkstats = "$this->gestore?op=stats&id=##id_banner##";
		$this->linkstats_label = "stats";

		$this->linkelimina = "javascript:confermaDelete('##id_banner##');";
		$this->linkelimina_label = "elimina";

		checkAbilitazione("BANNER","SETTA_SOLO_SE_ESISTE");


	}

	/*
		mostra l'elenco dei componenti.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'elenco in html.
	*/
	function elenco($combotipo="",$combotiporeset="",$keyword="") {
		global $session;

		$html = "";

		if ($session->get("BANNER")) {
			if($combotiporeset=='reset') {
				//se ho cambiato con la combo del filtro
				//allora resetto la paginazione.
				$this->start = 0;
			}
			//$this->ps = 200;
			$t=new grid($this->tbdb,$this->start, $this->ps, $this->oby, $this->omode);
			$t->checkboxFormAction=$this->gestore;
			$t->checkboxFormName="datagrid";
			$t->checkboxForm= ($session->get("idprofilo")>5) ? true : false;
			$t->functionhtml = "";	// se non lo specifico processa l'html altrimenti fa l'htmlspecialchars che è il default
			$t->mostraRecordTotali = true;

			$t->parametriDaPssare = "";
			if($combotipo) {
				$t->parametriDaPssare.="&combotipo=".urlencode($combotipo);
			}
			if($keyword) $t->parametriDaPssare.="&keyword=".urlencode($keyword);

			//campi da visualizzare
			$t->campi="cliente,nome,dt_giorno1,dt_giorno2,giorni,nu_pageviews,tasso,nu_clicks,CTR,de_posizione,fl_stato";

			//titoli dei campi da visualizzare
			$t->titoli="Client and Campaign,Banner,From,To,Days,Impressions,<acronym title='Daily impressions'>Daily</acronym>,Clicks,<acronym title='Impressions/clicks'>CTR</acronym>,Position,Status";

			//id per fare i link
			$t->chiave="id_banner";

			$miocliente = "";
			if( $session->get("idprofilo") == 5) {
				// profilo guest vede solo i banner suoi
				$miocliente = concatenaId("select cd_cliente from 7banner_clienti_tbc where cd_utente='".$session->get("idutente")."'");
				if ($miocliente) $miocliente = " AND cd_cliente in (".$miocliente.")"; else $miocliente = " AND 1=0";
			}



			//query per estrarre i dati
			//$t->debug = true;
			$t->query="SELECT id_banner, CONCAT(7banner_clienti.de_nome,'<div class=''small''>',de_titolo,'</div>') as cliente, CONCAT(7banner.de_nome,'|^',fl_stato,'|^',CAST(id_banner AS CHAR CHARACTER SET utf8)) as nome,dt_giorno1,dt_giorno2,
				DATEDIFF(dt_giorno2,dt_giorno1)+1 as giorni,
				nu_pageviews,
				(
				CASE WHEN dt_giorno1>=CURDATE() THEN '-'
				WHEN dt_giorno2>CURDATE() THEN 
				ROUND(nu_pageviews/DATEDIFF(CURDATE(),dt_giorno1) ) ELSE
				ROUND(nu_pageviews/DATEDIFF(dt_giorno2,dt_giorno1) ) 
				END ) as tasso,
				nu_clicks,
				(CASE WHEN dt_giorno1>=CURDATE() THEN '-' ELSE CONCAT( (100 * nu_clicks / nu_pageviews),'%') END) as CTR,de_posizione,fl_stato FROM ".$this->tbdb." 
				LEFT OUTER JOIN 7banner_posizioni ON cd_posizione=id_posizione
				INNER JOIN 7banner_campagne ON cd_campagna=id_campagna
				INNER JOIN 7banner_clienti ON 7banner_campagne.cd_cliente=id_cliente

				$miocliente

				";


			$where = "";
			if($combotipo) {
				if($combotipo=="-999") {

				} else {

					$temp = explode("|",$combotipo);

					if($temp[1]=='A') {
						$temp[2] = " in ('A','L','P') ";
					} elseif($temp[1]=='S') {
						$temp[2] = " = 'S' ";
					} elseif($temp[1]=='T') {
						$temp[2] = " in ('A','L','P') ";
					} elseif($temp[1]=='X') {
						$temp[2] = " = 'S' ";
					}
					if($where!="") { $where.= " and "; }
					$where.=($temp[0]!="-999" ? "cd_posizione='{$temp[0]}' and " : "") ." fl_stato ".$temp[2]." ";
				}
			}
			if($keyword) {
				if($where!="") { $where.= " and "; }
				$where.="  (7banner.de_nome like '%{$keyword}%' or  7banner_clienti.de_nome like '%{$keyword}%' or  de_url like '%{$keyword}%')";
			}
			
			if($where) {
				$t->query.=" where {$where}";
			}
			$t->addCampi('nome',"moremenu");

			$t->addScegliDaInsieme("fl_stato",
				array(
					"A"=>"<span class='labelgreen'>SERVING</span>",
					"S"=>"<span class='labelred'>ENDED</span>",
					"L"=>"<span class='labelgreen'>SERVING</span>",
					"P"=>"<span class='labelyellow'>PAUSED</span>",
				)
			);
			$t->arFormattazioneTD=array(
				"giorni" => "numero",
				"nu_pageviews" => "numero",
				"nu_clicks" => "numero",
				"tasso" => "numero",
				"CTR" => "numero",
			);

			

			if($session->get("idprofilo")>5) $t->addComando($this->linkmodifica,$this->linkmodifica_label,"Edit");
			$t->addComando($this->linkstats,$this->linkstats_label,"Stats");
			if($session->get("idprofilo")>5) $t->addComando($this->linkduplica,$this->linkduplica_label,"Make a copy");
			if($session->get("idprofilo")>5) $t->addComando($this->linkelimina,$this->linkelimina_label,"Delete");

			$t->addCampiDate("dt_giorno1",DATEFORMAT);
			$t->addCampiDate("dt_giorno2",DATEFORMAT);
			$texto = $t->show();

			if (trim($texto)=="") $texto="No records found.";
			$html .= $texto."<br/>";

		} else {
			$html = "0";
		}
		return $html;
	}

	function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
	{
		if(function_exists("date_diff")) {
			$datetime1 = date_create($date_1);
			$datetime2 = date_create($date_2);
			$interval = date_diff($datetime1, $datetime2);
			return $interval->format($differenceFormat);
		} else {
			return date_diff2($date_1, $date_2);
		}
	}

	function getCharts($id,$combobanner,$startdateData="",$enddateData="") {
		global $session;
		$def = 30;

		
		if ($session->get("BANNER") && $this->check_cliente($id ? $id : $combobanner)) {
			$html = loadTemplateAndParse ("template/charts.html");

			$v = $id ? $id : $combobanner;

			if(!stristr($v,"-999|")) $sql = "select MIN(dt_giorno1) from 7banner where id_banner=".(integer)$v;
			else $sql = "select MIN(dt_giorno1) from 7banner where cd_campagna=".((integer)(str_replace("-999|","",$combobanner)));
			$d = execute_scalar($sql);

 			if($startdateData=="") {
				$startdateData = DateAdd(-1,$d,"Y-m-d");
				$enddateData = DateAdd($def,$startdateData,"Y-m-d");
			}
			if($startdateData > date("Y-m-d")) $startdateData= date("Y-m-d");

			$period = $this->dateDifference($startdateData,$enddateData) + 1;



			if(stristr($combobanner,"|")) {
				$temp = explode("|",$combobanner);
				if($temp[0]=="-999") {
					$dati['cd_campagna'] = $temp[1];
					$dati['de_nome'] = "All";
					$ids = concatenaId("select id_banner from 7banner where cd_campagna='".$dati['cd_campagna']."'");
				}
			} else {

				$dati = $this->getDati($id ? $id : $combobanner);
				$dati["de_posizione"] = execute_scalar("select de_posizione from 7banner_posizioni where id_posizione='".$dati['cd_posizione']."'");
				$html = str_replace("##LABEL##", $dati["de_posizione"], $html);
				$html = str_replace("##IDPOS##", $dati['cd_posizione'] , $html);
				$ids = $dati['id_banner'];

			}
			

			$cli = execute_row("select 7banner_clienti.de_nome,7banner_campagne.de_titolo from 7banner_campagne inner join 7banner_clienti on cd_cliente=id_cliente and id_campagna='".$dati['cd_campagna']."'");
			$giorni = $this->giorni($period,$startdateData);
			$html = str_replace("##GIORNI##", $giorni, $html);

			$serie = $this->serie($ids ,$period,$startdateData);
			preg_match_all("/\[([^\]]*)\]/",$serie,$obj);
			$lista = "";
			if(isset($obj[1]) && isset($obj[1][0]) && $obj[1][0]!="") {
				$pv = explode(",",$obj[1][0]); // pageviews
				$cl = explode(",",$obj[1][1]); // clicks
				$gg = explode(",",$giorni);    // days
				$lista .= "<h1 style='text-align:center;margin-top:50px'>From: ".$startdateData." To: ".$enddateData."</h2>";
				$lista .= "<style>table.griglia td.right,table.griglia th.right {text-align:right;padding-right:10px}table.griglia td.left {text-align:left}</style><table class='griglia' style='margin:0 auto;min-width:80%'>";
				$q=0;
				$tc = 0; // total clicks
				$tv = 0; // total views
	
				foreach($pv as $v){
					if($q==0) {
						$lista.= "<tr><th>Day</th><th>Views</th><th>Clicks</th></tr>";
					}
					$gg[$q] = str_replace("'","",$gg[$q]);
					$lista .= "<tr>";
					$lista .= "<td class='left'>".$gg[$q]."</td>";
					$lista .= "<td class='right'>$v</td>";
					$lista .= "<td class='right'>".$cl[$q]."</td>";
					$lista .= "</tr>";

					$tc = $tc+$cl[$q];
					$tv = $tv+$v;


					$q++;


				}
				$lista .=  "<tr><th class='right'>Totals</th><th class='right'>".$tv."</th><th class='right'>".$tc."</th></tr>";
				$lista .= "</table>";
			}


			$html = str_replace("##STATSLIST##",$lista,$html);

			$html = str_replace("##SERIE##", $serie, $html);
			
			$html = str_replace("##combobanner##", $this->getHtmlcomboBanner($id ? $id : $combobanner, $dati['cd_campagna']), $html);

			$html = str_replace("##CLIENTE##", addslashes($cli['de_nome'] . " / ". $cli['de_titolo']), $html);
			$html = str_replace("##NOME##", addslashes($dati['de_nome']) , $html);

			$objform = new form();
			$objform->method="GET";
			//global $root;
			//$objform->pathJsLib = $root."template/controlloform.js";

			$startdate = new data("startdate",$startdateData,"aaaa-mm-gg", $objform->name);
			$startdate->obbligatorio=1;
			$startdate->label="'Start date'";
			$objform->addControllo($startdate);

			$enddate = new data("enddate",$enddateData,"aaaa-mm-gg",$objform->name);
			$enddate->obbligatorio=1;
			$enddate->label="'End date'";
			$objform->addControllo($enddate);

			$op = new hidden("op","stats");
			$submit = new submit("submitta","view");

			##HIDEME##


			$html = str_replace("##STARTFORM##", $objform->startform(), $html);

			$html = str_replace("##STARTDATEDATA##", $startdateData , $html);

			$html = str_replace("##enddate##", $enddate->gettag() , $html);
			$html = str_replace("##startdate##", $startdate->gettag() , $html);

			$html = str_replace("##op##", $op->gettag(), $html);
			$html = str_replace("##SUBMIT##", $submit->gettag(), $html);
			$html = str_replace("##gestore##", $this->gestore, $html);
			$html = str_replace("##ENDFORM##", $objform->endform(), $html);





		} else {
			$html = "0";
		}
		return $html;
	}



	/*function getScriptTags() {
		global $session,$root;

		if ($session->get("BANNER")) {
			$html = loadTemplate("template/default.html");

			$sql = "select id_posizione,
				count(*) as c
				from 7banner_posizioni 
				order by id_psozione";
			$rs = mysql_query($sql) or trigger_error(mysql_error());
			$arFiltri = array("Attivi"=>"");
			$old = "A";
			$out = "";
			while($riga = mysql_fetch_array($rs)) {
				$script = htmlspecialchars('<script type="text/javascript" src="http://'.$_SERVER['HTTP_HOST'].'/adserve.php?f='.urlencode($riga['id_posizione']).'"></script>');

				$out.=" <div style='padding:30px 10px 10px 10px; margin-bottom:30px;border-bottom:1px solid #ddd'>Per utilizzare la posizione <b>".$riga['de_posizione']."</b> (#ID = ".$riga['id_posizione'].") utilizza questo script: <br><br/><tt>".$script."</tt></div>";
			}
			//------------------------------------------------
			$html = str_replace("##corpo##", $out, $html);

		} else {
			$html = "0";
		}
		return $html;
	}*/


	function check_cliente($id) {
		// 1) id può essere un banner id
		// 2) id può essere un -999|idcampagna
		global $session;

		if($session->get("idprofilo")==5) {
			if(!$id) return false;
			if(stristr($id,"|")) {
				$temp = explode("|",$id);
				if($temp[0]=="-999") {
					$cd_campagna= $temp[1];
					$q = execute_scalar("
						select count(*) from 7banner_campagne 
							inner join 7banner_clienti_tbc ON 7banner_clienti_tbc.cd_cliente=7banner_campagne.cd_cliente 
							where id_campagna='".$cd_campagna."'"
					);
					return $q>=1;
				}
				//$id = $temp[0]; 11/11/2016 da verificare, aggiunto, mancava 
				//return false;
			}
			$q = execute_scalar( "select count(*) from 7banner inner join 7banner_campagne on cd_campagna=id_campagna
				inner join 7banner_clienti_tbc on 7banner_clienti_tbc.cd_cliente=7banner_campagne.cd_cliente
				WHERE id_banner='$id' AND cd_utente='".$session->get("idutente")."'");
			return $q>=1;
		} else return true;
	}

	/*
		mostra il dettaglio.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'html.
	*/
	function getDettaglio($id="",$op="modifica") {
		global $session,$root,$conn;

		if ($session->get("BANNER") && $this->check_cliente($id) ) {
			if ($id!="") {
				/*
					modifica o duplica
				*/
				$dati = $this->getDati($id);
				if($op=='duplica') {
					$dati['id_banner']='';
					$id = '';
					$dati["de_nome"] = '[COPY] '.$dati["de_nome"];
					$ts1 = strtotime($dati["dt_giorno1"]);
					$ts2 = strtotime($dati["dt_giorno2"]);
					$seconds_diff = $ts2 - $ts1;
					$dati["dt_giorno1"]= $dati["dt_giorno1"] > date("Y-m-d") ? $dati["dt_giorno1"] : date("Y-m-d");
					$dati["dt_giorno2"]= date("Y-m-d",(strtotime($dati["dt_giorno1"]) + $seconds_diff));
					$dati["nu_clickthrought"]= 0;
					$dati["nu_pageviews"]= 0;
					$dati["nu_clicks"]= 0;

					$action= "aggiungiStep2";
				} else {
					$action = "modificaStep2";
				}
			} else {
				/*
					inserimento
				*/
				$dati = getEmptyNomiCelleAr($this->tbdb) ;
				$dati["nu_maxday"] = "0";
				$dati["nu_maxtot"] = "0";
				$dati["nu_maxclick"] = "0";
				$action = "aggiungiStep2";
			}

			$html = loadTemplateAndParse("template/dettaglio.html");

			//costruzione form
			$objform = new form();
			//$objform->pathJsLib = $root."template/controlloform.js";

			$de_nome = new testo("de_nome",$dati["de_nome"],100,50);
			//$de_nome->attributes=" style='width:500px'";
			$de_nome->obbligatorio=1;
			$de_nome->label="'Title'";
			$objform->addControllo($de_nome);
		
			$nu_maxtot = new intero("nu_maxtot",$dati["nu_maxtot"],10,10);
			$nu_maxtot->obbligatorio=0;
			$nu_maxtot->label="'Max total impressions'";
			$objform->addControllo($nu_maxtot);

			$nu_maxday = new intero("nu_maxday",$dati["nu_maxday"],10,10);
			$nu_maxday->obbligatorio=0;
			$nu_maxday->label="'Max daily impressions'";
			$objform->addControllo($nu_maxday);

			$nu_maxclick = new intero("nu_maxclick",$dati["nu_maxclick"],10,10);
			$nu_maxclick->obbligatorio=0;
			$nu_maxclick->label="'Max total click'";
			$objform->addControllo($nu_maxclick);

			$nu_price = new numerodecimale("nu_price",number_format((float)$dati["nu_price"],2),10,10,2);
			$nu_price->obbligatorio=0;
			$nu_price->label="'Price'";
			$nu_price->attributes.=' style="text-align:right"';
			$objform->addControllo($nu_price);

			$de_codicescript = new areatesto("de_codicescript",(($dati["de_codicescript"])),5,90);
			$de_codicescript->obbligatorio=0;
			$de_codicescript->attributes=" style='width:500px'";
			$de_codicescript->label="'Alternative script";
			$objform->addControllo($de_codicescript);

			$valore = ($dati["dt_giorno1"]=="") ? date("Y-m-d") : $dati["dt_giorno1"];
			$dt_giorno1 = new data("dt_giorno1",$valore,"aaaa-mm-gg",$objform->name);
			$dt_giorno1->obbligatorio=1;
			$dt_giorno1->label="'Start date'";
			$objform->addControllo($dt_giorno1);

			$valore = ($dati["dt_giorno2"]=="") ? date("Y-m-d") : $dati["dt_giorno2"];
			$dt_giorno2 = new data("dt_giorno2",$valore,"aaaa-mm-gg",$objform->name);
			$dt_giorno2->obbligatorio=1;
			$dt_giorno2->label="'End date'";
			$objform->addControllo($dt_giorno2);

			$fl_stato = new optionlist("fl_stato",(($dati["fl_stato"])),
				array("A"=>"Serving" ,"S"=>"Ended" ,"P"=>"Paused") );
			$fl_stato->obbligatorio=0;
			$fl_stato->label="'Stato'";
			$objform->addControllo($fl_stato);

			$de_target = new optionlist("de_target",$dati["de_target"],
				array("_blank"=>"New window" ,"_self"=>"Same window") );
			$de_target->obbligatorio=0;
			$de_target->label="'Target'";
			$objform->addControllo($de_target);

			//$en_formato = new optionlist("en_formato",$dati["en_formato"],set_and_enum_values($this->tbdb,'en_formato') );
			//$en_formato->obbligatorio=0;
			//$en_formato->label="'Formato'";
			//$objform->addControllo($en_formato);

			$de_url = new urllink("de_url",$dati["de_url"],255,60);
			$de_url->obbligatorio=0;
			$de_url->label="'Link'";
			$objform->addControllo($de_url);

			//------------------------------------------------
			//combo campagna
			
			$miocliente = $session->get("idprofilo")==5 ? concatenaId("select cd_cliente from 7banner_clienti_tbc where cd_utente='".$session->get("idutente")."'") : "";
			if ($miocliente) $miocliente = " AND cd_cliente in (".$miocliente.")";

			$sql = "select id_campagna,de_titolo,de_nome from 7banner_campagne 
				inner join 7banner_clienti on cd_cliente=id_cliente ".(!$id ? " and fl_status=1 ": "")." 
				WHERE 1 $miocliente
				order by de_nome,de_titolo";
			$rs = $conn->query($sql) or die($conn->error.$sql);
			$ar = array();
			$ar[""]="--choose--";
			while($riga = $rs->fetch_array()) $ar[$riga['id_campagna']]=$riga['de_nome']." - ".$riga['de_titolo'];
			$cd_campagna = new optionlist("cd_campagna",($dati["cd_campagna"]),$ar);
			$cd_campagna->obbligatorio=1;
			$cd_campagna->label="'Campaign'";
			$objform->addControllo($cd_campagna);
			//------------------------------------------------

			//------------------------------------------------
			//combo posizioni
			$sql = "select id_posizione,de_posizione from 7banner_posizioni order by de_posizione";
			$rs = $conn->query($sql) or die($conn->error.$sql);
			$ar = array();
			$ar[""]="--choose--";
			while($riga = $rs->fetch_array()) $ar[$riga['id_posizione']]=$riga['de_posizione'];
			$cd_posizione = new optionlist("cd_posizione",($dati["cd_posizione"]),$ar);
			$cd_posizione->obbligatorio=1;
			$cd_posizione->label="'Position'";
			$objform->addControllo($cd_posizione);
			//------------------------------------------------


			$file = new fileupload('file');
			$file->obbligatorio=0;
			$file->label="'Banner file'";
			$objform->addControllo($file);
			$file->value="";	
			$thumbs = loadgallery($this->uploadDir,$id."_","div1","html",true);

			$id_banner = new hidden("id",$dati["id_banner"]);
			
			$op = new hidden("op",$action);

			//$submit = new submit("invia","salva");

			
			$html = str_replace("##STARTFORM##", $objform->startform(), $html);
			$html = str_replace("##idid##", $id ? $id : "n.a.", $html);
			$html = str_replace("##id##", $id_banner->gettag(), $html);
			$html = str_replace("##nu_maxclick##", $nu_maxclick->gettag(), $html);
			$html = str_replace("##nu_price##", $nu_price->gettag(), $html);
			$html = str_replace("##cd_campagna##", $cd_campagna->gettag(), $html);
			$html = str_replace("##cd_posizione##", $cd_posizione->gettag(), $html);
			$html = str_replace("##op##", $op->gettag(), $html);
			//$html = str_replace("##submit##", $submit->gettagimage($root."images/salva.gif"," Salva"), $html);
			$html = str_replace("##de_codicescript##", $de_codicescript->gettag(), $html);
			$html = str_replace("##fl_stato##", $fl_stato->gettag(), $html);
			$html = str_replace("##dt_giorno1##", $dt_giorno1->gettag(), $html);
			$html = str_replace("##dt_giorno2##", $dt_giorno2->gettag(), $html);
			$html = str_replace("##de_url##", $de_url->gettag(), $html);
			$html = str_replace("##nu_maxtot##", $nu_maxtot->gettag(), $html);
			$html = str_replace("##nu_maxday##", $nu_maxday->gettag(), $html);
			$html = str_replace("##nu_maxday_count##", number_format((float)$dati['nu_maxday_count'],0), $html);
			$html = str_replace("##dt_maxday_date##",TOdmy($dati["dt_maxday_date"]),$html);

			if($dati['nu_pageviews']>0) {
				$html = str_replace("##hideme##", "", $html);
				$html = str_replace("##nu_clickthrought##", number_format($dati['nu_clicks'] / $dati['nu_pageviews'] * 100,4)."%",$html); 
				$html = str_replace("##nu_pageviews##", number_format((float)$dati["nu_pageviews"],0,'.',','), $html);
				$html = str_replace("##nu_clicks##", number_format((float)$dati["nu_clicks"],0,'.',','), $html);
				if($dati["nu_clicks"]==0) {
					$html = str_replace("##CPC##", "0", $html);
				} else {
					$html = str_replace("##CPC##", number_format((float)$dati['nu_price']/$dati["nu_clicks"],2,'.',','), $html);
				}
				$html = str_replace("##CPM##", number_format((float)$dati["nu_price"]*1000/$dati["nu_pageviews"],2,'.',','), $html);

				$ts1 = strtotime($dati["dt_giorno1"]);
				$ts2 = strtotime($dati["dt_giorno2"]);
				$seconds_diff = $ts2 - $ts1;
				$days = floor( $seconds_diff / ( 24 * 3600 ) )  + 1;

				$html = str_replace("##DAYS##", number_format((float)$days,0,'.',','), $html);
				$html = str_replace("##CPD##", number_format((float)$dati["nu_price"]/$days,2,'.',','), $html);
			} else {
				$html = str_replace("##hideme##", "style='display:none'", $html);
				$html = str_replace("##nu_clickthrought##", "n.a.",$html); 
			}			

			$html = str_replace("##de_target##", $de_target->gettag(), $html);
			$html = str_replace("##de_nome##", $de_nome->gettag(), $html);
			$html = str_replace("##file##", $file->gettag(), $html);
			//$html = str_replace("##en_formato##", $en_formato->gettag(), $html);
			$html = str_replace("##max##", "Max ".$this->max_files." files", $html);
			$html = str_replace("##KB##", $this->maxKB ."Kb", $html);
			$html = str_replace("##X##", $this->maxX, $html);
			$html = str_replace("##Y##", $this->maxY, $html);
			$html = str_replace("##thumbs##", $thumbs, $html);

			if($id)$html = str_replace("##URLFORTRACKING##", WEBURL . "/adtrack.php?b=".$id, $html);
				else $html = str_replace("##URLFORTRACKING##", "<i>The URL will be available after saving.</i>", $html);

			$html = str_replace("##gestore##", $this->gestore, $html);
			$html = str_replace("##ENDFORM##", $objform->endform(), $html);
			$html = str_replace("##MONEY##", MONEY, $html);


		} else {
			$html = "0";
		}
		return $html;
	}


	function getDati($id) {
		return execute_row("SELECT * from ".$this->tbdb." where id_banner='{$id}'");
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
		if ($session->get("BANNER") && $this->check_cliente($arDati["id"])) {
	
			if ($arDati["id"]!="") {
				$id = $arDati["id"];
				/*
					Modifica
				*/
				$posAr = execute_row("select * from 7banner_posizioni where id_posizione='".$arDati["cd_posizione"]."'");

				$sql="UPDATE ".$this->tbdb." set
					dt_giorno1='##dt_giorno1##',
					dt_giorno2='##dt_giorno2##',
					de_nome='##de_nome##',
					nu_maxday='##nu_maxday##',
					nu_maxtot='##nu_maxtot##',
					nu_maxclick='##nu_maxclick##',
					de_codicescript='##de_codicescript##',
					cd_posizione='##cd_posizione##',fl_stato='##fl_stato##',de_target='##de_target##',
					de_url='##de_url##',
					cd_campagna='##cd_campagna##',
					nu_width='##nu_width##',
					nu_height='##nu_height##',
					nu_price='##nu_price##'
				where id_banner='##id_banner##'";
				$sql= str_replace("##dt_giorno1##",$arDati["dt_giorno1"],$sql);
				$sql= str_replace("##dt_giorno2##",$arDati["dt_giorno2"],$sql);
				$sql= str_replace("##de_nome##",$arDati["de_nome"],$sql);
				$sql= str_replace("##nu_maxclick##",$arDati["nu_maxclick"],$sql);
				$sql= str_replace("##nu_maxtot##",$arDati["nu_maxtot"],$sql);
				$sql= str_replace("##nu_maxday##",$arDati["nu_maxday"],$sql);
				$sql= str_replace("##de_codicescript##",$arDati["de_codicescript"],$sql);
				$sql= str_replace("##fl_stato##",$arDati["fl_stato"],$sql);
				$sql= str_replace("##cd_posizione##",$arDati["cd_posizione"],$sql);
				$sql= str_replace("##de_url##",$arDati["de_url"],$sql);
				$sql= str_replace("##de_target##",$arDati["de_target"],$sql);
				$sql= str_replace("##cd_campagna##",$arDati["cd_campagna"],$sql);
				$sql= str_replace("##id_banner##",$arDati["id"],$sql);
				$sql= str_replace("##nu_width##",$posAr["nu_width"],$sql);
				$sql= str_replace("##nu_height##",$posAr["nu_height"],$sql);
				$sql= str_replace("##nu_price##",$arDati["nu_price"],$sql);
				


				
				$conn->query($sql);
				$html= "ok|".$id;
			} else {
				/*
					Inserimento
				*/
				$posAr = execute_row("select * from 7banner_posizioni where id_posizione='".$arDati["cd_posizione"]."'");

				// -----------------------------------------------------------------
				// 22/04/2020 patch for Too many files, 1
				// allow usage of 0000-00-00 dates on mysqlserver > 5.6
				$ver = execute_row("SHOW VARIABLES LIKE 'version'");
				if($ver['Value'] >= '5.7') { $conn->query("SET sql_mode = '';"); }
				// -----------------------------------------------------------------

				$sql="INSERT into ".$this->tbdb." (dt_giorno1,dt_giorno2,de_nome,de_codicescript,de_url,cd_posizione,fl_stato,de_target,nu_maxday,nu_maxclick,nu_maxtot,cd_campagna,nu_width,nu_height,nu_price) values('##dt_giorno1##','##dt_giorno2##','##de_nome##','##de_codicescript##','##de_url##','##cd_posizione##','##fl_stato##','##de_target##','##nu_maxday##','##nu_maxclick##','##nu_maxtot##','##cd_campagna##','##nu_width##','##nu_height##','##nu_price##')";
				$sql= str_replace("##dt_giorno1##",$arDati["dt_giorno1"],$sql);
				$sql= str_replace("##dt_giorno2##",$arDati["dt_giorno2"],$sql);
				$sql= str_replace("##de_nome##",$arDati["de_nome"],$sql);
				$sql= str_replace("##nu_maxclick##",(integer)$arDati["nu_maxclick"],$sql);
				$sql= str_replace("##nu_maxtot##",(integer)$arDati["nu_maxtot"],$sql);
				$sql= str_replace("##nu_maxday##",(integer)$arDati["nu_maxday"],$sql);
				$sql= str_replace("##de_codicescript##",$arDati["de_codicescript"],$sql);
				$sql= str_replace("##fl_stato##",$arDati["fl_stato"],$sql);
				$sql= str_replace("##cd_posizione##",$arDati["cd_posizione"],$sql);
				$sql= str_replace("##de_url##",$arDati["de_url"],$sql);
				$sql= str_replace("##de_target##",$arDati["de_target"],$sql);
				$sql= str_replace("##cd_campagna##",$arDati["cd_campagna"],$sql);
				$sql= str_replace("##nu_width##",$posAr["nu_width"],$sql);
				$sql= str_replace("##nu_height##",$posAr["nu_height"],$sql);
				$sql= str_replace("##nu_price##",$arDati["nu_price"],$sql);

				$conn->query($sql);// or die(mysql_error().$sql);
				$id = $conn->insert_id;
				$html= "ok|".$id;

			}

			



			/* upload FILE */
			if(stristr($html,"ok|") && $files['file']['type']!="") {
				$htmltemp = uploadFile(
					$files,
					'file',
					$this->uploadDir.$id."_",
					array('gif','jpg','png','zip'),
					$this->maxX,
					$this->maxY,
					$this->maxKB,
					$this->max_files
				);

				if($htmltemp=="") {
					// ok, check for zip
					$thumbs = loadgallery($this->uploadDir,$id."_","","array");
					
					if(isset($thumbs[0][3]) && $thumbs[0][3] == "zip") {
						$dest =$this->uploadDir.$id."/";
						
						// https://github.com/alexcorvi/php-zip modified at line 1851
						$zip = new Zip();

						// check for malicious files
						$ar = $zip->fileList($thumbs[0][0]);
						foreach($ar as $f) {
							if(preg_match("/\.php$/i",$f)) {
								if (file_exists($thumbs[0][0])) unlink($thumbs[0][0]);
								if (file_exists($thumbs[0][0].'.info')) unlink($thumbs[0][0].'.info');
								return "-1|File ZIP blocked, it contains PHP code.";
							}
						}

						// ok, unzip
						mkdir($dest,0755);
						$zip->unzip_file($thumbs[0][0] );
						$zip->unzip_to($dest);

					}


				} else $html = $htmltemp;
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
		if ($session->get("BANNER") && $this->check_cliente($id)) {
			$sql="DELETE FROM ".$this->tbdb." where id_banner='$id'";
			//echo "$sql<br>";
			$conn->query($sql) or trigger_error($conn->error."sql='$sql'<br>");

			$sql="DELETE FROM ".$this->tbdb."_stats where cd_banner='$id'";
			$conn->query($sql) or trigger_error($conn->error."sql='$sql'<br>");

			// cancellazione files
			for ($i=0;$i<$this->max_files;$i++) deldbimg($this->uploadDir.$id."_".$i);
			rrmdir($this->uploadDir.$id);
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
		if ($session->get("BANNER")) {
			$p=$dati['gridcheck'];
			for ($i=0;$i<count($p);$i++) if($this->deleteItem( $p[$i] ) == "0") return "0";
			$html = "";
		} else {
			$html="0";		//il tuo profilo non ti consente di cancellare
		}
		return $html;
	}

	function getHtmlcomboBanner($id_banner, $id_campagna) {
		global $conn;
		//------------------------------------------------
		//combo provenienze
		$sql = "select id_banner, de_nome, de_posizione, cd_posizione from ".$this->tbdb." 
			inner join 7banner_posizioni on cd_posizione=id_posizione
			".($id_campagna ? " where cd_campagna = '$id_campagna' " : "" ) .
			"order by de_posizione";
		//echo $sql;
		$rs = $conn->query($sql) or trigger_error($conn->error."sql='$sql'<br>");
		$arFiltri = array("-999|$id_campagna"=>"All");
		while($riga = $rs->fetch_array()) {
			if ($riga['cd_posizione']=="") $riga['c']=0;
			$arFiltri[$riga['id_banner']]= $riga['de_nome']." (".$riga['de_posizione'].")" ;
		}
		//------------------------------------------------
		$out = "";
		foreach ($arFiltri as $k => $v) { $out.="<option value='{$k}' ".(($k."x"==$id_banner."x")?"selected":"").">{$v}</option>"; }
		return "<select onchange=\"$('form[name=dati]').submit();\" name='combobanner' id='combobanner'>{$out}</select><input type='hidden' name='combobannerreset' id='combobannerreset'>";
	}

	function getHtmlcombotipo($def="") {
		global $conn;
		//------------------------------------------------
		//combo provenienze
		$sql = "select cd_posizione, de_posizione,
			( CASE fl_stato WHEN 'A' THEN 'A' 
			WHEN 'L' THEN 'A'
			WHEN 'P' THEN 'A'
			WHEN 'S' THEN 'S'
			END ) AS stato,
			count(*) as c
			from ".$this->tbdb." LEFT OUTER JOIN 7banner_posizioni ON cd_posizione=id_posizione
			group by cd_posizione, stato order by stato,de_posizione";
		$rs = $conn->query($sql) or trigger_error($conn->error);
		$arFiltri = array("-999"=>"All","-999|T"=>"------- Serving -------");
		$old = "A";
		while($riga = $rs->fetch_array()) {
			if ($riga['cd_posizione']=="") $riga['c']=0;
			if($riga['stato']=="S" && $old!="S") {
				$arFiltri["-999|X"]= "------- Ended -------"; $old = $riga['stato']; 
			}
			$arFiltri[$riga['cd_posizione']."|".$riga['stato']]= ($riga['cd_posizione'] ? "[ ".$riga['cd_posizione']." ] · ".$riga['de_posizione']." (".$riga['c'].")":"no position") ;
		}
		//------------------------------------------------
		$out = "";
		foreach ($arFiltri as $k => $v) { $out.="<option value='{$k}' ".(($k."x"==$def."x")?"selected":"").">{$v}</option>"; }
		return "<select onchange='aggiornaGriglia()' name='combotipo' id='combotipo'>{$out}</select><input type='hidden' name='combotiporeset' id='combotiporeset'>";
	}

	function giorni($q,$startdate) {
		$s = "";
		$formato = "m/d/Y";
		if(DATEFORMAT=="dd/mm/yyyy" || DATEFORMAT=="dd/mm/yy") $formato = "d/m/Y";
		//date($formato,strtotime($gg[$q]))

		$enddate = DateAdd($q,$startdate, $formato );
		for($i=0;$i<$q;$i++) $s.=($s ? "," : "") . "'".DateAdd($i,$startdate, $formato)."'";
		return $s;
	}

	function serie($ids,$q,$startdate) {

		$serie = "";
		global $conn;

		$rs2=$conn->query($sql = "select id_day,sum(nu_pageviews) as nu_pageviews,sum(nu_click) as nu_click from ".$this->tbdb."_stats where cd_banner in(".$ids.") and id_day>='$startdate' group by id_day order by id_day asc limit 0,".$q);
		//echo $sql;
		$v=array(); // view
		$c=array(); // click
		while($r2=$rs2->fetch_array()) {
			//print_r($r2);
			for($i=0;$i<$q;$i++) {
				$data = DateAdd($i,$startdate,"Y-m-d");
				if($data==$r2['id_day']) {
					$v[$data]=$r2['nu_pageviews'];
					$c[$data]=$r2['nu_click'];
				} else {
					if(!isset($v[$data])) $v[$data]="0";
					if(!isset($c[$data])) $c[$data]="0";
				}
			}
			
		}
		$serie.= ($serie ? ", " : "") . "{ name :'View', type: 'spline', yAxis: 1, data: [";
		for($i=0;$i<$q;$i++) { $data = DateAdd($i,$startdate,"Y-m-d"); if(isset($v[$data])) $serie.= ($i==0 ? "": ",") . $v[$data]; }
		$serie.="] }";


		$serie.= ($serie ? ", " : "") . "{ name :'Click', type: 'column', data: [";
		for($i=0;$i<$q;$i++) { $data = DateAdd($i,$startdate,"Y-m-d"); if(isset($c[$data])) $serie.= ($i==0 ? "": ",") . $c[$data]; }
		$serie.="] }";

		return $serie;
	}


	function setStato($id,$stat) {
		global $conn;
		$conn->query("update ".$this->tbdb." set fl_stato='".$stat."' where id_banner='".$id."'" );
	}




}

?>