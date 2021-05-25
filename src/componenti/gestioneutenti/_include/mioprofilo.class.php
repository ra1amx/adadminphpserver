<?php
/*
	classe per la gestione dei miei dati personali.
*/

class Mioprofilo {

	var $uploadDir; //contiene la cartella in cui uploadare i file.
					//percorso dalla root.

	var $gestore;


	function __construct () {
		global $session,$root;
		$this->gestore = $_SERVER["PHP_SELF"];

		checkAbilitazione("MIOPROFILO");

	}


	/*
		mostra il dettaglio.
		ritorna 0 se l'utente non è abilitato, altrimenti restituisce l'html.
	*/
	function getDettaglio() {

		global $session,$root;

		$id = $session->get("idutente");

		if ($session->get("MIOPROFILO")) {
			/*
				modifica
			*/
			$dati = $this->getDati($id);
			$extra = $this->getDatiExtra($id);

			$action = "modificaStep2";



			$html = loadTemplateAndParse("template/dettaglio.html");

			//costruzione form
			$objform = new form();
			//$objform->pathJsLib = $root."template/controlloform.js";

			$nome = new testo("nome",($dati["nome"]),100,100);
			$nome->obbligatorio=1;
			$nome->label="'Name'";
			$objform->addControllo($nome);

			$cognome = new testo("cognome",($dati["cognome"]),100,100);
			$cognome->obbligatorio=1;
			$cognome->label="'Surname'";
			$objform->addControllo($cognome);


			$cr = new cryptor();
			$password = new password("password",($cr->decrypta($dati["password"])),20,20);
			$password->obbligatorio=1;
			$password->label="'Password'";
			$objform->addControllo($password);

			$de_email = new testo("de_email",htmlspecialchars(isset($extra["de_email"]) ? $extra["de_email"] :"" ),200,30);
			$de_email->obbligatorio=1;
			$de_email->label="'Email address'";
			$objform->addControllo($de_email);


//			$fileimg1 = new fileupload('fileimg1');
//			$fileimg1->showlink=true;
//			$fileimg1->obbligatorio=1;
//			$fileimg1->label="'File'";
//			$objform->addControllo($fileimg1);
//			$fileimg1->value="";
//
//			/* se c'e' il file lo metto nell'oggetto per farlo vedere */
//			if($id) {
//				if(file_exists($this->uploadDir.$id."foto.jpg")){
//					$fileimg1->value=$this->uploadDir.$id."foto.jpg";
//				}
//			}


			$id = new hidden("id",$dati["id"]);
			$op = new hidden("op",$action);

			//$submit = new submit("invia","salva");

			$html = str_replace("##STARTFORM##", $objform->startform(), $html);
			$html = str_replace("##id##", $id->gettag(), $html);
			$html = str_replace("##op##", $op->gettag(), $html);
			//$html = str_replace("##submit##", $submit->gettagimage($root."images/salva.gif"," Salva"), $html);
			$html = str_replace("##username##", $dati['username'], $html);
			$html = str_replace("##password##", $password->gettag(), $html);
			$html = str_replace("##nome##", $nome->gettag(), $html);
			$html = str_replace("##cognome##", $cognome->gettag(), $html);
			$html = str_replace("##de_email##", $de_email->gettag(), $html);
//			$html = str_replace("##fileimg1##", $fileimg1->gettag(), $html);
			$html = str_replace("##gestore##", $this->gestore, $html);
			$html = str_replace("##ENDFORM##", $objform->endform(), $html);


		} else {
			$html = "0";
		}
		return $html;
	}

	function getDati($id) {
		return execute_row("SELECT * from frw_utenti where id='{$id}'");
	}
	function getDatiExtra($id) {
		$sql = "SELECT * from frw_extrauserdata where cd_user='{$id}'";
		$dati = execute_row($sql);
		if(!isset($dati['cd_user']))  $dati['cd_user'] = 0;
		return $dati;
	}

	function update($arDati,$files) {
		// in:
		// arDati--> array _POST del form
		// files --> array _FILES
		// risultato:
		//	"" --> ok
		//  "0" --> il tuo profilo non ti consente l'inserimento/modifica
		//  "2|messaggio" --> errore file

		global $session,$conn;
		if ($session->get("MIOPROFILO")) {
			if ($arDati["id"]!="") {
				$id = $session->get("idutente");
				/*
					Modifica
				*/
				$cr = new cryptor();
				$sql="UPDATE frw_utenti set nome='##nome##',cognome='##cognome##',
					password='##password##'
					where id='##id##'";
				$sql= str_replace("##nome##",$arDati["nome"],$sql);
				$sql= str_replace("##cognome##",$arDati["cognome"],$sql);
				$sql= str_replace("##password##",$cr->crypta($arDati["password"]),$sql);
				$sql= str_replace("##id##",$arDati["id"],$sql);
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");
				$html= "";

				$dt_datacreazione = execute_scalar("select dt_datacreazione from frw_extrauserdata where cd_user={$arDati['id']}");

				$sql="delete from frw_extrauserdata where cd_user={$arDati['id']}";
				$conn->query($sql) or die($conn->error."sql='$sql'<br>");

				$sql="INSERT into frw_extrauserdata (de_email,cd_user,dt_datacreazione) values('##de_email##',##id_user##,'##dt_datacreazione##')";
				$sql= str_replace("##de_email##",$arDati["de_email"],$sql);
				$sql= str_replace("##dt_datacreazione##",$dt_datacreazione,$sql);
				$sql= str_replace("##id_user##",$arDati["id"],$sql);

				$conn->query($sql) or die($conn->error."sql='$sql'<br>");


			}

//			if($html=="") {
//				/* upload */
//				$html = $this->uploadImageFile(
//					$files,
//					'fileimg1',
//					$this->uploadDir.$id."foto.jpg",
//					array(	'image/jpeg',
//							'image/pjpeg')
//				);
//			}

		} else {
			$html="0";		//il tuo profilo non ti consente l'inserimento
		}
		return $html;
	}

//	function uploadImageFile($files,$campo,$uploadfile,$allowedArray) {
//		/* ----------------------------------------------------- */
//		$msg = "";										//output
//		if($files[$campo]['type']!="") {
//
//			if( !in_array($files[$campo]['type'],$allowedArray) ) {
//				/*
//					tipo file errato
//				*/
//				$msg = "Sono permesse solo immagini formato JPG.";
//			} else {
//				/*
//					tipo ok, completo upload
//				*/
//				$arDatiImg = GetImageSize($files[$campo]['tmp_name']);
//				if($arDatiImg[0]>$this->maxXimg || $arDatiImg[1]>$this->maxYimg) {
//					/*
//						dimensioni errate
//					*/
//					$msg = "Sono permesse solo immagini grandi al massimo {$this->maxXimg}x{$this->maxYimg}.";
//				} else {
//					/*
//						dimensioni ok.
//					*/
//					if(is_uploaded_file($_FILES[$campo]['tmp_name'])) {
//						if(filesize($_FILES[$campo]['tmp_name'])>$this->maxKBimg*1024) {
//							/*
//								troppo pesante
//							*/
//							$msg = "File troppo grande, massimo {$this->maxKBimg}kb.";
//						} else {
//							/*
//								ok
//							*/
//							if (move_uploaded_file($_FILES[$campo]['tmp_name'], $uploadfile)) {
//								// tutto ok
//								$msg = "";
//							} else {
//								//attack
//								$msg = "File non uploadato (2).";
//							}
//						}
//					} else {
//						/*
//						ko
//						*/
//						$msg = "File non uploadato (1).";
//					}
//				}
//			}
//			if($msg!="") {
//				$msg = "2|{$msg}";
//			}
//		}
//		/* ------------------------------------------------------ */
//		return $msg;
//	}

}

?>