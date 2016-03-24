<?php
// check login
session_start();
if (!isset($_SESSION['authorized_bis']) || $_SESSION['authorized_bis'] != 'yes') {
	header("Location: ./bis_login.php");
	exit();
}

include_once("../include_globalVars.php");
include_once("../include_helperMethods.php");

$link = mysql_connect($database_host, $database_user, $database_pass);
if (!mysql_select_db($database, $link)) {
	echo "Fout: database niet gevonden.<br>";
	exit();
}

setlocale(LC_TIME, 'nl_NL');
?>

<!DOCTYPE html>
<html lang="nl">

    <head>
        <title>Cursussen - BIS</title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <link type="text/css" href="../css/bis.css" rel="stylesheet">
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-8">

<?php

$id = $_GET['id'];
$query = "SELECT * FROM cursussen WHERE ID='$id';";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van cursusgegevens mislukt.".mysql_error());
} else {
	$rows_aff = mysql_affected_rows($link);
	if ($rows_aff > 0) {
		$row = mysql_fetch_assoc($result);
		$startdate = $row['Startdatum'];
		$startdate_sh = strtotime($exstartdate);
		$type = $row['Type'];
		$description = $row['Omschrijving'];
		$org_email = $row['Mailadres'];
	} else {
		die("Ophalen van cursusgegevens mislukt.");
	}
}
$skiff2 = false;
if (preg_match("/skiff-2/", $type) || preg_match("/Skiff-2/", $type) || preg_match("/skiff 2/", $type) || preg_match("/Skiff 2/", $type)) $skiff2 = true;

// init
if (!$_POST['cancel'] && !$_POST['insert']) {
	$fail = FALSE;
}

// knop gedrukt
if ($_POST['cancel']){
	unset($_POST['name'], $_POST['demand'], $_POST['email'], $_POST['telph'], $name, $demand, $email, $telph);
	$fail = FALSE;
	echo "<h1>U wordt niet aangemeld.</h1>";
	echo "<a href='index.php' class='btn btn-primary'>Terug naar het cursusscherm</a></p>";
}

if ($_POST['insert']){
	$name = $_POST['name'];
	$demand = $_POST['demand'];
	$email = $_POST['email'];
	$telph = $_POST['telph'];
	
	if (!CheckName($name)) {
		$fail_msg_name = "U dient een geldige voor- en achternaam op te geven. Let op: de apostrof (') wordt niet geaccepteerd.";
	}
	if ($skiff2 && !$demand) {
		$fail_msg_demand = "U dient op te geven hoe u aan de instructie-eis voldaan heeft.";
	}
	if (!$telph || !$email) {
		$fail_msg_contact = "U dient zowel een telefoonnnummer als een e-mailadres op te geven.";
	} else {
		if (!check_phone_dutch($telph)) {
			$fail_msg_telph = "U dient een geldig 10-cijferig telefoonnummer, met streepje, in te voeren.";
		}
		if (!CheckEmail($email)) {
			$fail_msg_email = "U dient een geldig e-mailadres in te voeren.";
		}
	}
	
	if ($fail_msg_name || $fail_msg_demand || $fail_msg_contact || $fail_msg_telph || $fail_msg_email) $fail = TRUE;
	
	if (!$fail) {
		$query = "INSERT INTO `cursus_inschrijvingen` (Naam, Demand, Ex_ID, Email, TelNr) VALUES ('$name', '$demand', '$id', '$email', '$telph');";
		$result = mysql_query($query);
		if (!$result) {
			die("Inschrijven voor cursus mislukt.".mysql_error());
		} else {
			$intro = "Beste cursist,<br><br>Bedankt voor uw aanmelding. Wij hebben onderstaande gegevens ontvangen en nemen z.s.m. per email contact met u op. U ontvangt dan nadere informatie omtrent de cursus.<br><br>Met vriendelijke groet,<br>De Instructiecommissie<br><br>KGR De Hunze<br>Praediniussingel 32<br>9711 AG Groningen<br><br>www.hunze.nl<br><br>";
			$message = "Naam: ".$name."<br>";
			$query2 = "SELECT Startdatum, Type FROM `cursussen` WHERE ID='$id';";
			$result2 = mysql_query($query2);
			$row2 = mysql_fetch_assoc($result2);
			$startdate_db = $row2['Startdatum'];
			$type = $row2['Type'];
			$message .= "Cursus: ".$type."<br>";
			$message .= "Beginnend op: ".DBdateToDate($startdate_db)."<br>";
			if ($demand) $message .= "Tegenprestatie: ".$demand."<br>";
			if ($telph) $message .= "Telefoonnummer: ".$telph."<br>";
			if ($email) $message .= "E-mailadres: ".$email."<br>";
			// Verstuur naar cursist zelf
			if ($email) SendEmail($email, "Bevestiging cursusaanmelding", $intro.$message);
			// Verstuur naar organisatie
			if ($org_email != "instructie@hunze.nl") SendEmail($org_email, "Nieuwe cursusaanmelding", $message);
			SendEmail("instructie@hunze.nl", "Nieuwe cursusaanmelding", $message);
			echo "<h1>Hartelijk dank voor uw aanmelding!</h1><p>Deze is doorgegeven aan het betreffende lid van de Instructiecommissie.<br>Als u zelf een e-mailadres had opgegeven, krijgt u een kopie van uw inschrijving via e-mail.<br>";
			echo "<a href='index.php' class='btn btn-primary'>Terug naar het cursusscherm/a></p>";
		}
	}
}

// Formulier
if ((!$_POST['insert'] && !$_POST['cancel']) || $fail) {
	echo "<h1>Aanmeldformulier voor ".$type." beginnend op ".strftime('%A %d-%m-%Y', $startdate_sh)."&nbsp;".$description . "</h1>";
	echo "<form name='form' action=\"$REQUEST_URI\" method=\"post\">";
	
	// naam
	echo "<div class='form-group'><label>Naam</label>";
	echo "<input type=\"text\" name=\"name\" value=\"$name\" class='form-control'>";
	if ($fail_msg_name) echo "<div class='help-block'>$fail_msg_name</div>";
	echo "</div>";
	
	// tegenprestatie (alleen bij skiff-2)
	if ($skiff2) {
		echo "<h3>Om deel te kunnen nemen aan de cursus skiff-2, dient u instructie gegeven te hebben. Omschrijf a.u.b. kort welke instructie u hebt gegeven, wanneer en bij wie.</h3>";
		echo "<div class='form-group'><label>Instructie-eis</label>";
		echo "<input type=\"text\" name=\"demand\" value=\"$demand\" class='form-control' maxlength=\"100\">";
		if ($fail_msg_demand) echo "<div class='help-block'>$fail_msg_demand</div>";
		echo "</div>";
	}
	
	echo "<p>U dient beide onderstaande velden in te vullen. De gegevens worden niet op de cursuspagina getoond, maar alleen doorgegeven aan de Instructiecommissie.</p>";
	
	// telefoonnr.
	echo "<div class='form-group'><label>Telefoonnummer (10 cijfers, met streepje)</label>";
	echo "<input type=\"text\" name=\"telph\" value=\"$telph\" class='form-control'>";
	if ($fail_msg_contact) {
		echo "<div class='help-block'>$fail_msg_contact</div>";
	} else {
		if ($fail_msg_telph) echo "<div class='help-block'>$fail_msg_telph</div>";
	}
	echo "</div>";
	
	// e-mail
	echo "<div class='form-group'><label>E-mailadres</label>";
	echo "<input type=\"text\" name=\"email\" value=\"$email\" class='form-control'>";
	if ($fail_msg_email) echo "<div class='help-block'>$fail_msg_email</div>";
	echo "</div>";
	
	// knoppen
	echo "<div class='form-group'><input type=\"submit\" name=\"insert\" value=\"Inschrijven\" class='btn btn-primary'></div>";
	echo "</form>";
}

mysql_close($link);

?>

        </div>

    </div>
    
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

</body>
</html>
