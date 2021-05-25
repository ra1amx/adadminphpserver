<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

	<link rel="stylesheet" type="text/css" href="##root##src/template/stile.css?##rand##"> <!-- stili comuni -->
	<link rel="stylesheet" type="text/css" href="##root##data/##DOMINIO##/stile.css"/> <!-- stili del tema -->

	##JQUERYINCLUDE##
	<script language="JavaScript" src="##root##src/template/comode.js?##rand##"></script>


	<title>POOR BOY, YOU LOSE YOUR KEY</title>

	<script>

	$(document).ready(function() {
		$('tr').each(function(){
			if(!$(this).is(":visible")) $(this).remove();
		});
	} );

	</script>

	<style>
		p {text-align:center;padding-bottom:20px}
	</style>

</head>
<body onload="document.forms[0].email.focus();">

	<form method="post" action="##actionurl##" id='loginform' name='loginform'>
		<p>
			#ciao#
		</p>
		<input name="code" type="hidden" value="##code##">
		<table ##hideall##>
			<tr ##show##>
				<td>Email</td>
				<td><input name="email" type="text" maxlength="200" class='f' value="##email##"></td>
			</tr>
			<tr ##hide##>
				<td>New Password</td>
				<td><input name="pass1" type="password" maxlength="20" class='f'></td>
			</tr>
			<tr ##hide##>
				<td>Repeat Password</td>
				<td><input name="pass2" type="password" maxlength="20" class='f'></td>
			</tr>
			<tr>
				<td></td>
				<td align="right"> &nbsp;<input type="button" value="Reset" id='login' onclick='document.forms[0].submit()'></td>
			</tr>
			<tr>
				<td colspan='2'>
				<div class="message">##msg##</div>
				</td>
		</table>
	</form>
</body>
</html>