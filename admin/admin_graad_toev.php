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
        <link type="text/css" href="../css/bis.css" rel="stylesheet">
            	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-6">
<?php

// ingeval van editen bestaande roeigraad
$id = $_GET['id'];
$query = "SELECT * FROM `roeigraden` WHERE ID='$id' LIMIT 1;";
$result = mysql_query($query);
if ($result) {
	$rows_aff = mysql_affected_rows($link);
	if ($rows_aff > 0) {
		$row = mysql_fetch_assoc($result);
		$grade = $row['Roeigraad'];
		$show = $row['ToonInBIS'];
		$color = $row['KleurInBIS'];
		$exable = $row['Examinabel'];
	} else {
		$color = "#FFFF99"; // standaard geel kleurtje
	}
}

// init
if (!$_POST['cancel'] && !$_POST['insert']) {
	$fail = FALSE;
}

// knop gedrukt
if ($_POST['cancel']){
	unset($_POST['grade'], $_POST['show'], $_POST['color'], $_POST['exable'], $grade, $show, $color, $exable);
	$fail = FALSE;
}

if ($_POST['insert']){
	$grade = $_POST['grade'];
	$show = $_POST['show'];
	$color = $_POST['color'];
	$exable = $_POST['exable'];
	if ($id) {
		$query = "UPDATE `roeigraden` SET Roeigraad='$grade', ToonInBIS='$show', KleurInBIS='$color', Examinabel='$exable' WHERE ID='$id';";
	} else {
		$query = "SELECT MAX(ID) AS MaxID FROM `roeigraden`;";
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$id = $row['MaxID'] + 1;
		$query = "INSERT INTO `roeigraden` (ID, Roeigraad, ToonInBIS, KleurInBIS, Examinabel) VALUES ('$id', '$grade', '$show', '$color', '$exable');";
	}
	$result = mysql_query($query);
	if (!$result) {
		die("toevoegen/wijzigen roeigraad mislukt.". mysql_error());
	} else {
		echo "<p>Roeigraad succesvol toegevoegd/gewijzigd.</p>";
	}
}

// Formulier
if ((!$_POST['insert'] && !$_POST['delete'] && !$_POST['cancel']) || $fail) {
	echo "<h1>Roeigraad toevoegen/wijzigen</h1>";
	echo "<form name='form' action=\"$REQUEST_URI\" method=\"post\">";
	
	// naam
	echo "<div class='form-group'><label>Roeigraad</label>";
	echo "<input type=\"text\" name=\"grade\" value=\"$grade\" class='form-control' autofocus></td>";
	echo "</div>";
	
	// functie
	echo "<div class='checkbox'><label>";
	echo "<input type=\"checkbox\" name=\"show\" value=1 ";
	if ($show == 1) echo "CHECKED";
	echo ">Zichtbaar in BIS?";
	echo "</label></div>";
	
	// mail
	echo "<div class='form-group'><label>Achtergrondkleur in BIS-botentabel</label>";
	echo "<input type=\"text\" name=\"color\" value=\"$color\"  class='form-control'></td>";
	echo "</div>";
	
	// MPB
	echo "<div class='checkbox'><label>";
	echo "<input type=\"checkbox\" name=\"exable\" value=1 ";
	if ($exable == 1) echo "CHECKED";
	echo "/>Kan examen in worden gedaan?</label>";
	echo "</div>";
	
	// knoppen
	echo "<p><input type=\"submit\" name=\"insert\" value=\"Toevoegen\" class='btn btn-primary'> ";
	echo "</form>";
}

mysql_close($link);

?>

            <br><br>
            
        </div>
        
    </div>
    
</div>
</body>
</html>
