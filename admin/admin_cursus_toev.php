<?php
// check login
session_start();
if (!isset($_SESSION['authorized']) || $_SESSION['authorized'] != 'yes' || $_SESSION['restrict'] != 'instrcie') {
	header("Location: admin_login.php");
	exit();
}

include_once("../include_globalVars.php");
include_once("../include_helperMethods.php");

$link = mysql_connect($database_host, $database_user, $database_pass);
if (!mysql_select_db($database, $link)) {
	echo "Fout: database niet gevonden.<br>";
	exit();
}

?>

<!DOCTYPE html>
<html lang="nl">
    
    <head>
        <title>Admin - <?php echo $systeemnaam; ?></title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">
<?php

$id = $_GET['id']; // wijzigen bestaande cursus
if ($id && ($id < 0 || !is_numeric($id))) { // check op ID
	echo "Er is iets misgegaan.";
	exit();
}
$query = "SELECT * FROM `cursussen` WHERE ID='$id';";
$result = mysql_query($query);
if ($result) {
	$rows_aff = mysql_affected_rows($link);
	if ($rows_aff > 0) {
		$row = mysql_fetch_assoc($result);
		$startdate_db = $row['Startdatum'];
		$startdate = DBdateToDate($startdate_db);
		$enddate_db = $row['Einddatum'];
		$enddate = DBdateToDate($enddate_db);
		$quotum = $row['Quotum'];
		$description = $row['Omschrijving'];
		$email = $row['Mailadres'];
		$type = $row['Type'];
	} else {
		$email = "instructie@hunze.nl"; // default
	}
} else {
	$email = "instructie@hunze.nl"; // default
}

// init
if (!$_POST['cancel'] && !$_POST['insert']) {
	$fail = FALSE;
}

// knop gedrukt
if ($_POST['cancel']){
	unset($_POST['startdate'], $_POST['enddate'], $_POST['quotum'], $_POST['description'], $_POST['email'], $_POST['type'], $startdate, $enddate, $quotum, $description, $email, $type);
	$fail = FALSE;
	echo "<p>Invoer/wijziging cursus geannuleerd.<br><a href='admin_cursussen.php'>Terug naar de cursuspagina</a></p>";
}

if ($_POST['insert']){
	$startdate = $_POST['startdate'];
	$startdate_db = DateToDBdate($startdate);
	$enddate = $_POST['enddate'];
	$enddate_db = DateToDBdate($enddate);
	$description = $_POST['description'];
	$email = $_POST['email'];
	if (!$email) $email = "instructie@hunze.nl";
	if (!CheckEmail($email)) $fail_msg_email = "U dient een geldig e-mailadres op te geven";
	$type = $_POST['type'];
	$quotum = $_POST['quotum'];
	if ($quotum <= 0 || !is_numeric($quotum)) $fail_msg_quotum = "U dient een aantal groter dan 0 op te geven.";
	
	if ($id) {
		$query = "SELECT COUNT(*) AS NrOfExi FROM `cursus_inschrijvingen` WHERE Ex_ID='$id'";
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$nr_of_exi = $row['NrOfExi'];
		if ($nr_of_exi > $quotum) $fail_msg_quotum = "Het quotum mag niet lager zijn dan het aantal reeds ingeschreven cursisten.";
	}
	
	if ($fail_msg_quotum || $fail_msg_email) {
		$fail = TRUE;
	} else{
		if ($id) {
			$query = "UPDATE `cursussen` SET Startdatum='$startdate_db', Einddatum='$enddate_db', Type='$type', Omschrijving='$description', Mailadres='$email', Quotum='$quotum' WHERE ID='$id';";
		} else {
			$query = "INSERT INTO `cursussen` (Startdatum, Einddatum, Type, Omschrijving, Mailadres, Quotum, ToonOpSite) VALUES ('$startdate_db', '$enddate_db', '$type', '$description', '$email', '$quotum', '1');";
		}
		$result = mysql_query($query);
		if (!$result) {
			die("toevoegen/wijzigen cursus mislukt.". mysql_error());
		} else {
			echo "<p>Cursus succesvol toegevoegd/gewijzigd.<br><a href='admin_cursussen.php'>Terug naar de cursuspagina</a></p>";
		}
	}
}

// Formulier
if ((!$_POST['insert'] && !$_POST['delete'] && !$_POST['cancel']) || $fail) {
	echo "<p><b>Cursus toevoegen/wijzigen</b></p>";
	echo "<form name='form' action=\"$REQUEST_URI\" method=\"post\">";
	echo "<table>";
	
	// data
	echo "<tr><td>Startdatum (dd-mm-jjjj):</td>";
	echo "<td><input type='text' onchange='' name='startdate' id='startdate' size='8' maxlength='10' value='$startdate'>";
	echo "&nbsp;<a href=\"javascript:show_calendar('form.startdate');\" onmouseover=\"window.status='Kalender';return true;\" onmouseout=\"window.status='';return true;\"><img src='../res/kalender.gif' alt='kalender' width='19' height='17' border='0'></a></td>";
	echo "</tr>";
	echo "<tr><td>Einddatum (dd-mm-jjjj):</td>";
	echo "<td><input type='text' onchange='' name='enddate' id='enddate' size='8' maxlength='10' value='$enddate'>";
	echo "&nbsp;<a href=\"javascript:show_calendar('form.enddate');\" onmouseover=\"window.status='Kalender';return true;\" onmouseout=\"window.status='';return true;\"><img src='../res/kalender.gif' alt='kalender' width='19' height='17' border='0'></a></td>";
	echo "</tr>";
	
	// type
	echo "<tr><td>Type cursus (max. 45 tekens):</td>";
	echo "<td><input type=\"text\" name=\"type\" value=\"$type\" size=45 /></td>";
	echo "</tr>";
	
	// omschrijving
	echo "<tr><td>Nadere omschrijving (max. 45 tekens):</td>";
	echo "<td><input type=\"text\" name=\"description\" value=\"$description\" size=45 /></td>";
	echo "</tr>";
	
	// email
	echo "<tr><td>Mailadres, indien afwijkend van instructie@hunze.nl:</td>";
	echo "<td><input type=\"text\" name=\"email\" value=\"$email\"/></td>";
	if ($fail_msg_email) echo "<td><em>".$fail_msg_email."</em></td>";
	echo "</tr>";
	
	// quotum
	echo "<tr><td>Quotum:</td>";
	echo "<td><input type=\"text\" name=\"quotum\" value=\"$quotum\" size=3 /></td>";
	if ($fail_msg_quotum) echo "<td><em>".$fail_msg_quotum."</em></td>";
	echo "</tr>";
	
	// knoppen
	echo "</table>";
	echo "<input type=\"submit\" name=\"insert\" value=\"Toevoegen\" /> ";
	echo "</form>";
}

mysql_close($link);

?>


        </div>
        
        <div class="col-md-3">
            
            <div class="well">
                
                <strong>Welkom in de admin van BIS</strong>
                <br><br>
                <a href='./admin_logout.php' class="btn btn-primary">Uitloggen</a>
                
            </div>
            
        </div>
        
    </div>
    
</div>
</body>
</html>

<script language="javascript">

function changeInfo(){
	return true;
}

</script>
