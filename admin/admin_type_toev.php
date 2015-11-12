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

// ingeval van editen bestaand boottype
$type_ex = $_GET['type'];
$query = "SELECT * FROM `types` WHERE Type='$type_ex' LIMIT 1;";
$result = mysql_query($query);
if ($result) {
	$rows_aff = mysql_affected_rows($link);
	if ($rows_aff > 0) {
		$row = mysql_fetch_assoc($result);
		$type = $row['Type'];
		$cat = $row['Categorie'];
		$sort = $row['Roeisoort'];
	}
}

// init
if (!$_POST['cancel'] && !$_POST['insert']) {
	$fail = FALSE;
}

// knop gedrukt
if ($_POST['cancel']){
	unset($_POST['type'], $_POST['cat'], $_POST['sort'], $type, $cat, $sort);
	$fail = FALSE;
}

if ($_POST['insert']){
	$type = $_POST['type'];
	$cat = $_POST['cat'];
	$sort = $_POST['sort'];
	if ($type_ex) {
		$query = "UPDATE `types` SET Type='$type', Categorie='$cat', Roeisoort='$sort' WHERE Type='$type_ex';";
	} else {
		$query = "INSERT INTO `types` (Type, Categorie, Roeisoort) VALUES ('$type', '$cat', '$sort');";
	}
	$result = mysql_query($query);
	if (!$result) {
		die("toevoegen/wijzigen boottype mislukt.". mysql_error());
	} else {
		echo "<p>Boottype succesvol toegevoegd/gewijzigd.</p>";
	}
}

// Formulier
if ((!$_POST['insert'] && !$_POST['delete'] && !$_POST['cancel']) || $fail) {
	echo "<h1>Boottype toevoegen/wijzigen</h1>";
	echo "<form name='form' action=\"$REQUEST_URI\" method=\"post\">";
	
	// naam
	echo "<div class='form-group'><label>Type</label>";
	echo "<input type=\"text\" name=\"type\" value=\"$type\" class='form-control' autofocus>";
	echo "</div>";
	
	// categorie
	echo "<div class='form-group'><label>Categorie</label>";
	echo "<input type=\"text\" name=\"cat\" value=\"$cat\"  class='form-control'><div class='help-block'>Meerdere types kunnen deel uitmaken van dezelfde categorie</div>";
	echo "</div>";
	
	// roeisoort
	echo "<div class='form-group'><label>Roeisoort (boord/scull)</label>";
	echo "<input type=\"text\" name=\"sort\" value=\"$sort\" class='form-control'></td>";
	echo "</div>";
	
	// knoppen
	echo "<div class='form-group'><input type=\"submit\" name=\"insert\" value=\"Toevoegen\" class='btn btn-primary'></div> ";
	echo "</form>";
}

mysql_close($link);

?>

        </div>
        
    </div>
    
</div>
</body>
</html>
