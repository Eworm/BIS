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

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">
            
            <h1>
                Botenoverzicht
                <a href='./admin_boot_toevoegen.php' class='btn btn-primary'>Boot toevoegen</a>
            </h1>

<?php


$query = "SELECT ID, Naam, Gewicht, Type, Roeigraad from boten WHERE Datum_eind IS NULL ORDER BY Naam;";
$boats_result = mysql_query($query);
if (!$boats_result) {
	die("Ophalen van boten-informatie mislukt.". mysql_error());
}
echo "<br><table class=\"table\">";
echo "<tr><th><div>Naam</div></th><th><div>Gewicht</div></th><th><div>Type</div></th><th><div>Roeigraad</div></th><th><div>Status</div></th><th colspan=3><div>Aanpassen</div></th></tr>";
$c = 0;
while ($row = mysql_fetch_assoc($boats_result)) {
	$id = $row['ID'];
	$name = $row['Naam'];
	$name_tmp = addslashes($name);
	$weight = $row['Gewicht'];
	$type = $row['Type'];
	$type_plus = preg_replace('/\+/', 'plus', $type); // +tekens redden bij overdracht via GET
	$grade = $row['Roeigraad'];
	echo "<tr>";
	echo "<td><div>$name</div></td>";	
	echo "<td><div>$weight</div></td>";
	echo "<td><div>$type</div></td>";
	echo "<td><div>$grade</div></td>";
	
	// in/uit de vaart
	echo "<td><div>";
	$query2 = sprintf('SELECT * 
			FROM uitdevaart 
			WHERE Verwijderd=0 
			AND Boot_ID=%d 
			AND Startdatum<="%s" 
			AND (Einddatum="0" OR Einddatum="0000-00-00" OR Einddatum IS NULL OR Einddatum>="%s")', 
				$id, $today_db, $today_db);
	$result2 = mysql_query($query2);
	if (!$result2) {
		die("Ophalen van Uit de Vaart-informatie mislukt.". mysql_error());
	} else {
		$rows_aff = mysql_affected_rows($link);
		if ($rows_aff > 0) {
			echo "UIT";
		} else {
			echo "IN";
		}
	}
	echo " de vaart</div></td>";
	// einde in/uit de vaart
	
	echo "<td><div><a href=\"./admin_inuitdevaart.php?id=$id\">In/uit de vaart</a>&nbsp;&nbsp;&nbsp;</div></td>";
	echo "<td><div><a href=\"./admin_boot_toevoegen.php?id=$id\">Wijzigen</a>&nbsp;&nbsp;&nbsp;</div></td>";
	echo "<td><div><a href=\"./admin_boot_verwijderen.php?id=$id\">Verwijderen</a></div></td>";
	echo "</tr>";
	$c++;
}
echo "</table>";

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
