<?php
// check login
session_start();
if (!isset($_SESSION['authorized']) || $_SESSION['authorized'] != 'yes' || $_SESSION['restrict'] != 'gebcie') {
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
        
        <script type="text/javascript" src="../scripts/sortable.js"></script>
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">
            
<?php


$id = $_GET['id'];
$mode = $_GET['mode'];

if ($mode) {
	$source = "schades_gebouw_oud";
	$target = "schades_gebouw";
} else {
	$source = "schades_gebouw";
	$target = "schades_gebouw_oud";
}

$query = "INSERT INTO ".$target." SELECT * FROM ".$source." WHERE ID='$id';";
$result = mysql_query($query);
if (!$result) {
	die("(De-)archiveren mislukt.". mysql_error());
} else {
	$query2 = "DELETE FROM ".$source." WHERE ID='$id';";
	$result2 = mysql_query($query2);
	if (!$result2) {
		die("De-)archiveren mislukt.". mysql_error());
	} else {
		echo "Schade succesvol ge(de)archiveerd.<br>";
		echo "<a href='admin_schade_gebouw.php?mode=$mode'>Terug naar de werkstroom</a></p>";
	}
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
