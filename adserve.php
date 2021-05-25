<?php
if(stristr($_SERVER['HTTP_USER_AGENT'],"googlebot")) {
	/* prevent google bot to see banners */
	die;
}

$root="";

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header('Content-Type: text/javascript; charset=UTF-8');
if (function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Rome');

include($root."pons-settings.php");
include($root."src/_include/comode.php");

DEFINE("BANNERLINKER",		WEBURL . "/adtrack.php");
DEFINE("DEFAULTBANNERTYPE",	"2");
DEFINE("BANNERIMAGES", $root."data/dbimg/7banner");

if (!Connessione()) die(); else CollateConnessione();

if(isset($_GET['iframe']) && isset($_GET['f'])) {
	// caricamento forzato dentro ad un iframe
	?><html><head><meta http-equiv="cache-control" content="max-age=0" /><meta http-equiv="cache-control" content="no-cache" /><meta http-equiv="expires" content="0" /><meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" /><meta http-equiv="pragma" content="no-cache" />
	<style>html,body {margin:0;padding:0;text-align:center}</style><body><script type="text/javascript" src="adserve.php?f=<?php echo $_GET['f'];?>&bypass=<?php echo rand(1,1111111);?>"></script></body></html><?
	die;
}


if(isset($_GET['f'])) { /* banner normale */
	$f = (integer)$_GET['f'];
	$banner = showBanner($f);
	$banner = str_replace("+","%20",urlencode($banner));
	echo "document.write ( unescape(\"".$banner."\") );";
	die;
}

if(isset($_GET['id'])) { /* banner normale chiamato con ID preciso, non aggiorna lo stato per la rotazione */
	$id = (integer)$_GET['id'];
	$banner = showBanner(null,"yes",$id);
	$banner = str_replace("+","%20",urlencode($banner));
	echo "document.write (unescape(\"".$banner."\"));";
	die;
}

if(isset($_GET['r'])) { /* banner refresh ????*/
	if(stristr($_GET['r'],"<script")) {
		session_start();
		$_SESSION['bannertemp'] = $_GET['r'];
		header("Location: banner.js.php?r=session");
		die;
	} elseif ($_GET['r']=='session') {
		session_start();
		echo "<html><head><style>body,html{border:0;margin:0}</style></head><body>".$_SESSION['bannertemp']."</body></html>";
	} else {
		echo "<html><head><style>body,html{border:0;margin:0}</style></head><body><script type='text/javascript' src='".$_GET['r']."'></script></body></html>";
	}
	die;
}

/*
	funzioni
*/
// ---------------------------------------------------------------------------------------------------
// per banner

function LinkBanner($image,$xx=300,$yy=250,$alternative_pic="",$href="",$target="_blank") { /*linka img. Usata nei banner*/
	$anchor="<a rel=\"nowfollow\" href=\"".$href."\"" . ($target ? " target=\"".$target."\" ":"").">";
	if (preg_match("/\.swf$/i",$image)) {
		/*
		DEPRECATO
		non passa mai qui

		$link="<object type=\"application/x-shockwave-flash\" data=\"$image\" ";
		if($xx!="") $link.=" width=\"$xx\"";
		if($yy!="") $link.=" height=\"$yy\"";
		$link.=">";
		$link.="<param name=\"pluginspage\" value=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\" />";
		$link.="<param name=\"quality\" value=\"high\" />";
		$link.="<param name=\"wmode\" value=\"transparent\" />";
		$link.="<param name=\"movie\" value=\"{$image}\" />";
		if($alternative_pic) {
			$link.=$anchor."<img src=\"$alternative_pic\"";
			if($xx!="") $link.=" width=\"$xx\"";
			if($yy!="") $link.=" height=\"$yy\"";
			$link.="/></a>";
		}
		$link.="</object>";
		*/
	} elseif(preg_match("/\.zip$/i",$image)) {
		//
		// HTML5 banner in a folder
		// butto fuori iframe che richiama index.html dentro la folder
		// può essere un iframe responsive se xx = -1
		//
		if($xx==-1) $xx="100%"; else $xx=$xx."px";
		$link ="<iframe src=\"".$href."\" style=\"border:0;display:block;margin:0 auto;overflow:hidden\" scrolling=\"no\" width=\"".$xx."\" height=\"".$yy."\" allow=\"autoplay\" allowfullscreen include></iframe>";
		
	} else {

		if($xx==-1) $d="width=\"100%\"";
			else $d="width=\"".$xx."\" height=\"".$yy."\"";
		$link=$anchor."<img src=\"".$image."\" ".$d." /></a>";
	}
	return $link;
}

function GetBanner($posizione,$conta='yes',$id=0) {
	$d = date( "Y-m-d");
	global $conn;
	if($posizione && !$id) {
		/*
			funzionamento normale, banner chiamato 
			specificando una posizione e non l'id
		*/
		$sql="SELECT * FROM 7banner WHERE (fl_stato='L' OR fl_stato='A') AND (dt_giorno1<='{$d}') AND (cd_posizione='{$posizione}') ORDER BY id_banner DESC";
	} else {
		/*
			funzionamento con id specifico
		*/
		$sql="SELECT * FROM 7banner WHERE  id_banner='".$id."'";
	}
	$result=$conn->query($sql) or die($conn->error."sql='$sql'<br>");
	if ($result->num_rows == 0) return "";
	// estrae la riga da far uscire e aggiorna la tabella
	$l=0;
	$primo = null;
	$vecchio = null;
	$esce = null;
	while ($r=$result->fetch_array()) {
		if($r['nu_maxday']>0 && $r['dt_maxday_date']==date("Y-m-d") && $r['nu_maxday_count']>$r['nu_maxday']) {
			/*
				se ho già fatto le visualizzazioni giornaliere
				previste allora skippo.
			*/


		} else {

			if (!$primo) $primo=$r;							// il primo estratto
			if ($vecchio && !$esce) $esce=$r;				// ho trovato l'ultimo
															// imposto quello da visualizzare
			if ($r["fl_stato"]=='L') {$vecchio=$r; $l++;}	// l'ultimo visualizzato, conto quanti hanno il flag ultimo visualizzato

		}
	}
	$result->free();
	if (!$esce) $esce=$primo; /* esce è il primo dopo L e se non c'e' è il primo */
	if (!$vecchio) $vecchio=$esce; /* se non c'e' l'ultimo visualizzato, allora l'ultimo è quello che esce */

	// SISTEMA L
	$fl='A';
	$gio=GetTimeStamp($vecchio["dt_giorno2"])-GetTimeStamp($d);
	if ($gio<0) $fl='S';
	if($vecchio['nu_maxtot']>0 && $vecchio['nu_maxtot']<=$vecchio['nu_pageviews']) $fl='S';
	if($vecchio['nu_maxclick']>0 && $vecchio['nu_maxclick']<=$vecchio['nu_clicks']) $fl='S';

	if(!$id) {
		/* rotazione flag e conteggio pagine viste */

		$sql1="UPDATE 7banner SET fl_stato='$fl' WHERE id_banner='".$vecchio["id_banner"]."'";
		$res1=$conn->query($sql1) or die($conn->error."sql1='$sql1'<br>");
		if (($vecchio["id_banner"]!=$esce["id_banner"])or($l==0)) {
			if($esce["id_banner"]) {
				$fl='L';
				$sql2="UPDATE 7banner SET fl_stato='$fl' WHERE id_banner=".$esce["id_banner"];
				$conn->query($sql2) or die($conn->error."sql2='$sql2'<br>");
			}
		}
		if($conta=='yes') {
			if($esce["id_banner"]) {
				$sql2b="UPDATE 7banner SET ";
				if($esce['dt_maxday_date']!=date("Y-m-d")) $sql2b.="dt_maxday_date='".date("Y-m-d")."',nu_maxday_count=0,";
				$sql2b.="
					nu_pageviews = nu_pageviews + 1,
					nu_maxday_count = nu_maxday_count + 1
					WHERE id_banner=".$esce["id_banner"];
				$conn->query($sql2b) or die($conn->error."sql2b='$sql2b'<br>");
			}
		}

		$id = $esce["id_banner"];

	} else {
		/*
			se ho l'id allora conteggio direttamente e non faccio la rotazione
		*/
		if($conta=='yes') {
			$sql2c="UPDATE 7banner SET ";
			if($esce['dt_maxday_date']!=date("Y-m-d")) $sql2.="dt_maxday_date='".date("Y-m-d")."',nu_maxday_count=0";
			$sql2c.="
				nu_pageviews = nu_pageviews + 1,
				nu_maxday_count = nu_maxday_count + 1
				WHERE id_banner='".$id."'";
			$conn->query($sql2c) or die($conn->error."sql2c='$sql2c'<br>");
		}
		$posizione = $esce['cd_posizione'];

	}
	// statistiche su 7banner_stats
	$q = execute_scalar("select id_day from 7banner_stats where id_day='".$d."' and cd_banner='".$id."' limit 0,1");
	if($q==$d) $conn->query("update 7banner_stats set nu_pageviews=nu_pageviews+1 where id_day='".$d."' and cd_banner='".$id."'");
		else $conn->query("insert ignore into 7banner_stats (id_day,nu_pageviews,nu_click,cd_banner) values ('$d',1,0,'".$id."')");
	return $esce;
}

function showBanner($posizione,$conta='yes',$id=0) {
	// se $conta='yes' allora aumenta il conteggio delle visualizzazioni
	// altrimenti non aumenta il conteggio. E' utilizzato per vedere se c'e'
	// magari un banner disponibile ma non devo mostrarlo realmente (quindi
	// non e' corretto conteggiarlo). E' il caso dell'overlayer.
	//
	if(!$id) {

		$f=GetBanner($posizione,$conta);

	} else {

		$f=GetBanner(null,"yes",$id);

	}

	if (!is_array($f)) return "";

	if ($f['de_codicescript']) {
		//
		// se è script, butto fuori lo script e sono
		// a posto.
		//
		// sostituzione di timestamp dinamico se presente nel codice per 
		// ridurre interferenze di cache
		$s = str_replace("[timestamp]",date("YmdHis"),$f['de_codicescript']);
		$s = str_replace("[RANDOM]",rand(10000,99999).date("YmdHis"),$s);
		$s = str_replace("[ID]",$f['id_banner'],$s);
		$s = str_replace("[LINK]",trim($f['de_url']),$s);
		$s = str_replace("[randnum]",rand(10000,99999).date("YmdHis"),$s);
	
	} else {

		$dir = BANNERIMAGES."/";
		$pics = loadbannerfile($dir,$f['id_banner'].'_', array('gif','png','jpg','zip'));


		$n_alternate = "";
		if(count($pics)==2) {
			/* 
				ci sono due immagini, una è l'swf e l'altra è jpg usata come placeholder
				DEPRECATO
				non passa mai qui
			
			$n = $pics[0];
			$n_alternate = $pics[1];
			$n_alternate = str_replace( $_SERVER['DOCUMENT_ROOT'],"//".$_SERVER['HTTP_HOST']."",$n_alternate);
			if(!preg_match($n,"/\.swf$/")) {
				$n_alternate = $pics[0];
				$n =  $pics[1];
			}
			*/
		} else {
			$n = array_pop( $pics );

		}
		$n = WEBURL . "/" .$n;
		//$n = str_replace( $_SERVER['DOCUMENT_ROOT'],"//".$_SERVER['HTTP_HOST']."",$n);
		if ($n!="") {
			$s = LinkBanner(
				$n,
				$f['nu_width'],$f['nu_height'],
				$n_alternate,
				preg_match("/\.zip$/i",$n) ? WEBURL."/data/dbimg/7banner/".$f['id_banner']."/index.html" : BANNERLINKER."?b=".$f['id_banner'],
				$f['de_target']
			);
		} else {
			$s = "";
		}
	}
	return $s;
}

function loadbannerfile($dir,$prenome,$arext) {
	$c = 0;
	//echo $dir;
	//die;
	$a=array();
	if (is_dir($dir) && $dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			$ext = substr(strrchr($file, '.'), 1);
			if(in_array($ext,$arext)) {
				if(strpos(" ".$file,$prenome)==1) {
					$a[$c]['nome']=$dir.$file;
					$p = (integer)preg_replace("/[^0-9]/","",stristr($file,"_"));
					$a[$c]['posizione']=$p;
					$c++;
				}
			}
		}
		closedir($dh);
	}
	$a = array_key_multi_sort($a,'posizione');
	$b = array();
	for($i=0;$i<count($a);$i++) $b[$i]=$a[$i]['nome'];
	return $b;
}

?>