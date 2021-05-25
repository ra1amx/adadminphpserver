<?php
/*
	sposta file (si puo' se si è loggati), sposta anche il .info
*/
$root="../../../";
include($root."src/_include/config.php");
if($session->get("idutente")!="") {
	$da = $_GET['da'];
	$a = $_GET['a'];
	$div0 = $_GET['div0'];
	die ( spostafilegallery($da,$a,$div0) );
}
die("ko");
?>