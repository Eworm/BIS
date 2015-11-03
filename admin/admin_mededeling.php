<?php
// check login
session_start();
if (!isset($_SESSION['authorized']) || $_SESSION['authorized'] != 'yes') {
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

<?php
  
    $mode = $_GET['mode'];
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">
            
            <h1>
                Mededelingen
                <a href='./admin_mededeling_toev.php' class='btn btn-primary'>Mededeling toevoegen</a>
                
                <?php
                    if (!$mode) {
                    	echo "<a href='admin_mededeling.php?mode=Arch' class='btn btn-default'>Gearchiveerde mededelingen</a>";
                    } else {
                    	echo "<a href='admin_mededeling.php' class='btn btn-default'>Actuele mededelingen</a>";
                    }
                ?>
            </h1>

<?php


$source = "mededelingen";
if ($mode) $source .= "_oud";
$query = "SELECT * from ".$source." ORDER BY Datum DESC;";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van mededelingen mislukt.". mysql_error());
}
echo "<br><table class=\"table\">";
echo "<tr><th><div>Datum</div></th><th><div>Bestuurslid</div></th><th><div>Betreft</div></th><th><div>Mededeling</div></th><th><div>&nbsp;</div></th>";
if (!$mode) echo "<th><div>&nbsp;</div></th><th><div>&nbsp;</div></th>";
echo "</tr>";

$c = 0;
while ($row = mysql_fetch_assoc($result)) {
	$id = $row['ID'];
	$date_db = $row['Datum'];
	$date = DBdateToDate($date_db);
	$name = $row['Bestuurslid'];
	$summary = $row['Betreft'];
	$note = $row['Mededeling'];
	echo "<tr>";
	echo "<td><div>$date</div></td>";	
	echo "<td><div>$name</div></td>";
	echo "<td><div>$summary</div></td>";
	echo "<td width=400px><div style=\"text-align:left overflow:auto\">$note</div></td>";
	if (!$mode) echo "<td><div><a href=\"./admin_mededeling_toev.php?id=$id\">Wijzigen</a></div></td>";
	if ($mode) {
		echo "<td><div><a href='admin_mededeling_verw.php?id=$id&mode=Dearch'>De-archiveer</a></div></td>";
	} else {
		echo "<td><div><a href='admin_mededeling_verw.php?id=$id&mode=Arch'>Archiveer</a></div></td>";
		echo "<td><div><a href='admin_mededeling_verw.php?id=$id&mode=Del'>Verwijder</a></div></td>";
	}
	echo "</tr>";
	$c++;
}
echo "</table>";

mysql_close($link);

?>

        </div>
        
        <div class="col-md-3">
            
            <div class="well">
                
                <strong>Welkom in de admin-sectie van BIS</strong>
                <br><br>
                <a href='./admin_logout.php' class="btn btn-primary">Uitloggen</a>
                
            </div>
            
        </div>
        
    </div>
    
</div>
</body>
</html>
