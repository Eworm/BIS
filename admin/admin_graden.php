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
                Overzicht roeigraden
                <a href='./admin_graad_toev.php' class='btn btn-primary'>Roeigraad toevoegen</a>
            </h1>

<?php

$query = "SELECT * from roeigraden ORDER BY ID;";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van roeigraden mislukt.". mysql_error());
}
echo "<br><table class=\"table\">";
echo "<tr><th><div>Roeigraad</div></th><th><div>Zichtbaar in BIS?</div></th><th><div>Achtergrondkleur in BIS-botentabel</div></th><th><div>Kan examen in worden gedaan?</div></th><th colspan=2><div>&nbsp;</div></th></tr>";

$c = 0;
while ($row = mysql_fetch_assoc($result)) {
	$id = $row['ID'];
	$grade = $row['Roeigraad'];
	$show = $row['ToonInBIS'];
	$color = $row['KleurInBIS'];
	$exable = $row['Examinabel'];
	echo "<tr>";
	echo "<td><div>$grade</div></td>";
	echo "<td><div>";
	if ($show) {
		echo "ja";
	} else {
		echo "nee";
	}
	echo "</div></td>";
	echo "<td><div>$color</div></td>";
	echo "<td><div>";
	if ($exable) {
		echo "ja";
	} else {
		echo "nee";
	}
	echo "</div></td>";
	echo "<td><div><a href=\"./admin_graad_toev.php?id=$id\">Wijzigen</a></div></td>";
	echo "<td><div><a href='admin_graad_verw.php?id=$id'>Verwijderen</a></div></td>";
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
