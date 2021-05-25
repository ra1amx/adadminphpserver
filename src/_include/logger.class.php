<?php

class logger {
	var $logfile;
	var $level;
	var $errorTemplate;

	function __construct() {
		$this->errorTemplate="<div class='errore'>##msg##</div>";
		set_error_handler(array(&$this,'handle_error'));
	}

	function setLogFile($logfile) {
		// 18/04/2011
		// setta il file di log. se non viene chiamata, il file di log viene settato
		// alla prima addlog o alla prima chiamata che deve usare il file di log
		// prendendolo dalla costante LOGS_FILENAME definita nel file di configurazione
		// dentro alla cartella ponsconfig
		$this->logfile = $logfile;
	}

	function addlog($description="",$filename="") {
		global $root;
		if(!defined("LOGS_FILENAME")) return; // se non è definito il file di log esce.
		if(!isset($this->logfile)) $this->logfile = $root.LOGS_FILENAME;
		if ($filename=="") $filename=$_SERVER["PHP_SELF"];
		$f = @fopen($this->logfile,'a');
		$Message=date('d/m/y h:i:s')." ".$filename." ".$description." ".$_SERVER['REMOTE_ADDR']." Browser:".$_SERVER['HTTP_USER_AGENT'];
		$Message.="\n";
		@fwrite($f,$Message);
	}

	function logsize() {
		global $root;
		if(!defined("LOGS_FILENAME")) return;
		if(!isset($this->logfile)) $this->logfile = $root.LOGS_FILENAME;
		if(!file_exists($this->logfile)) return "n.d.";
		return number_format(filesize($this->logfile)/1024,0,',','.') . " Kbyte";

	}
	function displayLog(){
		global $root;
		if(!defined("LOGS_FILENAME")) return;
		if(!isset($this->logfile)) $this->logfile = $root.LOGS_FILENAME;
		return nl2br(loadTemplate($this->logfile));
	}

	function deleteLog(){
		global $root;
		if(!defined("LOGS_FILENAME")) return;
		if(!isset($this->logfile)) $this->logfile = $root.LOGS_FILENAME;
		if (file_exists($this->logfile)) {
			unlink($this->logfile);
			echo $this->logfile." rimosso.";
		} else {
			echo $this->logfile." non trovato.";
		}
		return "";
	}

	function handle_error ($errno, $errstr, $errfile, $errline) {
		global $session,$root;
		/*
			se arrivo qui che non ho ancora queste tre costanti definite
			allora arrivo da un errore nella maschera di login,
			probabilmente causato dalla classe della sessione
		*/
		if(!defined('SEND_ERRORS_MAIL')) define("SEND_ERRORS_MAIL","");
		if(!defined('SHOW_ERRORS')) define("SHOW_ERRORS",true);
		if(!defined('STOP_ON_ERROR')) define("STOP_ON_ERROR",false);
		
		/*if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting
			return;
		}*/
		
		/*
			assemblo il messaggio dell'errore, questo messaggio
			contiene anche il dump dei dati
		*/
		// loggo gli errori
		$this->addlog("Error number: $errno, cio&egrave;: $errstr - File: $errfile, Linea: $errline - Main file: ".$_SERVER['PHP_SELF']);

		$text = "<link href=\"".$root."src/template/stile.css\" type=\"text/css\" rel=\"stylesheet\"><script language=\"JavaScript\" src=\"".$root."src/template/comode.js?z\"></script><b>An error has happened.</b><p>".(SEND_ERRORS_MAIL ? "I send a notice to the System Administrator." : "")."<br>"."Error number: "."$errno: $errstr<br/><b>File:</b> $errfile, <b>Line:  $errline</b>"."<br>Main file: ".$_SERVER['PHP_SELF']."<br>
		PHP ver. ".phpversion()."<br/>
		<a href=\"javascript:show('dump')\">[Show dump]</a></p><div id='dump' class='dump' style='display:none'>".$this->dump_info()."</div>";
		$text=str_replace("##msg##",$text, $this->errorTemplate);
		
		if (SEND_ERRORS_MAIL) {
			//
			// se c'e' la mail invia il messaggio
			//
			$headers = "MIME-Version: 1.0\r\n"
				."Content-type: text/html; charset=iso-8859-1\r\n"
				."From: errorhandler@{$_SERVER['SERVER_NAME']}\r\n"
				."X-Mailer: PHP/" . phpversion();
			$headers = str_replace("\r","",$headers);
			mail(SEND_ERRORS_MAIL, "Error on [".DOMINIODEFAULT."] user ".$session->get("username"), $text, $headers);
		}
		if (SHOW_ERRORS == true || !defined("SHOW_ERRORS")) echo $text;
		if (STOP_ON_ERROR == true || !defined("STOP_ON_ERROR")) die();
		/* Don't execute PHP internal error handler */
		return true;

	}

	function dump_array($s,$sep=",") {
		$o="";
		if (is_array($s)) 
			foreach($s as $key=>$value) {
			//while ( $element = each($s)) {
				$o .= htmlspecialchars($key);
				$o .= " = ";
				if (is_array($value)) $o.="Array(".$this->dump_array($value).")"; else
					$o .= htmlspecialchars($value);
				$o .= $sep;
			}
		else return $s;
		return $o;
	}

	function dump_info(){
		$o = "";
		$o .= "<h2>Vars in _SESSION:</h2>";
		$o .= isset($_SESSION) ? $this->dump_array($_SESSION,"<br>") : "nulla.";
		$o .= "<h2>Vars in _GET:</h2>";
		$o .= $this->dump_array($_GET,"<br>");
		$o .= "<h2>Vars in _POST:</h2>";
		$o .= $this->dump_array($_POST,"<br>");
		$o .= "<h2>Vars in _SERVER:</h2>";
		$o .= $this->dump_array($_SERVER,"<br>");
		$o .= "<h2>Classes declared:</h2>";
		$o .= $this->dump_array(get_declared_classes(),"<br>");
		return $o;
	}
}
?>