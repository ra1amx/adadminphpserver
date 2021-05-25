<?php

class Grid
{
	var $tablename;			//nome della tabella principale che visualizzo
	var $campi;				//elenco campi che estraggo separati da virgole, es: de_nome,de_cognome
	var $titoli;			//elenco label dei campi che estraggo separati da virgole, es.: Nome,Cognome
	var $chiave;			//nome del campo che e' chiave, utilizzato per linkare i comandi
	var $query;				//sql select che estrae i dati
	var $debug;
	var $comandi;
	var $gestore;
	var $scegliDaInsieme;	//array che contiene per un dato campo dei possibili match per
							//mostrare ad esempio immagini al posto dei valori...
	var $campiMeta;			//array che contiene i campi date/email da visualizzare, la visualizzazione
							//in questo modo converte i campi nel formato dd/mm/yyyy
	var $evidenzia;
	var $flagOrdinatori;	//se false non metto mai i link per ordinare
	var $istanceName;
	var $parametriDaPssare;

	var $letterSelectField;	//per gestire i filtri A B C D ...
	var $gridSelectedLetter;
	
	var $checkboxForm;		//true per mettere le checkbos collegate all'id
	var $checkboxFormName;
	var $checkboxFormTitolo;	//se c'è è l'intestazione della colonna delle checkbox
	var $checkboxFormChekcboxAttributes;	//codice aggiuntivo che mette nelle chechbox.
	var $checkboxFormAction;
	var $mostraRecordTotali;
	var $paginatori;	//se false non metto mai la paginazione
	var $alternaColoriRighe;	//se true alterna i colori delle righe
	var $arFormattazioneCondizionale;	//se settato permette di controllare la formattazione condizionale
										//con stili ad hoc a seconda dei valori, esempio:
										//	$t->arFormattazioneCondizionale=array(
										//		"de_stato" => array("Aperto","rigarossa")
										//	);
	var $arFormattazioneTD;	//se settato permette di controllare la formattazione delle celle tramite classi css
							//$t->arFormattazioneTD=array("giorni"=>"numero", "nomecampo"=>"nomeclassecss");

	var $functionhtml;	// valori consentiti: htmlspecialchars, htmlentities, myhtmlspecialchars
						// è la codifica che applica a tutti i td di stampa dei dati.
						//
	var $mouseeffect;	// se true mette il codice per l'onmouseover/onmouseout, altrimenti no.

	function __construct($table,$start=0,$pagesize=40,$orderby="id",$ordermode="asc",$selectedId="0",$istanceName="",$selectedLetter="")
	{
		global $session;
		$this->flagOrdinatori="on"; // se on mostra i link per ordinare la lista
		$this->tablename=$table;
		$this->istanceName=$istanceName;
		$this->alternaColoriRighe=true;
		$this->functionhtml="htmlspecialchars";
		$this->mouseeffect=true;
		$this->debug=false;

		if ($istanceName=="") {
			$this->istanceName=$this->tablename;
			$istanceName = $this->tablename;
		}
		$this->parametriDaPssare="";
		$this->letterSelectField="";

		$this->ABCDmenu="false";
		$this->gridSelectedLetter=$selectedLetter;

		$session->register($istanceName."gridStart",$start);
		$session->register($istanceName."gridPageSize",$pagesize);
		$session->register($istanceName."gridOrderBy",$orderby);
		$session->register($istanceName."gridOrderMode",$ordermode);
		$session->register($istanceName."gridSelectedId",$selectedId);
		$session->register($istanceName."gridSelectedLetter",$selectedLetter);

		$this->tdcssevidenzia="evidenziacelle";
		$this->campi="";
		$this->titoli="";
		$this->chiave="";
		$this->query="";
		$this->comandi=array();
		$this->scegliDaInsieme=array();
		$this->campiMeta=array();
		$this->gestore=$_SERVER["PHP_SELF"];
		$this->evidenzia=$selectedId;
		$this->mostraRecordTotali=false;	//se metti true mostra quanti sono i record totali

		$this->checkboxForm=false;
		$this->checkboxFormTitolo = "";
		$this->checkboxFormAction="";
		$this->checkboxFormName="";
		$this->checkboxFormChekcboxAttributes="";
	}

	function addComando($link,$label,$titolo="") {
		$i = count($this->comandi);
		$a = array();
		$a["link"]=$link;
		$a["label"]=$label;
		$a["title"]=$titolo;
		$this->comandi = $this->comandi + array($i => $a);
	}

	function addScegliDaInsieme($campoMatch,$arrayscelte) {
		$this->scegliDaInsieme = $this->scegliDaInsieme + array($campoMatch => $arrayscelte);
	}

	function addCampi($campoMatch,$formato) {
		// i formati supportati sono:
		//    dd/mm/yyyy , dd/mm/yyyy hh:ii , email, url
		$this->campiMeta = $this->campiMeta + array($campoMatch => $formato);
	}

	function addCampiDate($campoMatch,$formato="dd/mm/yyyy") {
		// i formati supportati sono:
		//    dd/mm/yyyy
		//    dd/mm/yyyy hh:ii
		$this->campiMeta = $this->campiMeta + array($campoMatch => $formato);
	}


	function getABCDmenu($sel="") {
		$s="";
		for ($i=65;$i<=90;$i++) {
			if ($sel==chr($i)) {
				$s.="<a class=\"grid_lettera_selezionata\" href=\"{$this->gestore}?gridSelectedLetter=".chr($i)."&gridStart=0{$this->parametriDaPssare}\">".chr($i)."</a> ";
			} else {
				$s.="<a class=\"grid_lettera_normale\" href=\"{$this->gestore}?gridSelectedLetter=".chr($i)."&gridStart=0{$this->parametriDaPssare}\">".chr($i)."</a> ";
			}
		}
		if ($sel=="") {
			$s.="<a class=\"grid_lettera_selezionata\" href=\"{$this->gestore}?gridSelectedLetter=&gridStart=0{$this->parametriDaPssare}\">all</a> ";
		} else {
			$s.="<a class=\"grid_lettera_normale\" href=\"{$this->gestore}?gridSelectedLetter=&gridStart=0{$this->parametriDaPssare}\">all</a> ";
		}
		return "<div class='grid_lettere_contenitore'>$s</div>";
	}

	function show() {
		global $session,$root,$conn;
		//rimappo i valori perchè "true" come stringa non mi piace.
		if($this->checkboxForm=="true") { $this->checkboxForm=true;} else {$this->checkboxForm=false;}
		if($this->ABCDmenu=="true") { $this->ABCDmenu=true;} else {$this->ABCDmenu=false;}

		if (($this->ABCDmenu==true)&&($this->letterSelectField!="")&&($session->get($this->istanceName."gridSelectedLetter")!="")) {
			//	se ho selezionato una lettera e c'è configurato un campo
			//	per selezionare l'elenco per lettera e se e' specificata
			//	una lettera
			if (stristr($this->query," where ")) {
				// se c'e' un where aggiungo la condizione
				// sulla lettera
				$this->query=preg_replace("/ where /i",' where '.$this->letterSelectField.' like \''.$session->get($this->istanceName."gridSelectedLetter").'%\' and ',$this->query);
			} else {
				//	non ci sono condizioni, aggiungo quella
				//	sulla lettera
				$this->query.=' where '.$this->letterSelectField.' like \''.$session->get($this->istanceName."gridSelectedLetter").'%\'';
			}

			$this->getABCDmenu($session->get($this->istanceName."gridSelectedLetter"));
		}
		$sql=$this->query;
		$sql.= " ORDER BY ".$session->get($this->istanceName."gridOrderBy")." ";
		$sql.= $session->get($this->istanceName."gridOrderMode");
		$sql.= " LIMIT ".(integer)$session->get($this->istanceName."gridStart").",";
		$sql.=(integer)$session->get($this->istanceName."gridPageSize");
		$x=strpos($sql,"SQL_CALC_FOUND_ROWS");
		if (strpos($sql,"SQL_CALC_FOUND_ROWS")==false) $sql = preg_replace("/^select /i","select SQL_CALC_FOUND_ROWS ",$sql);
		$t="";
		if ($this->debug) {
			$t.="<div class='helpbox'><b>QUERY:</b><br/> $sql<br/>";
			$rs = $conn->query($sql) or $t .= "<span style='background-color:red;color:#fff;'>errore: ".$conn->error."</span>";
			$t.="</div><br style='clear:both'/>";
		} else {
			$rs = $conn->query($sql) or trigger_error("Query: {$sql}; errore: ".$conn->error);
		}
		$sql = "Select FOUND_ROWS()"; $temp = $conn->query($sql); 
		$temprow = $temp->fetch_array();
		$max = $temprow[0];
		$indietro=0;
		if ($session->get($this->istanceName."gridStart") > $max) {
			$session->register($this->istanceName."gridStart",0);
			//echo $istanceName."gridStart";
			//echo $session->get($this->istanceName."gridStart");
			return "<script type='text/javascript'> document.location = \"$this->gestore?gridSelectedLetter=$this->gridSelectedLetter&gridStart=".$indietro."$this->parametriDaPssare\";</script>";
		}

		$ab = 0;	//per dare l'id alla riga;
		if ($rs->num_rows>0) {

			$titoli = explode(",",$this->titoli);
			$titolicampi = explode (",",$this->campi);

			if(count($titolicampi)<count($titoli)) {
				return "<div class='helpbox'>Attenzione la griglia non &egrave; configurata correttamente, stai estraendo meno campi di quelli che vuoi visualizzare.</div>";
			}


			$maxPagine = ceil($max / $session->get($this->istanceName."gridPageSize"));
			$paginaattuale= ceil($session->get($this->istanceName."gridStart") /  $session->get($this->istanceName."gridPageSize")) + 1;
			if ($paginaattuale > $maxPagine) $paginaattuale = $maxPagine;

			//--------------------------------------
			//paginatore
			$p = "";

			if ($max > $session->get($this->istanceName."gridPageSize") || $this->mostraRecordTotali) {
				$indietro=$session->get($this->istanceName."gridStart") - $session->get($this->istanceName."gridPageSize");
				$avanti = $session->get($this->istanceName."gridStart") + $session->get($this->istanceName."gridPageSize");
				if ($indietro >= 0) {
					$p.="<a title=\"Go to first page\" href=\"$this->gestore?gridSelectedLetter=$this->gridSelectedLetter&gridStart="."0"."$this->parametriDaPssare\"><img align=\"absmiddle\" src=\"$root"."src/images/rw.gif\" alt=\"prima\" border=\"0\"></a>&nbsp;&nbsp;";

					//pagina indietro
					$p.="<a title='Go to previous page' href=\"$this->gestore?gridSelectedLetter=$this->gridSelectedLetter&gridStart=".$indietro."$this->parametriDaPssare\"><img align=\"absmiddle\" src=\"$root"."src/images/arrow_left.gif\" alt=\"indietro\" border=\"0\"></a>&nbsp;&nbsp;";

				}
				$p.=" Page <B>".number_format($paginaattuale,0,'.','.')."</B> of <B>".number_format($maxPagine,0,'.','.')."</B> ";

				if ($avanti < $max) {
					//pagina avanti
					$p.="&nbsp;&nbsp;<a title='Go to next page' href=\"$this->gestore?gridSelectedLetter=$this->gridSelectedLetter&gridStart=".$avanti."$this->parametriDaPssare\"><img align=\"absmiddle\" src=\"$root"."src/images/arrow_right.gif\" alt=\"avanti\" border=\"0\"></a>&nbsp;&nbsp;";

					$p.="<a title=\"Go to last page\" href=\"$this->gestore?gridSelectedLetter=$this->gridSelectedLetter&gridStart=".($max-$session->get($this->istanceName."gridPageSize"))."$this->parametriDaPssare\"><img align=\"absmiddle\" src=\"$root"."src/images/ff.gif\" alt=\"ultima\" border=\"0\"></a>";

				}
				$p.="&nbsp;&nbsp;&nbsp;";
				if ($this->mostraRecordTotali) $p.="<span id='infogrid'>".number_format($max,0,'.','.')." records</span>";
			}


			//--------------------------------------



			if ($this->checkboxForm==true)
				$custr="<input type=\"button\" class='checkall' name=\"CheckAll\" title=\"Check all / Uncheck\" value=\"Check All\"
				onclick=\"return checkAll(document.".$this->checkboxFormName.".elements['gridcheck[]'])\">";
			//
			// intestazione colonne
			//
			if ($this->checkboxForm==true) {
				//
				// se ho le checkbox devo inserire un form
				//
				$t.="<form name=\"$this->checkboxFormName\" method=\"post\" action=\"$this->checkboxFormAction\">\n";
				$t.="<input type=\"hidden\" name=\"op\" value=\"checkboxes\">\n";
				$t.="<input type=\"hidden\" name=\"id\" value=\"\">\n";
			}
			$t.="<table class='griglia' id='tab_{$this->tablename}'>";
			$t.="<thead>";


			//header--------------------------------------
			$th="<tr><th class='top' colspan='".(count($titoli)+count($this->comandi)+1)."'>{$p}</th></tr>";
			$th.="<tr>";
			if($this->checkboxForm) {
				//se ho le checkbox e l'intestazione metto il td.
				$th.="\t<th>{$custr}{$this->checkboxFormTitolo}</th>";
			}
			for ($i=0; $i<count($titoli); $i++ ){
				$th.="\t<th>";

				if($this->functionhtml == "htmlspecialchars") {
					$label =htmlspecialchars($titoli[$i]);
				} elseif($this->functionhtml == "htmlentities") {
					$label =htmlentities($titoli[$i]);
				} elseif($this->functionhtml == "myhtmlspecialchars") {
					$label =myhtmlspecialchars($titoli[$i]);
				} else $label = $titoli[$i] ;

				if ($this->flagOrdinatori=="on") {

					if(
						($titolicampi[$i] == $session->get($this->istanceName."gridOrderBy"))
						&&
						($session->get($this->istanceName."gridOrderMode" )== "asc")
						) {
							$link = "<a title=\"Order by $titoli[$i] descending\" href=\"$this->gestore?gridSelectedLetter=$this->gridSelectedLetter&gridOrderBy=$titolicampi[$i]&gridOrderMode=desc$this->parametriDaPssare\">";
							$img = "<img src=\"{$root}src/images/arrow_up.gif\" alt=\"\" border=\"0\">";
					} elseif (($titolicampi[$i] == $session->get($this->istanceName."gridOrderBy"))
						&&
						($session->get($this->istanceName."gridOrderMode" )== "desc")
						) {
							$link = "<a title=\"Order by $titoli[$i] ascending\" href=\"$this->gestore?gridSelectedLetter=$this->gridSelectedLetter&gridOrderBy=$titolicampi[$i]&gridOrderMode=asc$this->parametriDaPssare\">";
							$img = "<img src=\"{$root}src/images/arrow_down.gif\" alt=\"\" border=\"0\">";
					} else {
						$link = "<a title=\"Order by $titoli[$i] ascending\" href=\"$this->gestore?gridSelectedLetter=$this->gridSelectedLetter&gridOrderBy=$titolicampi[$i]&gridOrderMode=asc$this->parametriDaPssare\">";
						$img = "";
					}


					$th.=$link.$label.$img;
					$th.="</a>";

				} else {
					$th .= $label;
				}
				$th.="</th>\n";
			}

			if(count($this->comandi)>0) $th.="<th colspan='".count($this->comandi)."' align='right'>";
			
			//
			$th.="</th>";
			$th.="</tr>\n";
			$t.=$th."</thead>";
			//--------------------------------------

			//footer--------------------------------------
			//$t.="<tfoot>".$th."</tfoot>";
			//--------------------------------------


			$t.="<tbody>";

			//
			// elenco dati
			//
			while ($r=$rs->fetch_array()) {
				$t_TR="";
				$id="";
				$t_FOR="";

				// ---------------------------------------------------------------------------
				// TODO: modificato il sistema di visualizzazione per essere indipendente dall'
				// ordine dei campi specificato nella query ma usa quelli indicati nella
				// variabile $this->campi
				// ---------------------------------------------------------------------------
				
				$arCampi = explode(",",$this->campi);
				for ($i = 0; $i<count($arCampi); $i++) {
					$arCampi[$i] = trim ($arCampi[$i]);
					if($this->chiave) $id = $r[$this->chiave]; else $id ="";
					$classetr = "";
					$classetd = "";

					if (isset($this->arFormattazioneTD) ) {
						foreach ($this->arFormattazioneTD as $nomecampo => $val) {
							if($nomecampo == $arCampi[$i] ) {
								$classetd = $val;
							}
						}
					}



					if (($this->checkboxForm==true)&&($i==0)) {
						//se è impostato "true" questo flag aggiungo le checkbox per
						//generare il form trasversale
						$t_FOR.="\t<td class='$classetd' id='cell_{$id}_check'>";
						$t_FOR.="<input type=\"checkbox\" {$this->checkboxFormChekcboxAttributes} name=\"gridcheck[]\" value=\"$id\"></td>";
					}

				
					if (isset($this->arFormattazioneCondizionale)) {
						//	se c'è definito questo vettore allora lo uso per determinare il
						//	css della riga.
						// UN VALORE IN UN CAMPO PUO' DETERMINARE UNA CLASSE SUL TR
						foreach ($this->arFormattazioneCondizionale as $nomecampo => $arValori) {
							if($nomecampo == $arCampi[$i] ) {
								for($fc=0;$fc<count($arValori);$fc++) {
									if($arValori[$fc]==$r[$arCampi[$i]]) {
										$classetr = $arValori[$fc + 1];
									}
									$fc++;
								}
							}
						}
					}




					if (($id == $this->evidenzia)&&($this->evidenzia!="0")) $classetr = $this->tdcssevidenzia;
					$t_FOR.="\t<td class='$classetd' id='cell_{$id}_{$i}'>";


					if (array_key_exists ($arCampi[$i],$this->scegliDaInsieme)) {
						//se per il campo specificato c'e' una label alternativa da mostrare
						//al posto del campo, allora la mostra
						if (isset($this->scegliDaInsieme[$arCampi[$i]][$r[$arCampi[$i]]]))
							$t_FOR.= str_replace("##$this->chiave##",$id,$this->scegliDaInsieme[$arCampi[$i]][$r[$arCampi[$i]]]) ;
						else $t_FOR.=$r[$arCampi[$i]];
					} else if (array_key_exists ($arCampi[$i],$this->campiMeta)) {
						//	formattazione dei campi data
						switch ($this->campiMeta[$arCampi[$i]]) {
							case 'url':
								if(trim($r[$arCampi[$i]])) {
									$t_FOR.="<a href='".(preg_match("/^http:/i",$r[$arCampi[$i]])?"http://":"").$r[$arCampi[$i]]."'>".$r[$arCampi[$i]]." <img src='".$root."src/images/browse.gif'/></a>";
								} else {
									$t_FOR.=$r[$arCampi[$i]];
								}
								break;
							case 'email':
								if(trim($r[$arCampi[$i]])) {
									$t_FOR.="<a href=\"mailto:".$r[$arCampi[$i]]."\" class='emaildata'>".$r[$arCampi[$i]]."</a>";
								} else {
									$t_FOR.=$r[$arCampi[$i]];
								}
								break;
							case 'dd/mm/yyyy':
								$t_FOR.=($r[$arCampi[$i]] && !stristr($r[$arCampi[$i]],'0000-00-00')) ? TOdmy($r[$arCampi[$i]],"/") : $r[$arCampi[$i]];
								break;
							case 'mm/dd/yyyy':
								$t_FOR.=($r[$arCampi[$i]] && !stristr($r[$arCampi[$i]],'0000-00-00')) ? date("m/d/Y",strtotime($r[$arCampi[$i]])) : $r[$arCampi[$i]];
								break;
							case 'mm/dd/yyyy hh:ii':
								$t_FOR.=($r[$arCampi[$i]] && !stristr($r[$arCampi[$i]],'0000-00-00')) ? date("m/d/Y H:i",strtotime($r[$arCampi[$i]])) : $r[$arCampi[$i]];
								break;
							case 'timestamp':
								$r[$arCampi[$i]] = date("Y-m-d H:i:s",(float) $r[$arCampi[$i]]);
							case 'dd/mm/yyyy hh:ii':
								if ($r[$arCampi[$i]] && !stristr($r[$arCampi[$i]],'0000-00-00')) {
									$tempAr = explode(" ",$r[$arCampi[$i]]);
									$t_FOR.=TOdmy($tempAr[0],"/")." ".$tempAr[1];
								} else {
									$t_FOR.=$r[$arCampi[$i]];
								}
								break;
							default:
								// funzione esterna
								eval ( '$t_FOR.='.$this->campiMeta[$arCampi[$i]] ."(\"".str_replace("\"","&quot;", $r[$arCampi[$i]])."\");" );
								break;

						}

					} else {
						//
						//altrimenti mostro il valore estratto
						//applicando l'encode definito.
						//
						if(!isset($r[$arCampi[$i]])) { 
							if($this->debug) {
								$r[$arCampi[$i]] = "<span class='nf'><b>{$arCampi[$i]}</b> not found!</span>";
							} else {
								$r[$arCampi[$i]] = "<span class='nf2'>null</span>";
							}
						}
						if($this->functionhtml == "htmlspecialchars") {
							$t_FOR.=htmlspecialchars($r[$arCampi[$i]]);
						} elseif($this->functionhtml == "htmlentities") {
							$t_FOR.=htmlentities($r[$arCampi[$i]]);
						} elseif($this->functionhtml == "myhtmlspecialchars") {
							$t_FOR.=myhtmlspecialchars($r[$arCampi[$i]]);
						} else $t_FOR.= $r[$arCampi[$i]] ;
					}
					$t_FOR.="</td>\n";

				}

				$ab++;

				$t_TR="<tr id=\"tr{$ab}\" {$classetr}>";

				$t.=$t_TR.$t_FOR;
				for ($i=0; $i<count($this->comandi); $i++ ){
					$t.="\t<td>";
					$comando = $this->comandi[$i]["link"];
					for ($j=0; $j<mysqli_num_fields($rs); $j++) {
						$field = $rs->fetch_field_direct($j);
						$comando=str_replace("##".$field->name."##",$r[$j],$comando);
					}
					//se nel comando c'è "##idrigatabella##" lo sostituisce con l'id della riga tr della tabella
					$comando=str_replace("##idrigatabella##","tr{$ab}",$comando);
					$t.="<a title=\"".$this->comandi[$i]["title"]."\" class=\"".$this->comandi[$i]["label"]."\" href=\"".str_replace("##$this->chiave##",$id,$comando)."\">";

					//$t.=$this->comandi[$i]["label"];
					$t.="</a>";
					$t.="</td>\n";
				}

				$t.="</tr>\n";
			}
			$t.="</tbody>";
			$t.="</table>\n";
			if ($this->checkboxForm==true) {
				//
				// se ho le checkbox devo chiudere il form
				//
				$t.="</form>\n";
			}
		} else {
			$t.="";	//eventuale messaggio di tabella vuota;
		}
		if ($this->ABCDmenu==true) {
			$t=$this->getABCDmenu($session->get($this->istanceName."gridSelectedLetter")). $t;
		}

		//if ($ab>0) $t.="<script language='javascript'>stripe('tab_{$this->tablename}');</script>";

		if ($this->checkboxForm==true && $max>0) $t="<script  type='text/javascript'>
			function checkAll(field) {
				var v = !field[0].checked;
				if (field.length==undefined) field.checked = v;
				for (i = 0; i < field.length; i++) field[i].checked = v ;
				return false;
			}
			</script>".$t;

		return $t;
	}

}

?>