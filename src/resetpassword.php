<?php
$root="../";

$public=true;

include($root."src/_include/config.php");

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

$msg = setVariabile("msg","");
$email = setVariabile("email","");
$pass1 = setVariabile("pass1","");
$pass2 = setVariabile("pass2","");
$code = setVariabile("code","");
$html = "";

//
// prova a fare la login con i dati che forse ci sono nel post
if (!$login->logged()) {

	//.se non riesce allora genera la form
	//$session->finish();
	$html = $login->getResetForm($msg,$email,$pass1,$pass2,$code);

} else {
	header("Location: index.php");
	die;
}

echo $html;

?>