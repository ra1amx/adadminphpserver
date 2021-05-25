<?php
/*
	cancellazione file (si puo' se si è loggati)
*/
$root="../../../";
include($root."src/_include/config.php");
if($session->get("idutente")!="") {
	if(isset($_GET['f'])) {
		$f = $_GET['f'];
		$div0 = $_GET['div0'];
		die( deletefilegallery($f,$div0) );
	}
}
die("ko");
?>