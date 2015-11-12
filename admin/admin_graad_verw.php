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
                
        <div class="col-md-12">
<?php

echo "<p><strong>Welkom in de admin van BIS</strong> [<a href='./admin_graden.php'>Terug naar roeigradenmenu</a>] [<a href='./admin_logout.php'>Uitloggen</a>]</p>";

$id = $_GET['id'];

$query = "DELETE FROM `roeigraden` WHERE ID='$id';";
$result = mysql_query($query);
if (!$result) {
	die("Verwijderen roeigraad mislukt.". mysql_error());
} else {
	echo "Verwijderen roeigraad gelukt.<br>";
}

mysql_close($link);

?>

        </div>
        
    </div>
    
</div>
</body>
</html>
