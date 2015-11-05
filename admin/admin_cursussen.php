<?php
// check login
session_start();
if (!isset($_SESSION['authorized']) || $_SESSION['authorized'] != 'yes') {
	header("Location: admin_login.php");
	exit();
}

include_once('../include_globalVars.php');
include_once('../include_helperMethods.php');

$link = mysql_connect($database_host, $database_user, $database_pass);
if (!mysql_select_db($database, $link)) {
	die('Fout: database niet gevonden.');
}

$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($mode == "c") {
	if ($_GET['curval'] == 1) {
		$query = 'UPDATE cursussen SET ToonOpSite=0 WHERE ID=' . $id;
	} else {
		$query = 'UPDATE cursussen SET ToonOpSite=1 WHERE ID=' . $id;
	}
	mysql_query($query);
	header('Location: admin_cursussen.php');
	exit;
}
if ($mode == "d") {
	$query = 'DELETE FROM cursussen WHERE ID=' . $id;
	$result = mysql_query($query);
	if (!$result) {
		die('Verwijderen van cursus mislukt: ' . mysql_error());
	}
	echo "Verwijderen van cursus gelukt.<br>";
	echo "<a href='admin_cursussen.php'>Terug naar de cursuspagina&gt;&gt;</a>";
	exit;
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
        
        <script type="text/javascript" src="../scripts/kalender.js"></script>
        <script type="text/javascript" src="../scripts/sortable.js"></script>
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">

<h1>
    Instructiecommissie
    <a href='admin_cursus_toev.php' class="btn btn-primary">Maak een nieuwe cursus aan</a>
</h1>

<?php
setlocale(LC_TIME, 'nl_NL');

$query = "SELECT * FROM cursussen ORDER BY Startdatum DESC";
$result = mysql_query($query);
if (!$result) {
	die('Ophalen van cursussen mislukt: ' . mysql_error());
}
echo "<br><table class=\"basis\" border=\"1\" cellpadding=\"6\" cellspacing=\"0\" bordercolor=\"#AAB8D5\">";
echo "<tr><th><div>Startdatum</div></th><th><div>Einddatum</div></th><th><div>Type</div></th><th><div>Omschrijving</div></th><th><div>Mailadres</div></th><th><div>Quotum</div></th><th><div>Toon op site?</div></th><th colspan=4></th></tr>";
$c = 0;
while ($row = mysql_fetch_assoc($result)) {
	$id = $row['ID'];
	$startdate = $row['Startdatum'];
	$startdate_sh = DBdateToDate($startdate);
	$enddate = $row['Einddatum'];
	$enddate_sh = DBdateToDate($enddate);
	$type = $row['Type'];
	$description = $row['Omschrijving'];
	$email = $row['Mailadres'];
	$quotum = $row['Quotum'];
	$show = $row['ToonOpSite'];
	echo "<tr>";
	echo "<td><div>$startdate_sh</div></td>";
	echo "<td><div>$enddate_sh</div></td>";
	echo "<td><div>$type</div></td>";
	echo "<td><div>$description</div></td>";
	echo "<td><div>$email</div></td>";
	echo "<td><div>$quotum</div></td>";
	if ($show) {
		echo "<td><div>ja";
	} else {
		echo "<td><div>nee";
	}
	echo "&nbsp;[<a href='admin_cursussen.php?mode=c&curval=$show&id=$id'>Wijzig</a>]</div></td>";
	echo "<td><div><a href='admin_cursus_toev.php?id=$id'>Wijzigen</a></div></td>";
	echo "<td><div><a href='admin_cursussen.php?mode=d&id=$id'>Verwijderen</a></div></td>";
	echo "<td><div><a href='admin_cursisten.php?id=$id'>Bekijk/beheer deelnemers</a></div></td>";
	echo "</tr>";
}
echo "</table>";
?>

<?php include 'admin_footer.php'; ?>
