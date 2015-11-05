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

if (isset($_GET['id'])) {
	$id = $_GET['id']; // wijzigen bestaand examen
	if ($id < 0 || !is_numeric($id)) {
		echo "Er is iets misgegaan.";
		exit();
	}
	$grades = array();
	$query = "SELECT * FROM `examens` WHERE ID=" . $id;
	$result = mysql_query($query);
	if ($result) {
		if (mysql_affected_rows($link) > 0) {
			$row = mysql_fetch_assoc($result);
			$date_db = $row['Datum'];
			$date = DBdateToDate($date_db);
			$quotum = $row['Quotum'];
			$description = $row['Omschrijving'];
			$grades_db = $row['Graden'];
			$grades = split(",", $grades_db);
		}
	}
}

// init
if (!isset($_POST['cancel']) && !isset($_POST['insert'])) {
	$fail = false;
}

// knop gedrukt
if (isset($_POST['cancel'])) {
	unset($_POST['date'], $_POST['quotum'], $_POST['description'], $date, $quotum, $description);
	$fail = false;
	echo "<p>Invoer examen geannuleerd.<br><a href='admin_examens.php'>Terug naar de examenpagina</a></p>";
}

if (isset($_POST['insert'])) {
	$date = $_POST['date'];
	$date_db = DateToDBdate($date);
	$description = $_POST['description'];
	$grades_db = '';
	$query = "SELECT Roeigraad FROM roeigraden WHERE Examinabel=1 ORDER BY ID;";
	$grade_result = mysql_query($query);
	if (!$grade_result) {
		die("Ophalen van examengraden mislukt: " . mysql_error());
	} else {
		$first_time = false;
		while ($row = mysql_fetch_assoc($grade_result)) {
			$curr_grade = $row['Roeigraad'];
			if (array_key_exists($curr_grade, $_POST) && $_POST[$curr_grade] == "true") {
				if ($first_time == false) {
					$first_time = true;
				} else {
					$grades_db .= ",";
				}
				$grades_db .= $curr_grade;
			}
		}
	}
	$quotum = $_POST['quotum'];
	if ($quotum <= 0 || !is_numeric($quotum)) {
		$fail_msg_quotum = "U dient een aantal groter dan 0 op te geven.";
	}
	if ($id) {
		$query = "SELECT COUNT(*) AS NrOfExi FROM `examen_inschrijvingen` WHERE Ex_ID=" . $id;
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$nr_of_exi = $row['NrOfExi'];
		if ($nr_of_exi > $quotum) {
			$fail_msg_quotum = "Het quotum mag niet lager zijn dan het aantal reeds ingeschreven kandidaten.";
		}
	}
	
	if (isset($fail_msg_quotum)) {
		$fail = true;
	} else{
		if ($id) {
			$query = "UPDATE `examens` SET Datum='$date_db', Omschrijving='$description', Graden='$grades_db', Quotum='$quotum' WHERE ID='$id';";
		} else {
			$query = "INSERT INTO `examens` (Datum, Omschrijving, Graden, Quotum, ToonOpSite) VALUES ('$date_db', '$description', '$grades_db', '$quotum', '1');";
		}
		$result = mysql_query($query);
		if (!$result) {
			die("toevoegen/wijzigen examen mislukt: " . mysql_error());
		} else {
			echo "<p>Examen succesvol toegevoegd/gewijzigd.<br><a href='admin_examens.php'>Terug naar de examenpagina</a></p>";
		}
	}
}

// Formulier
if ((!isset($_POST['insert']) && !isset($_POST['delete']) && !isset($_POST['cancel'])) || (isset($fail) && $fail == true)) {
	echo "<p><b>Examen toevoegen/wijzigen</b></p>";
	echo "<form name='form' action='" . $_SERVER['REQUEST_URI'] . "' method='post'>";
	
	// datum
	echo "<div class='form-group'><label>Datum</label>";
	echo "<input type='text' name='date' id='date' class='form-control' maxlength='10' value='" . (isset($date) ? $date : '') . "'>";
	echo "</div>";
	
	// omschrijving
	echo "<div class='form-group'><label>Omschrijving (max. 45 tekens)</label>";
	echo "<input type='text' name='description' value='" . (isset($description) ? $description : '') . "' class='form-control'>";
	echo "</div>";
	
	// te behalen graden
	echo "<div class='form-group'><label>Te behalen graden</label>";
	$query = "SELECT Roeigraad FROM roeigraden WHERE Examinabel=1 ORDER BY ID;";
	$grade_result = mysql_query($query);
	if (!$grade_result) {
		die("Ophalen van examengraden mislukt: " . mysql_error());
	} else {
		while ($row = mysql_fetch_assoc($grade_result)) {
			$curr_grade = $row['Roeigraad'];
			echo "<div class='checkbox'><label><input type='checkbox' name='" . $curr_grade . "' value='true' ";
			if (isset($grades) && in_array($curr_grade, $grades)) {
				echo "checked='checked'";
			}
			echo "/>" . $curr_grade . "</label></div>";
		}
	}
	echo "<td></td></tr>";
	
	// quotum
	echo "<div class='form-group'><label>Quotum</label>";
	echo "<input type='text' name='quotum' value='" . (isset($quotum) ? $quotum : '') . "' class='form-control'>";
	if (isset($fail_msg_quotum)) {
		echo "<div class='alert'>" . $fail_msg_quotum . "</div>";
	}
	echo "</div>";
	
	// knoppen
	echo "<input type=\"submit\" name=\"insert\" value=\"Toevoegen\" class='btn btn-primary'> ";
	echo "</form>";
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
