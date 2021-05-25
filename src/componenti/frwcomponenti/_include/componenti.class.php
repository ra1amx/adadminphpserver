<?php

class Componenti {

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


	function __construct ($tbdb="frw_componenti",$ps=20,$oby="nome",$omode="asc",$start=0) {
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
		$this->linkaggiungi_label = "Installa un nuovo componente";

		$this->linkmodifica = "$this->gestore?op=modifica&id=##id##";
		$this->linkmodifica_label = "modifica";

		$this->linkelimina = "javascript:confermaDelete('##id##');";
		$this->linkelimina_label = "elimina";

		checkAbilitazione("FRWCOMPONENTI","SETTA_SOLO_SE_ESISTE");



	}

	/*
		mostra l'elenco dei componenti.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'elenco in html.
	*/
	function elenco() {
		global $session;
		$html = "";
		if ($session->get("FRWCOMPONENTI")!="") {
			$t=new grid($this->tbdb,$this->start, $this->ps, $this->oby, $this->omode);
			$t->functionhtml = "myhtmlspecialchars";
			$t->debug=false;

			//campi da visualizzare
			$t->campi="id,nome,label,urlcomponente";

			//titoli dei campi da visualizzare
			$t->titoli="ID,Nome (slug),Nome nel menù,Url Linkata";

			//id per fare i link
			$t->chiave="id";

			//query per estrarre i dati
			$t->query="SELECT id, nome, CONCAT('<a class=\"linkmenu ',nome,'\">',label,'</a>') as label, urlcomponente from frw_componenti";

			$t->addComando($this->linkmodifica,$this->linkmodifica_label);
			$t->addComando($this->linkelimina,$this->linkelimina_label);
			$t->addComando("javascript:abilitaComponente('##id##');","assegna","assegna i permessi");

			$html = $t->show();

		} else {
			$html = "0";
		}
		return $html;
	}

	function profila_service($idc) {
		// metodo aggiunto per utilizzare la distribuzione dei permessi in maniera
		// applicativa senza utilizzare un utente reale e i suoi permessi.
		$html = $this->profila($idc,"NOSESSION");
		return $html!="0" ? "ok" : "ko";
	}

	function profila($idc, $check="YES") {
		global $session,$conn;
		/*
			da idcomponente recupero le funzionalita' e i moduli in cui e' il componente.
			elimino da frw_ute_fun tutte le funzionalita' recuperate.
			recupero i profili che hanno le funzionalita' del componente.
			per ogni profilo trovato:
				per ogni utente con questo profilo:
					per ogni modulo in cui e' utilizzato il componente:
						aggiungo le funzionalita' del componente.
					end per
				end per
			end per
		*/
		if ($check!="NOSESSION" && $session->get("FRWCOMPONENTI")=="") return "0";

		$html = "";

		$html.="Pulizia tabelle... ";
		$sql = "delete FROM `frw_com_mod` WHERE idmodulo not in (".$this->getElencoId("select id from frw_moduli").")";
		$conn->query($sql) or (trigger_error($conn->error."<br>sql=\"{$sql}\""));
		$sql = "DELETE FROM `frw_com_mod` WHERE idcomponente not in (".$this->getElencoId("Select id from frw_componenti").")";
		$conn->query($sql) or (trigger_error($conn->error."<br>sql=\"{$sql}\""));
		$sql = "delete FROM `frw_funzionalita` WHERE idcomponente not in (".$this->getElencoId("select id from frw_componenti").")";
		$conn->query($sql) or (trigger_error($conn->error."<br>sql=\"{$sql}\""));
		$sql = "delete FROM `frw_profili_funzionalita` where cd_funzionalita not in (".$this->getElencoId("select id from frw_funzionalita").")";
		$conn->query($sql) or (trigger_error($conn->error."<br>sql=\"{$sql}\""));
		$sql = "DELETE FROM `frw_ute_fun` WHERE idfunzionalita not in (".$this->getElencoId("select id from frw_funzionalita").")";
		$conn->query($sql) or (trigger_error($conn->error."<br>sql=\"{$sql}\""));
		$sql = "delete FROM `frw_ute_fun` WHERE idutente not in (".$this->getElencoId("select id from frw_utenti").")";
		$conn->query($sql) or (trigger_error($conn->error."<br>sql=\"{$sql}\""));
		$html.="ok<br><br>";


		$idfunzionalita = $this->getElencoId("select id from frw_funzionalita where idcomponente={$idc}");
		if ($idfunzionalita==""){
			return "1";
			//il componente non ha funzionalità...
		}
		$html.="funzionalit&agrave; trovate: $idfunzionalita<br>";

		$idmoduli = $this->getElencoId("select distinct idmodulo from frw_com_mod where idcomponente={$idc}");
		if ($idmoduli==""){
			return "2";
			//il componente non è installato in nessun modulo...
		}
		$html.="moduli trovati: $idmoduli<br>";

		$sql = "delete from frw_ute_fun where idfunzionalita in ($idfunzionalita)";
		//echo "pulizia: $sql<hr>";
		$conn->query($sql) or (trigger_error($conn->error."<br>sql=\"{$sql}\""));

		$idprofili = $this->getElencoId("select distinct cd_profilo from frw_profili_funzionalita where cd_funzionalita in ($idfunzionalita)");
		if ($idprofili==""){
			/*
				qua bisogna cancellare tutti gli ute_fun che hanno queste funzionalita
			*/
			return "3";
			//il componente non è associato a nessun profilo...
		}
		$html.="profili trovati: $idprofili<br>";

		$arFunz = explode(",",$idfunzionalita);
		$arModu = explode(",",$idmoduli);
		$arProf = explode(",",$idprofili);
		$qi = 0;
		$qs = 0;
		$qd = 0;

		// ciclo su profili NON specificati per rimuovere eventuali funzionalità assegnate
		$sql = "select id,cd_profilo from frw_utenti where cd_profilo NOT IN ($idprofili)";
		$rs = $conn->query($sql) or (trigger_error($conn->error."<br>sql=\"{$sql}\""));
		while ($r = $rs->fetch_array()) {
			for ($j=0;$j<count($arModu);$j++) {
				for ($k=0;$k<count($arFunz);$k++) {
					$sql = "delete from frw_ute_fun where idutente={$r['id']} and idfunzionalita={$arFunz[$k]} and idmodulo={$arModu[$j]}";
					$conn->query($sql) or (trigger_error($conn->error."<br>sql=\"{$sql}\""));
					$qd++;
				}
			}
		}
		$rs->free();

		// ciclo su utenti per assegnare fnzionalita'
		for ($i=0;$i<count($arProf);$i++) {
			$sql = "select id,cd_profilo from frw_utenti where cd_profilo in ($idprofili)";
			$rs = $conn->query($sql) or (trigger_error($conn->error."<br>sql=\"{$sql}\""));
			$qs++;
			while ($r = $rs->fetch_array()) {
				for ($j=0;$j<count($arModu);$j++) {
					for ($k=0;$k<count($arFunz);$k++) {
						if ($r['cd_profilo']==$arProf[$i]) {
							$qs++;
							if ($this->checkExistProfiloConFunzionalita($arProf[$i],$arFunz[$k])) {
								$sql = "insert into frw_ute_fun (idutente,idfunzionalita,idmodulo) values ( {$r['id']},{$arFunz[$k]},{$arModu[$j]})";
								$conn->query($sql);
								$errno = $conn->errno;
								if ($errno!=1062 && $errno>0) trigger_error($conn->error."<br>sql=\"{$sql}\"");
								$qi++;
							}
						}
					}
				}
			}
			$rs->free();
		}
		$html.="query select eseguite: $qs<br>";
		$html.="query insert eseguite: $qi<br>";
		$html.="query delete eseguite: $qd<br>";
		return $html;
	}

	/*
		estrae una stringa con i record sepatati da una virgola.
		prende il primo item dei record estratti.
	*/
	function getElencoId($sql) {
		return concatenaId($sql);
	}

	function tendinaPosizione($selez,$index) {
		$o="";
		for ($i=0;$i<100;$i++){
			$o.="<option value='$i' ";
			if ($i==$selez) $o.="selected";
			$o.=">$i</option>";
		}
		$o = "<select name='tp_$index' onChange=\"document.dati.elements['idmoduli[]'][$index].value = document.dati.elements['idmoduli[]'][$index].value.substr(0,document.dati.elements['idmoduli[]'][$index].value.indexOf(',')) + ','+this.value;\">$o</select>";
		return $o;
	}

	function getListaCheckboxModuli($idcomp,$strValoriSelezionati="") {
		/*
			ritorna l'html per le checkbox della scelta:
			genere l'elenco html <input type="checkbox" name="..." value="...">....
			utilizzando i dati in ingresso.
		*/
		global $conn;
		$sql = "select id as idmoduli,nome from frw_moduli";
		if ($sql=="") return "";
		$rs = $conn->query ($sql);
		$html="";
		$c = 0;
		while ($r=$rs->fetch_array()) {
			$posiz="0";
			$posizSql = "select posizione from frw_com_mod where idcomponente='$idcomp' and idmodulo='{$r['idmoduli']}'";
			$posizRs = $conn->query($posizSql);
			if ($posizRs->num_rows>0) {
				$posizR = $posizRs->fetch_array();
				$posiz= $posizR["posizione"];
			}
			$posizRs->free();
			$html.="<label for='chk{$c}'><input type=\"checkbox\" id='chk{$c}' name=\"idmoduli[]\" value=\"{$r['idmoduli']},$posiz\"";
			if (stristr(",".$strValoriSelezionati.",",",".$r["idmoduli"].",")) $html.=" checked";

			$html.="> {$r['nome']}</label>, alla posizione ".$this->tendinaPosizione($posiz,$c);
			$html.="\n\r";
			$c++;
		}
		return $html;
	}

	function getListaCheckboxProfili($strValoriSelezionati="") {
		global $conn;
		$sql = "select id_profilo as idprofili,de_label from frw_profili";
		if ($sql=="") return "";
		$rs = $conn->query ($sql) or trigger_error($conn->error."<br>sql='$sql'");
		$html="";
		$c=0;
		while ($r=$rs->fetch_array()) {
			$html.="<label for='chk{$c}'><input id='chk{$c}' type=\"checkbox\" name=\"idprofili[]\" value=\"{$r['idprofili']}\"";
			if (stristr(",".$strValoriSelezionati.",",",".$r["idprofili"].",")) $html.=" checked";
			$html.="> {$r['de_label']}";
			$html.="</label>\n\r";
			$c++;
		}
		return $html;
	}

	/*
		mostra il dettaglio del componente.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'html.
	*/
	function getDettaglio($id="") {
		global $session;

		if ($session->get("FRWCOMPONENTI")!="") {
			if ($id!="") {
				/*
					modifica
				*/
				$html = loadTemplateAndParse("template/dettaglio.html");
				$dati = $this->getDatiComponente($id);

				$html = str_replace("##id##", $dati["id"], $html);
				$html = str_replace("##labelid##", $dati["id"], $html);
				$html = str_replace("##action##", "modificaStep2", $html);
				$html = str_replace("##nome##", htmlspecialchars($dati["nome"]), $html);
				$html = str_replace("##label##", htmlspecialchars($dati["label"]), $html);
				$html = str_replace("##urlcomponente##", htmlspecialchars($dati["urlcomponente"]), $html);
				$html = str_replace("##descrizione##", htmlspecialchars($dati["descrizione"]), $html);
				$html = str_replace("##gestore##", $this->gestore, $html);
				$modulichecckati = $this->getElencoId("select idmodulo from frw_com_mod where idcomponente={$id}");

				$moduli = $this->getListaCheckboxModuli( $dati["id"],$modulichecckati);

				$html = str_replace("##elencomoduli##", nl2br($moduli), $html);

				$sql = "select id, nome, label from frw_funzionalita where idcomponente='{$id}'";


				$t=new grid("frw_funzionalita",0, 40, "nome", "asc");
				//campi da visualizzare
				$t->campi="id,nome,label";
				$t->flagOrdinatori=false;
				//titoli dei campi da visualizzare
				$t->titoli="ID,Nome (slug),Label/Valore";
				//id per fare i link
				$t->chiave="id";
				//query per estrarre i dati
				$t->query="SELECT id, nome, label from frw_funzionalita where idcomponente='{$id}'";
				$t->addComando("{$this->gestore}?op=modificaf&id=##id##","modifica");
				$t->addComando("javascript:eliminaf(##id##)","elimina");

				$funzionalita = "<div class='panel'><a href='{$this->gestore}?op=aggiungif&id={$id}' class='aggiungi'>aggiungi funzionalit&agrave;</a></div>". $t->show();

				$html = str_replace("##elencofunzionalita##", $funzionalita, $html);

				/*$profili = getListaCheckboxForm(
					"idprofili",
					$this->getElencoId("select cd_profilo from frw_profili_funzionalita join frw_com_mod on cd_modulo=idmodulo where idcomponente={$id}") ,
					"de_label",
					"select id_profilo as idprofili,de_label from frw_profili"
				);
				$html = str_replace("##elencoprofili##", nl2br($profili), $html);*/
			} else {
				/*
					Inserimento
				*/
				$html = loadTemplateAndParse("template/dettaglio.html");
				$html = str_replace("##id##", "", $html);
				$html = str_replace("##labelid##", "<i>non ancora assegnato</i>", $html);
				$html = str_replace("##action##", "aggiungiStep2", $html);
				$html = str_replace("##nome##", "", $html);
				$html = str_replace("##label##", "", $html);
				$html = str_replace("##urlcomponente##", "componenti/", $html);
				$html = str_replace("##descrizione##", "", $html);
				$html = str_replace("##gestore##", $this->gestore, $html);
				$moduli = $this->getListaCheckboxModuli( "","");
				$html = str_replace("##elencomoduli##", nl2br($moduli), $html);
				$funzionalita = "<i>Dopo l'inserimento potrai aggiungere funzionalit&agrave;</i>";
				$html = str_replace("##elencofunzionalita##", $funzionalita, $html);

			}

		} else {
			$html = returnmsg("Non sei autorizzato.");
		}
		return $html;
	}

	function getDatiFunzionalita($id) {
		$sql = "SELECT * from frw_funzionalita where id='{$id}'";
		return execute_row($sql);
	}

	function getDatiComponente($id) {
		$sql = "SELECT * from frw_componenti where id='{$id}'";
		return execute_row($sql);
	}

	/*
		mostra il dettaglio della funzionalità.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'html.
	*/
	function getDettaglioF($id="",$idcomponente="") {
		global $session;

		if ($session->get("FRWCOMPONENTI")!="") {
			if ($id!="") {
				/*
					modifica
				*/
				$html = loadTemplateAndParse("template/dettagliof.html");
				$dati = $this->getDatiFunzionalita($id);
				$datiC = $this->getDatiComponente($dati["idcomponente"]);
				$html = str_replace("##componente##", "{$datiC['nome']} (id n.{$datiC['id']})", $html);
				$html = str_replace("##idcomponente##", "{$datiC['id']}", $html);

				$html = str_replace("##id##", $dati["id"], $html);
				$html = str_replace("##labelid##", $dati["id"], $html);
				$html = str_replace("##action##", "modificafStep2", $html);
				$html = str_replace("##nome##", htmlspecialchars($dati["nome"]), $html);
				$html = str_replace("##label##", htmlspecialchars($dati["label"]), $html);
				$html = str_replace("##descrizione##", htmlspecialchars($dati["descrizione"]), $html);
				$html = str_replace("##gestore##", $this->gestore, $html);

				$modulichecckati = $this->getElencoId("select idmodulo from frw_com_mod where idcomponente={$datiC['id']}");
				$html = str_replace("##modulichecckati##", $modulichecckati, $html);

				$profilichecckati = $this->getElencoId("select cd_profilo from frw_profili_funzionalita where cd_funzionalita={$id}");

				$profili = $this->getListaCheckboxProfili($profilichecckati);

				$html = str_replace("##elencoprofili##", nl2br($profili), $html);

			} else {
				/*
					Inserimento
				*/


				$html = loadTemplateAndParse("template/dettagliof.html");
				$datiC = $this->getDatiComponente($idcomponente);
				$html = str_replace("##componente##", "{$datiC['nome']} (id n.{$datiC['id']})<br>{$datiC['label']}", $html);
				$html = str_replace("##idcomponente##", "{$datiC['id']}", $html);
				$modulichecckati = $this->getElencoId("select idmodulo from frw_com_mod where idcomponente={$datiC['id']}");
				$html = str_replace("##modulichecckati##", $modulichecckati, $html);

				$html = str_replace("##id##", "", $html);
				$html = str_replace("##labelid##", "<i>non ancora assegnato</i>", $html);
				$html = str_replace("##action##", "aggiungifStep2", $html);
				$html = str_replace("##nome##", "", $html);
				$html = str_replace("##label##", "", $html);
				$html = str_replace("##descrizione##", "", $html);
				$html = str_replace("##gestore##", $this->gestore, $html);
				$profili = $this->getListaCheckboxProfili( "","");
				$html = str_replace("##elencoprofili##", nl2br($profili), $html);
			}

		} else {
			$html = returnmsg("Non sei autorizzato.");
		}
		return $html;
	}




	function checkExistIn($nometabella,$nomecampo,$valore,$giaesistente,$campoidunivoco) {
		global $conn;
		$sql = "select $nomecampo from $nometabella where $nomecampo='$valore' and $campoidunivoco<>'$giaesistente'";
		$rs = $conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));
		if ($rs->num_rows>0) $risultato = true; else $risultato=false;
		$rs->free();
		return $risultato;
	}

	function checkExistProfiloConFunzionalita($p,$f) {
		global $conn;
		$sql = "select cd_profilo from frw_profili_funzionalita where cd_profilo='$p' and cd_funzionalita='$f'";
		$rs = $conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));
		if ($rs->num_rows>0) $risultato = true; else $risultato=false;
		$rs->free();
		//echo "profilo $p ha $f = $risultato<hr>";
		return $risultato;
	}

	function addComMod($idcomponente,$arModuli) {
		/*
			aggiunge gli
		*/
		global $conn;
		$sql ="delete from frw_com_mod where idcomponente='{$idcomponente}'";
		$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));
		//echo "{$sql}<br>";
		for ($i=0;$i<count($arModuli);$i++) {
			$idmodulo = substr($arModuli[$i],0,strpos($arModuli[$i],","));
			$posizione =substr($arModuli[$i],strlen($idmodulo)+1);
			$sql ="insert into frw_com_mod (idcomponente,idmodulo,posizione) values ($idcomponente,$idmodulo,$posizione)";
			$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));
			//echo "{$sql}<br>";

		}
	}

	function addFunPro($idfunzionalita,$arProfili,$modulichecckati) {
		/*
			aggiunge gli
		*/
		global $conn;
		$sql ="delete from frw_profili_funzionalita where cd_funzionalita='{$idfunzionalita}'";
		$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));
		//echo "{$sql}<br>";
		$idmodulo = explode(",",$modulichecckati);
		if (count($idmodulo)==0) return "1";
		for ($j=0;$j<count($idmodulo);$j++) {
			for ($i=0;$i<count($arProfili);$i++) {
				$sql ="insert into frw_profili_funzionalita (cd_profilo,cd_modulo,cd_funzionalita) values ({$arProfili[$i]},{$idmodulo[$j]},$idfunzionalita)";
				$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));
				//echo "{$sql}<br>";
			}
		}
		return "";
	}


	function updateAndInsert($arDati) {
		// in:
		// arDati--> array POST del form
		// risultato:
		//	"" --> ok
		//	"1" --> nome gia' utilizzato da un altro componente
		//  "0" --> il tuo profilo non ti consente l'inserimento/modifica

		global $session,$conn;
		if ($session->get("FRWCOMPONENTI")!="") {
			if ($arDati["id"]!="") {
				/*
					Modifica
				*/
				if (!$this->checkExistIn("frw_componenti","nome",$arDati["nome"],$arDati["id"],"id")){
					$sql="UPDATE frw_componenti set nome='##nome##',label='##label##',descrizione='##descrizione##',urlcomponente='##urlcomponente##' where id='##id##'";
					$sql= str_replace("##nome##",$arDati["nome"],$sql);
					$sql= str_replace("##label##",$arDati["label"],$sql);
					$sql= str_replace("##descrizione##",$arDati["descrizione"],$sql);
					$sql= str_replace("##urlcomponente##",$arDati["urlcomponente"],$sql);
					$sql= str_replace("##id##",$arDati["id"],$sql);

					$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));

					if (!isset($arDati["idmoduli"])) $arDati["idmoduli"] = array();
					$this->addComMod(
						$arDati["id"],
						$arDati["idmoduli"]
					);

					$html= "";



				} else {
					$html="1";	//nome già utilizzato
				}
			} else {
				/*
					Inserimento
				*/
				if (!$this->checkExistIn("frw_componenti","nome",$arDati["nome"],$arDati["id"],"id")){
					$sql="INSERT into frw_componenti (nome,label,descrizione,urlcomponente) values('##nome##','##label##','##descrizione##','##urlcomponente##')";
					$sql= str_replace("##nome##",$arDati["nome"],$sql);
					$sql= str_replace("##label##",$arDati["label"],$sql);
					$sql= str_replace("##descrizione##",$arDati["descrizione"],$sql);
					$sql= str_replace("##urlcomponente##",$arDati["urlcomponente"],$sql);

					$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));
					$id_com = $conn->insert_id;

					if (!isset($arDati["idmoduli"])) $arDati["idmoduli"] = array();
					$this->addComMod(
						$id_com ,
						$arDati["idmoduli"]
					);


					if(getVarSetting("CREA_FUNZIONI_AUTOMATICAMENTE")=="1") {
						$this->updateAndInsertF(
							array ( "id" => "",
								"op" => "aggiungifStep2",
								"nome" => $arDati["nome"],
								"label" => $arDati["nome"],
								"descrizione" => $arDati["nome"],
								"idcomponente" => $id_com,
								"modulichecckati" => $this->getElencoId("select idmodulo from frw_com_mod where idcomponente={$id_com}"),
								"idprofili" => array ( "0" => "20", "1" => "999999" )
							)
						);

					}

					$html= "";

				} else {
					$html="1";	//nome già utilizzato
				}

			}

		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}


	function updateAndInsertF($arDati) {
		// in:
		// arDati--> array POST del form
		// risultato:
		//	"" --> ok
		//	"1" --> nome gia' utilizzato da un altro componente
		//  "0" --> il tuo profilo non ti consente l'inserimento/modifica

		global $session,$root,$conn;
		if ($session->get("FRWCOMPONENTI")!="") {
			if ($arDati["modulichecckati"]<>"") {
				if ($arDati["id"]!="") {
					/*
						Modifica
					*/
					$sql="UPDATE frw_funzionalita set idcomponente='##idcomponente##',label='##label##',descrizione='##descrizione##',nome='##nome##' where id='##id##'";
					$sql= str_replace("##nome##",$arDati["nome"],$sql);
					$sql= str_replace("##label##",$arDati["label"],$sql);
					$sql= str_replace("##descrizione##",$arDati["descrizione"],$sql);
					$sql= str_replace("##idcomponente##",$arDati["idcomponente"],$sql);
					$sql= str_replace("##id##",$arDati["id"],$sql);

					$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));

					if (!isset($arDati["idprofili"])) $arDati["idprofili"] = array();
					$this->addFunPro(
						$arDati["id"] ,
						$arDati["idprofili"],
						$arDati["modulichecckati"]
					);
					$session->register("backbutton","<br><a href=\"{$this->gestore}?op=modifica&id={$arDati['idcomponente']}\"><img src=\"{$root}images/back.gif\" border=\"0\"> torna</a>");

					$html= "";
				} else {
					/*
						Inserimento
					*/
					$sql="INSERT into frw_funzionalita (nome,label,descrizione,idcomponente) values('##nome##','##label##','##descrizione##','##idcomponente##')";
					$sql= str_replace("##nome##",$arDati["nome"],$sql);
					$sql= str_replace("##label##",$arDati["label"],$sql);
					$sql= str_replace("##descrizione##",$arDati["descrizione"],$sql);
					$sql= str_replace("##idcomponente##",$arDati["idcomponente"],$sql);

					$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));

					if (!isset($arDati["idprofili"])) $arDati["idprofili"] = array();
					$this->addFunPro(
						$conn->insert_id ,
						$arDati["idprofili"],
						$arDati["modulichecckati"]
					);
					$html="";
					$session->register("backbutton","<br><a href=\"{$this->gestore}?op=modifica&id={$arDati['idcomponente']}\"><img src=\"{$root}images/back.gif\" border=\"0\"> torna</a>");

				}
			} else {
				return "1"; //il componente a cui questa funzionalità appartiene non è associato ad alcun modulo
			}
		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}

	function deleteF($elencoIdF) {
		/*
			elimina funzionalità:
			in ingresso può ricevere anche un elenco di id separati da virgola.
		*/
		global $session,$root,$conn;
		if ($session->get("FRWCOMPONENTI")!="") {
			$sql="DELETE FROM frw_funzionalita where id in ($elencoIdF)";
			//echo "$sql<br>";
			$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));


			$sql="DELETE FROM frw_profili_funzionalita where cd_funzionalita in ($elencoIdF)";
			//echo "$sql<br>";
			$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));

			$sql="DELETE FROM frw_ute_fun where idfunzionalita in ($elencoIdF)";
			//echo "$sql<br>";
			$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));

			$session->register("backbutton","<br><a href=\"javascript:history.back()\"><img src=\"{$root}images/back.gif\" border=\"0\"> torna</a>");

			return "";
		}
		return "0";
	}

	function deleteC($id) {
		// in:
		// id --> id componente da cancellare
		// risultato:
		//	"" --> ok
		//  "0" -->il tuo profilo non ti consente la cancellazione

		global $session,$conn;
		if ($session->get("FRWCOMPONENTI")!="") {
			$sql="DELETE FROM frw_componenti where id='$id'";
			//echo "$sql<br>";
			$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));
			$sql="DELETE FROM frw_com_mod where idcomponente='$id'";
			//echo "$sql<br>";
			$conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));
			$sql="SELECT id from frw_funzionalita where idcomponente='$id'";
			//echo "$sql<br>";
			$rs= $conn->query($sql) or (trigger_error($conn->error."<br>sql='{$sql}'"));
			$elencoIdF="";
			while($r=$rs->fetch_array()) {
				if ($elencoIdF!="")$elencoIdF.=",";
				$elencoIdF.=$r["id"];
			}
			$rs->free();
			if ($elencoIdF!="") {
				$this->deleteF($elencoIdF);
			}
			$html = "";
		} else {
			$html="0";		//il tuo profilo non ti consente di cancellare
		}
		return $html;

	}
}

?>