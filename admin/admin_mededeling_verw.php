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
                
        <div class="col-md-12">

<?php

$id = $_GET['id'];
$mode = $_GET['mode'];

if ($mode == "Dearch") {
	$source = "mededelingen_oud";
	$target = "mededelingen";
} else {
	$source = "mededelingen";
	$target = "mededelingen_oud";
}

if ($mode == "Del") {
	$query = "DELETE FROM mededelingen WHERE ID='$id';";
	$result = mysql_query($query);
	if (!$result) {
		die("Verwijderen mislukt.". mysql_error());
	} else {
		echo "Mededeling succesvol verwijderd.<br>";
	}
} else {
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
			echo "Mededeling succesvol ge(de)archiveerd.<br>";
		}
	}
}

mysql_close($link);

?>

        </div>
        
    </div>
    
</div>
</body>
</html>
