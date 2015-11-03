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

<?php

if ($_GET['id']) { // bestaande boot
	$id = $_GET['id'];
	$query = "SELECT * FROM `boten` WHERE ID='$id';";
	$result = mysql_query($query);
	if (!$result) {
		die("Ophalen bootinformatie mislukt. ". mysql_error());
	} else {
		$row = mysql_fetch_assoc($result);
		$naam = $row['Naam'];
		$gewicht = $row['Gewicht'];
		$type = $row['Type'];
		$roeigraad = $row['Roeigraad'];
	}
} else {
	// Default gewicht = '-'
	$gewicht = "-";
}

// init
if (!$_POST['cancel'] && !$_POST['insert']) {
	$fail = FALSE;
}

// knop gedrukt
if ($_POST['cancel']){
	unset($_POST['naam'], $_POST['gewicht'], $_POST['type'], $_POST['roeigraad'], $naam, $gewicht, $type, $roeigraad);
	$fail = FALSE;
}

if ($_POST['insert']){
	$naam_lb = $_POST['naam'];
	$naam = addslashes($naam_lb);
	$gewicht = $_POST['gewicht'];
	$type = $_POST['type'];
	$roeigraad = $_POST['roeigraad'];
	$datum_start = $today_db;
	if ($id) { // wijziging in bestaande boot
		$query = "UPDATE `boten` SET Naam='$naam', Gewicht='$gewicht', Type='$type', Roeigraad='$roeigraad' WHERE ID=$id;";
		$result = mysql_query($query);
		if (!$result) {
			die("Wijzigen $naam_lb mislukt.". mysql_error());
		} else {
			echo "<p>$naam_lb succesvol gewijzigd.</p>";
		}
	} else { // bij nieuwe boot, check op unieke naam-type-combi (incl. historie!)
		$query = "SELECT * FROM `boten` WHERE Naam='$naam' AND Type='$type';";
		$result = mysql_query($query);
		if (!$result) {
			die("Controleren naam $naam_lb mislukt. ". mysql_error());
		} else {
			$rows_aff = mysql_affected_rows($link);
			if ($rows_aff > 0) {
				echo "<p>$naam_lb van type $type niet uniek en dus niet toegestaan. Hierbij wordt ook gekeken naar namen van afgevoerde boten, sinds september 2009.</p>";
			} else {
				$query = "INSERT INTO `boten` (Naam, Gewicht, Type, Roeigraad, Datum_start) VALUES ('$naam', '$gewicht', '$type', '$roeigraad', '$datum_start');";
				$result = mysql_query($query);
				if (!$result) {
					die("Toevoegen $naam_lb mislukt. ". mysql_error());
				} else {
					echo "<p>$naam_lb succesvol toegevoegd.</p>";
				}
			}
		}
	}
}

// Formulier
if ((!$_POST['insert'] && !$_POST['delete'] && !$_POST['cancel']) || $fail) {
	echo "<h1>Boot toevoegen/wijzigen</h1>";
	echo "<form name='form' action=\"$REQUEST_URI\" method=\"post\">";
	
	// naam
	echo "<div class='form-group'><label>Naam</label>";
	echo "<input type=\"text\" name=\"naam\" value=\"$naam\" class='form-control' autofocus />";
	echo "<div class='help-block'>Svp alleen gewone letters en geen leestekens of apostrof gebruiken</div>";
	echo "</div>";
	
	// gewicht
	echo "<div class='form-group'><label>Gewicht</label>";
	echo "<input type=\"text\" name=\"gewicht\" value=\"$gewicht\" class='form-control' />";
	echo "</div>";
	
	// type
	echo "<div class='form-group'><label>Type</label>";
	echo "<select name=\"type\" class='form-control'>";
	$query = "SELECT Type from types;";
	$result = mysql_query($query);
	if (!$result) {
		die("Ophalen van boottypes mislukt.". mysql_error());
	}
	$c = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$boottype = $row['Type'];
		echo "<option value=\"$boottype\" ";
		if ($type == $boottype) echo "selected=\"selected\"";
		echo ">$boottype</option>";
		$c++;
	}
	echo "</select></div>";
		
	// roeigraad
	echo "<div class='form-group'><label>Roeigraad</label>";
	echo "<select name=\"roeigraad\" class='form-control'>";
	$query = "SELECT Roeigraad FROM roeigraden WHERE ToonInBIS=1 ORDER BY ID;";
	$result = mysql_query($query);
	if (!$result) {
		die("Ophalen van roeigraden mislukt.". mysql_error());
	}
	$c = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$grade = $row['Roeigraad'];
		echo "<option value=\"$grade\" ";
		if ($roeigraad == $grade) echo "selected=\"selected\"";
		echo ">$grade</option>";
		$c++;
	}
	echo "</select></div>";
	
	// knoppen
	echo "<div class='form-group'><input type=\"submit\" name=\"insert\" value=\"toevoegen\" class='btn btn-primary' /></div>";
	echo "</form>";
}

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
