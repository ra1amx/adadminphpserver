<?php

class User
{

	var $id;

	function __construct ($id="",$max_user_level=999999) {

		$this->id=$id;
		$this->MAX_USER_LEVEL=$max_user_level;
	}

	function setProfilo($sid) {
		global $conn;
		// 10 = user
		// 20 = administrator
		$id = $this->id;
		$sql="";
		$sql = "delete from frw_ute_fun where idutente='$id';";
		//echo $sql;
		$conn->query($sql) or die($conn->error."sql='$sql'<br>");
		$sql = "select * from frw_profili_funzionalita where cd_profilo='$sid'";
		//echo $sql;
		$rs =  $conn->query($sql) or die($conn->error."sql='$sql'<br>");
		while ($r=$rs->fetch_array()) {
			$sql2 = "insert into frw_ute_fun (idutente,idfunzionalita,idmodulo) values('$id','{$r['cd_funzionalita']}','{$r['cd_modulo']}');";
			//echo $sql2;
			$rs2 = $conn->query ($sql2);
		}

	}

	function getProfilo($id,$maxid="") {
		//ritorna l'html per la tendina della scelta del profilo:
		//prende i valori fino a quelli limitati da maxid
		global $session,$conn;
		if ($maxid=="") $maxid=$id;
		$sql= "select * from frw_profili where id_profilo<='".$session->get("idprofilo")."' order by id_profilo asc";
		$rs = $conn->query($sql) or die($conn->error."sql='$sql'<br>");
		$html="";
		while ($r=$rs->fetch_array()) {
			$html.="<option value=\"$r[id_profilo]\"";
			if ($r["id_profilo"]==$id) $html.=" selected";
			$html.=">$r[de_label]";
			$html.="</option>";
		}
		return $html;

	}

	function profiloEditabile($idprof) {
		/*
			ritorna true se il profilo idprof è editabile
			dall'utente loggato in questo momento
		*/
		global $session,$conn;
		$sql= "select chiedita from frw_profili where id_profilo='".$session->get("idprofilo")."'";
		$rs = $conn->query($sql) or die($conn->error."sql='$sql'<br>");
		$r=$rs->fetch_array();
		return stristr ( $r['chiedita'], ",".$idprof."," );
	}

	function getChiEdita($idprof) {
		/*
			ritorna quelli che puo' editare
		*/
		global $session,$conn;
		$sql= "select chiedita from frw_profili where id_profilo='".$session->get("idprofilo")."'";
		$rs = $conn->query($sql) or die($conn->error."sql='$sql'<br>");
		$r=$rs->fetch_array();
		return $r['chiedita'];
	}

	function getUserData() {
		//ritorna i dati dell'utente nell'array estratto con il recordset
		return execute_row( "select * from frw_utenti where id='$this->id'" );
	}

	function existUserWithUsername($username,$notme="") {
		//true se esiste un utente diverso da $notme con la stessa username
		global $conn;

		$sql= "select * from frw_utenti where username='$username'";
		if ($notme!="") {
			$sql.=" and id<>'$notme'";
		}
		$rs = $conn->query ($sql);
		if ($rs->num_rows>0) return true; else return false;
	}

	function getArrayUtenti($profilicsv = "") {
		global $conn;
		//restituisce array di coppie id,nomeutente
		$where = $profilicsv==""? "" : " where cd_profilo in ( {$profilicsv}) and fl_attivo=1";
		$sql = "select id,concat(cognome,' ',nome) as cognomenome from frw_utenti {$where} order by cognome";
		$rs = $conn->query($sql) or die($conn->error."sql='$sql'<br>");
		$arUtenti = array();
		while($riga = $rs->fetch_array()) {
			$arUtenti[$riga['id']]=$riga['cognomenome'];
		}
		return $arUtenti;
	}
}

?>