<?php
class Login {

	var $template;
	var $usernamevar;
	var $passwordvar;
	var $setmodulovar;
	var $actionurl;
	var $externalUserLogin;			//contiene il nome della chiamata ad una funzione esterna che
									//puo' essere utilizzata per fare un check diverso per la login
									//nel caso, che le funionalita' del framework non siano sufficienti
									//per effettuare una login.
									//in particolare: se e' definito $externalUserLogin (che conterra' il
									//nome di una funzione scritta in un file specifico per l'applicazione
									//del cliente e incluso nel file di configurazione del cliente) dopo
									//aver fatto il login secondo le regole del framework, viene invocata
									//la funzione esterna e se anche essa da' true allora la checkuser ritorna
									//true, altrimenti ritorna false.

	var $externalUserLogout;		//idem per il logout


	function __construct() {
		$this->usernamevar="utente";
		$this->passwordvar="password";
		$this->actionurl=$_SERVER['PHP_SELF'];
		$this->template = "";
		$this->externalUserLogin="";
		$this->externalUserLogout="";
		return true;
	}

	function getLoginForm($msg="") {
		global $session,$conn,$root;
		$html = loadTemplateAndParse(
			WEBURL."/data/".DOMINIODEFAULT."/layout-login-form.php"
		);

		$html = str_replace("##msg##", $msg, $html);
		$html = str_replace("##usernamevar##", $this->usernamevar, $html);
		$html = str_replace("##passwordvar##", $this->passwordvar, $html);
		$html = str_replace("##actionurl##", $this->actionurl, $html);
		$html = str_replace("##LOGO##", LOGO, $html);

		if(!defined("SERVER_EMAIL_ADDRESS") || (SERVER_EMAIL_ADDRESS=="")) {
			$html = str_replace("##hiderecover##", "style='display:none'", $html);
		} else {
			$html = str_replace("##hiderecover##", "", $html);
		}
		
		return $html;
	}

	function getResetForm($msg="",$email="",$pass1="",$pass2="",$code="") {
		global $session,$conn,$root;

		$code = preg_replace("/[^0-9a-z]/i","",$code);

		// aggiunge il campo per reset password
		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = 'frw_extrauserdata' AND COLUMN_NAME = 'de_temp'");
		if($q==0) {
			$sql = "ALTER TABLE frw_extrauserdata ADD de_temp VARCHAR(200) NOT NULL";
			$conn->query($sql) or die(trigger_error("Error while upgrading your DB for Password Reset function. ".$conn->error." sql='$sql'<br>"));
		}
		$html = loadTemplateAndParse(
			$root."data/".DOMINIODEFAULT."/layout-resetpassword.php"
		);
		$ciao = "Type your email to reset your password.";
		if(!defined("SERVER_EMAIL_ADDRESS") || (SERVER_EMAIL_ADDRESS=="")) {
			$ciao = "<b>WARNING</b>:<br>
			Can your server send emails? Be sure of it.<br>
			Add to your <code>pons-settings.php</code> file this line to use password recovery function:
			<br>
			<pre>define(\"SERVER_EMAIL_ADDRESS\", \"validemail@yourserver.com\");</pre>";
			$html = str_replace("##hideall##", "style='display:none'", $html);

		} else {

			if($code!="" && $pass1=="" && $pass2=="") {
				/* step 2, ho il codice di verifica dal messagio mail */
				$user = execute_row("select * from frw_extrauserdata inner join frw_utenti on cd_user=id where de_temp='".$code."'");
				if(isset($user['de_email'])) {
					$html = str_replace("##hideall##", "", $html);

					//print_r($user);
					
				} else {
				
					$code = "";

				}
			}

			if($code!="" && $pass1!="" && $pass2!="") {
				if ($pass1 == $pass2) {
					$user = execute_row("select * from frw_extrauserdata inner join frw_utenti on cd_user=id where de_temp='".$code."'");
					if(isset($user['de_email'])) {
						$cr = new cryptor();
						$conn->query("update frw_extrauserdata set de_temp='' where cd_user='".$user['cd_user']."'");
						$conn->query("update frw_utenti set password='".$cr->crypta($pass1)."' where id='".$user['cd_user']."'");
						$html = str_replace("##hideall##", "style='display:none'", $html);
						$ciao = "Try your new password <a href=\"".WEBURL."\">here</a>.";
					} else {
						$code = "";
						$email = "";
					}

				}
			}

			if($email!="" && $pass1=="" && $pass2=="")  {
				/* step 1, c'è una email e non le pass, controllo se l'utente esiste */
				$msg = "User not found.";
				if(is_email($email)) {
					$user = execute_row("select * from frw_extrauserdata inner join frw_utenti on cd_user=id where de_email='".$email."'");
					$msg = "";
					$ciao = "Hi ".$user['nome'].", check your email for email reset link.<br>";
					$html = str_replace("##hideall##", "style='display:none'", $html);

					$code = md5($email."--check-");
					$conn->query("update frw_extrauserdata set de_temp='".$code."' where cd_user='".$user['cd_user']."'");

					$link  = WEBURL."/src/resetpassword.php?code=".$code;
					$nome=strtoupper(str_replace("/","",PONSDIR));
					mail($email,"[".$nome."] Reset password","Hi,\nto reset your password click here:\n".$link,"From:".SERVER_EMAIL_ADDRESS);

				} else {
					$code = "";
				}
				$email = "";
			}

		
		}

		if($email=="" && $code=="") {
			$html = str_replace("##show##", "", $html);
			$html = str_replace("##hide##", "style='display:none'", $html);
		} else {
			if($code=="") {
				$ciao = "Choose a new password:";
				if($pass1!=$pass2) {
					$ciao = "Passwords mismatch! They must be the same.";
				}
			} else {
			
			}
			$html = str_replace("##hide##", "", $html);
			$html = str_replace("##show##", "style='display:none'", $html);
		}
		$html = str_replace("#ciao#", $ciao, $html);
		$html = str_replace("##code##", $code, $html);
		$html = str_replace("##msg##", $msg, $html);
		$html = str_replace("##email##", $email, $html);
		$html = str_replace("##actionurl##", $this->actionurl, $html);
		$html = str_replace("##hideall##", "", $html);
		return $html;
	}

	function logged() {
		global $session;

		if ( $session->get("username")!="") {
			//echo $s->get("username")."----";
			//utente già loggato, ci sono i dati in sessione
			return true;

		} else {
			//utente non loggato, se ci sono dati nel post prova a fare la login
			if (isset($_POST[$this->usernamevar])) {
				return @$this->checkUser($_POST[$this->usernamevar],$_POST[$this->passwordvar]);
			} else {
				return false;
			}
		}
	}


	function checkUser($username,$password,$modulo=1) {
		global $session,$logger,$conn;
		if (!defined('WEBDOMAIN')) return false;

		if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

		$cr = new cryptor();
		$sql = "SELECT id, username,
				password,
				nome, cognome, cd_profilo
				FROM frw_utenti where username='$username' and password='".$cr->crypta($password)."' and frw_utenti.fl_attivo='1'";
		$rs = $conn->query($sql) or trigger_error($conn->error);
		if ($rs->num_rows == 1) {
			$row = $rs->fetch_array();

			// forzo distribuzione permessi sull'utente che si è loggato
			require_once("componenti/gestioneutenti/_include/user.class.php");
			$u=new user();
			$u->id= $row['id'];
			$u->setProfilo($row['cd_profilo']);

			$session->register("idutente",$row['id']);
			$session->register("username",$row['username']);
			$session->register("password",$row['password']);
			$session->register("nome",$row['nome']);
			$session->register("idprofilo",$row['cd_profilo']);
			$session->register("cognome",$row['cognome']);
			//$this->setmoduloAttiva(execute_scalar("select id from frw_moduli where nome='".addslashes(PRIMO_MODULO_DA_MOSTRARE)."' and visibile=1"));
			$logger->addlog( "{inizio sessione utente ".$session->get("username").", id=".$session->get("idutente")."}" );

			return true;
		} else {
			$logger->addlog( "{login fallita user ".$username.", pass=".$password."}" );
			return false;
		}

	}

	function getMenu($idutente) {
		global $session,$conn,$root;

		//
		if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

		//if (execute_scalar("SHOW COLUMNS FROM frw_moduli LIKE 'posizione'")!='posizione') $this->install();

		$modulo = $session->get("moduloattiva");

		$sql = "SELECT distinct urlcomponente, urliconamenu, frw_componenti.id, frw_componenti.nome, frw_componenti.label AS labelc, frw_moduli.label AS labelv , frw_moduli.label as nmodu, frw_moduli.id as idmodu, frw_moduli.nome as nomemodu, frw_componenti.descrizione, 
		frw_moduli.posizione,frw_com_mod.posizione as posizione2
		from frw_ute_fun
		join frw_funzionalita on idfunzionalita=frw_funzionalita.id
		join frw_componenti on frw_funzionalita.idcomponente=frw_componenti.id
		join frw_com_mod on frw_com_mod.idcomponente=frw_componenti.id
		join frw_moduli on frw_moduli.id=frw_com_mod.idmodulo
		WHERE idutente = '$idutente' and frw_moduli.visibile=1 ".(RESTRICT_MODULO<>"" ? "AND frw_moduli.label in ('".addslashes(RESTRICT_MODULO)."','Config')" : "")." order by frw_moduli.posizione,idmodu,frw_com_mod.posizione";
		//echo $sql;
		$rs = $conn->query($sql) or trigger_error($conn->error);
		$html="";
		$nomemodulo = "";
		while($row = $rs->fetch_array()){
			if ($nomemodulo!=$row['nmodu']) {
				if ($html!="") $html.="</div></div>";
				$html.="<a class=\"linkmenu0 ".$row['nomemodu']."\" data-rel='modulo{$row['idmodu']}' href=\"javascript:show('modulo{$row['idmodu']}')\" title=\"{$row['nomemodu']}\">".($row['nmodu'])."</a><div id='modulo{$row['idmodu']}' ".($modulo!=$row['idmodu']?"style='display:none'":"")."><div>";
				$nomemodulo = $row['nmodu'];
			}
			if(preg_match("#^https?://#",$row['urlcomponente'])) {
				$target = "target='_blank'";
				$href = $row['urlcomponente'];
			} else {
				$target = "";
				$href = WEBURL."/src/".$row['urlcomponente'];
			}
			$html.="\n<a class=\"linkmenu ".$row['nome']."\" href=\"".$href."\" title=\"".$row['descrizione']."\" {$target}>";
			$html.=" ".($row['labelc'])."</a>";
		}
		$html.="</div></div>";	//chiude i moduli
		return $html;

	}

	function logOutLink() {
		return "<a class=\"linkmenu esci\" href=\"".WEBURL."/src/logout.php\">Logout</a>";
	}
}
?>