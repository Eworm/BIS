<?php
include_once("../include.php");

extract ($_REQUEST);
if (isset($login) && $login == $loginnaam && isset($password) && $password == $loginwachtwoord) {
	session_start();
	$_SESSION['authorized_bis'] = 'yes';
	header("Location: ./index.php");
	exit();
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title><? echo $systeemnaam; ?> - Login</title>
    <link type="text/css" href="../<? echo $csslink; ?>" rel="stylesheet" />
</head>
<body>
	<div style="position:absolute; top:30%; left:30%">
	<h1>Inloggen op het <? echo $systeemnaam; ?></h1>
	<form method="post" action="../documenten/bis_login.php">
		<table>
		<tr>
		  <td>Login-naam:</td>
		  <td><input type="text" name="login" value="<? echo $loginnaam; ?>" /></td>
		  <td><em><? echo $loginnaam_hulp; ?></em></td>
		</tr>
		<tr>
		  <td>Wachtwoord:</td><td><input type="password" name="password" /></td>
		  <td><em><? echo $loginwachtwoord_hulp; ?></em></td>
		</tr>
		<tr>
		  <td>&nbsp;</td>
		  <td><input type="submit" value="Inloggen" /></td>
		  <td>&nbsp;</td>
		</tr>
	</table>
	</form>
	</div>
</body>
</html>