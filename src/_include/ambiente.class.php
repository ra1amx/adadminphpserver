<?php
class Ambiente
{
	function __construct ()
	{

	}

function setPosizione($var)
	{
		//trigger_error("RIMUOVERE setPosizione $var");
		global $defaultReplace;
		$defaultReplace['##TITLE##'] = $var;
	}

	function setNomeUtente()
	{
		trigger_error("RIMUOVERE chiamata a setNomeUtente()");
	}


	function loadLogin($msg){
		global $root;
		//ritorna una stringa javascript che scatena il caricamento della pagina di login nel frame giusto.
		return "<script language=\"javascript\">top.location.href=\"".$root."src/login.php?msg=".urlencode($msg)."\";</script>";
	}


	function goHome(){
		//ritorna una stringa javascript che scatena il caricamento della home nel frame principale
		return "<script language=\"javascript\">document.location.href=\"".$root."src/index.php\";</script>";
	}
}

?>
