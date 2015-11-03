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
                Overzicht boottypes
                <a href='./admin_type_toev.php' class='btn btn-primary'>Boottype toevoegen</a>
            </h1>

<?php

$query = "SELECT * from types;";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van boottypes mislukt.". mysql_error());
}
echo "<br><table class=\"table\">";
echo "<tr><th><div>Type</div></th><th><div>Categorie</div></th><th><div>Roeisoort</div></th><th colspan=2><div>&nbsp;</div></th></tr>";

while ($row = mysql_fetch_assoc($result)) {
	$type = $row['Type'];
	$cat = $row['Categorie'];
	$sort = $row['Roeisoort'];
	echo "<tr>";
	echo "<td><div>$type</div></td>";
	echo "<td><div>$cat</div></td>";
	echo "<td><div>$sort</div></td>";
	echo "<td><div><a href=\"./admin_type_toev.php?type=$type\">Wijzigen</a></div></td>";
	echo "<td><div><a href='admin_type_verw.php?type=$type'>Verwijderen</a></div></td>";
	echo "</tr>";
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
