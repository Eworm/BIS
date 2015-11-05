<?php
// check login
session_start();
if (!isset($_SESSION['authorized']) || $_SESSION['authorized'] != 'yes' || $_SESSION['restrict'] != 'excie') {
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

$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
if (isset($_GET['id'])) {
	$id = $_GET['id'];
}

if ($mode == "c" && isset($id)) {
	if ($_GET['curval'] == 1) {
		$query = "UPDATE examens SET ToonOpSite=0 WHERE ID=" . $id;
	} else {
		$query = "UPDATE examens SET ToonOpSite=1 WHERE ID=" . $id;
	}
	mysql_query($query);
	header('Location: admin_examens.php');
	exit;
}
if ($mode == "d" && isset($id)) {
	$query = "DELETE FROM examens WHERE ID=" . $id;
	mysql_query($query);
	header('Location: admin_examens.php');
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
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">


<h1>
    Examencommissie
    <a href='admin_examen_toev.php' class="btn btn-primary">Examen toevoegen</a>
</h1>

<?php
$query = "SELECT * FROM examens ORDER BY Datum DESC";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van examens mislukt: " . mysql_error());
}
echo "<br><table class=\"table\">";
echo "<tr><th><div>Datum</div></th><th><div>Omschrijving</div></th><th><div>Graden</div></th><th><div>Quotum</div></th><th><div>Open voor inschrijving</div></th><th colspan=4></th></tr>";
$c = 0;
while ($row = mysql_fetch_assoc($result)) {
	$id = $row['ID'];
	$date = $row['Datum'];
	$date_sh = DBdateToDate($date);
	$description = $row['Omschrijving'];
	$grades_db = $row['Graden'];
	$quotum = $row['Quotum'];
	$show = $row['ToonOpSite'];
	echo "<tr>";
	echo "<td><div>$date_sh</div></td>";
	echo "<td><div>$description</div></td>";
	echo "<td><div>$grades_db</div></td>";
	echo "<td><div>$quotum</div></td>";
	if ($show) {
		echo "<td><div>ja";
	} else {
		echo "<td><div>nee";
	}
	echo "&nbsp;[<a href='admin_examens.php?mode=c&curval=$show&id=$id'>Wijzig</a>]</div></td>";
	echo "<td><div><a href='admin_examen_toev.php?id=$id'>Wijzigen</a></div></td>";
	echo "<td><div><a href='admin_examens.php?mode=d&id=$id'>Verwijderen</a></div></td>";
	echo "<td><div><a href='admin_examinandi.php?id=$id'>Bekijk/beheer deelnemers</a></div></td>";
	echo "</tr>";
}
echo "</table>";
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
