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
        <link type="text/css" href="../css/bis.css" rel="stylesheet">
            	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-12">
<?php

$mode = $_GET['mode'];
$id = $_GET['id'];
$part_id = $_GET['part_id'];

if ($mode == "d" && $part_id) {
	$query = "DELETE FROM cursus_inschrijvingen WHERE ID='$part_id';";
	$result = mysql_query($query);
	if (!$result) {
		die("Verwijderen van deelnemer mislukt.". mysql_error());
	}
	echo "Verwijderen van deelnemer gelukt.<br>";
	echo "<a href='admin_cursisten.php?id=$id'>Terug naar de deelnemerspagina</a>";
	exit;
}

echo "<p>Deelnemers</p>";

$query = "SELECT * FROM cursus_inschrijvingen WHERE Ex_ID='$id';";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van kandidaten mislukt.". mysql_error());
}
echo "<br><table class=\"table\">";
echo "<tr><th><div>Naam</div></th><th><div>Tegenprestatie</div></th><th><div>Telefoon</div></th><th><div>E-mail</div></th><th></th></tr>";
$c = 0;
while ($row = mysql_fetch_assoc($result)) {
	$part_id = $row['ID'];
	$name = $row['Naam'];
	$demand = $row['Demand'];
	$telph = $row['TelNr'];
	$email = $row['Email'];
	echo "<tr>";
	echo "<td><div>$name</div></td>";
	echo "<td><div>$demand</div></td>";
	echo "<td><div>$telph</div></td>";
	echo "<td><div>$email</div></td>";
	echo "<td><div><a href='admin_cursisten.php?mode=d&id=$id&part_id=$part_id' class='btn btn-danger'>Verwijder</a></div></td>";
	echo "</tr>";
}
echo "</table>";

mysql_close($link);

?>

            <br><br>

        </div>
                
    </div>
    
</div>
</body>
</html>
