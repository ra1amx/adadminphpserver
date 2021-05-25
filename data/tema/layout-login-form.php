<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

	<link rel="stylesheet" type="text/css" href="##root##src/template/stile.css?##rand##"> <!-- stili comuni -->
	<link rel="stylesheet" type="text/css" href="##root##data/##DOMINIO##/stile.css"/> <!-- stili del tema -->

	##JQUERYINCLUDE##
	<script language="JavaScript" src="##root##src/template/comode.js?##rand##"></script>

	<script>
	jQuery(document).ready(function($) {
		//...qui...
		setTimeout(function(){
		show("alertBox");},2000);
	} );
	</script>
		<style>
		#promo {position:fixed;right:0;bottom:0;display:inline-block;padding:5px 10px}
	</style>

	<title>LOGIN</title>
</head>
<body onload="document.forms[0].##usernamevar##.focus();">

	<form method="post" action="##actionurl##" id='loginform' name='loginform'>
		<table>
			<tr>
				<td class="logo">
				<img src="##LOGO##" id="logo">
				AdAdmin ##VER##</td>
			</tr>
			<tr>
				<td>User<br/>
				<input name="##usernamevar##" type="text" maxlength="20" class='f'></td>
			</tr>
			<tr>
				<td>Password<br/>
				<input name="##passwordvar##" type="password" maxlength="20" class='f' onkeypress="submitonenter('loginform',event,this)"></td>
			</tr>
			<tr>
				<td><input type="button" value="Entra" id='login' onclick='document.forms[0].submit()'></td>
			</tr>
			<tr>
				<td>
				<div class="message">##msg##</div>
				<br><br>
				<a href="##root##src/resetpassword.php" ##hiderecover##>&raquo; Forgot password?</a>
				</td>
		</table>
	</form>


		<?php
			if($_SERVER['HTTP_HOST']=="www.barattalo.it") {
				?>
				<div id='alertBox' style='display:none;position:absolute;top:50px;left:50px;'>
					<h1>NOTE FOR GUEST USERS</h1>
					<p>You can access AdAdmin back office as a guest users, with these credentials: user: <code>gu</code> password: <code>gu</code><br>
					Buy it here: <a href="http://codecanyon.net/item/adadmin-easy-adv-server/12710605?rel=ginoplusio"><u>CodeCanyon</u></a><br>
					Example frontend output: <a href="http://www.barattalo.it/adadmin/example/adadmin-frontend.html"><u>http://www.barattalo.it/adadmin/example/adadmin-frontend.html</u></a>
					<a id="closeBtn" href="javascript:;" onclick="$('#alertBox').hide();" style='float:right'>OK</a>
					</p>
				</div>
				<?php
			}
		?>
	<a href='https://codecanyon.net/item/adadmin-easy-adv-server/12710605' id='promo'>This software is available on CodeCanyon</a>

</body>
</html>