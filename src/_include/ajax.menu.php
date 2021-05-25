<?php

$root="../../";
include($root."src/_include/config.php");

if (!Connessione()) trigger_error($conn->error); else CollateConnessione();

echo $login->getMenu($session->get("idutente")).
	$login->logOutLink();

?>