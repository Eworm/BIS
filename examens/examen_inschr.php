<?php

// check login
session_start();
if (!isset($_GET['delId'])) { // Als uitschrijf-link geklikt, dan geen autorisatie nodig
	if (!isset($_SESSION['authorized_bis']) || $_SESSION['authorized_bis'] != 'yes') {
		header("Location: ./bis_login.php");
		exit();
	}
}

include_once("../include_globalVars.php");
include_once("../include_helperMethods.php");

$link = mysql_connect($database_host, $database_user, $database_pass);
if (!mysql_select_db($database, $link)) {
	echo 'Fout: database niet gevonden.';
	exit();
}

setlocale(LC_TIME, 'nl_NL');
?>

<!DOCTYPE html>
<html lang="nl">

    <head>
        <title>Examens - BIS</title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <link type="text/css" href="../css/bis.css" rel="stylesheet">
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-8">
<?php

// Uitschrijf-link geklikt
if (isset($_GET['delId'])) {
	$qr = mysql_query('SELECT Naam, Datum, Omschrijving 
			FROM examen_inschrijvingen 
			JOIN examens ON examen_inschrijvingen.Ex_ID = examens.ID
			WHERE UniekeHash = "' . $_GET['delId'] . '"');
	if (mysql_affected_rows() > 0) {
		$row = mysql_fetch_assoc($qr);
		$message = '<p>Kandidaat ' . $row['Naam'] . ' heeft zich uitgeschreven voor het examen ' . 
			$row['Omschrijving']  . ' op ' . DBdateToDate($row['Datum']) . '</p>';
		SendEmail("examens@hunze.nl", "Verwijderde examenaanmelding", $message);
		SendEmail("instructie@hunze.nl", "Verwijderde examenaanmelding", $message);
		mysql_query('DELETE FROM examen_inschrijvingen WHERE UniekeHash = "' . $_GET['delId'] . '"');
		echo "<p>Uw inschrijving is verwijderd en de Examencommissie is op de hoogte gesteld.<br>
			U kunt dit scherm nu sluiten.</p>";
		exit;
	} else {
		echo '<p>Onbekende inschrijving.</p>';
		exit;
	}
}

$id = $_GET['id'];

// init
if (!isset($_POST['cancel']) && !isset($_POST['insert'])) {
	$fail = false;
}

// knop gedrukt
if (isset($_POST['cancel'])) {
	unset($_POST['name'], $_POST['grade'], $_POST['age'], $_POST['email'], $_POST['telph'], $name, $grade, $email, $telph);
	$fail = false;
	echo "<h1>U wordt niet aangemeld</h1>";
	echo "<a href='index.php' class='btn btn-primary'>Terug naar het examenscherm</a></p>";
}

if (isset($_POST['insert'])) {
	$name = $_POST['name'];
	$grade = $_POST['grade'];
	$age = $_POST['age'];
	$email = $_POST['email'];
	$telph = $_POST['telph'];
	
	if (!CheckName($name)) {
		$fail_msg_name = "U dient een geldige voor- en achternaam op te geven. Let op: de apostrof (') wordt niet geaccepteerd.";
	}
	if (!$telph && !$email) {
		$fail_msg_contact = "U dient minimaal ofwel een telefoonnnummer ofwel een e-mailadres op te geven.";
	} else {
		if ($telph && !check_phone_dutch($telph)) {
			$fail_msg_telph = "U dient een geldig 10-cijferig telefoonnummer, met streepje, in te voeren.";
		}
		if ($email && !CheckEmail($email)) {
			$fail_msg_email = "U dient een geldig e-mailadres in te voeren.";
		}
	}
	
	if (isset($fail_msg_name) || isset($fail_msg_contact) || isset($fail_msg_telph) || isset($fail_msg_email)) {
		$fail = true;
	}
	
	if (!isset($fail) || $fail == false) {
		$hash = 0;
		while ($hash == 0) {
			 $hash = generateHash();
		}
		$query = "INSERT INTO `examen_inschrijvingen` (Naam, Graad, Leeftijd, Ex_ID, Email, TelNr, UniekeHash) VALUES ('$name', '$grade', '$age', '$id', '$email', '$telph', '$hash');";
		$result = mysql_query($query);
		if (!$result) {
			die("Inschrijven voor examen mislukt." . mysql_error());
		} else {
			$query2 = "SELECT Datum FROM `examens` WHERE ID='$id';";
			$result2 = mysql_query($query2);
			$row2 = mysql_fetch_assoc($result2);
			$date_db = DBdateToDate($row2['Datum']);
			
			// Mail kandidaat, met uitschrijflink
			if ($email) {
				$message = 'U bent aangemeld voor het examen op ' . $date_db . '<br>' .
					'Mocht u zich willen uitschrijven, klik dan <a href="' . $homepage . '/examens/examen_inschr.php?delId=' . $hash . '">hier</a>';
				SendEmail($email, "Bevestiging examenaanmelding", $message);
			}
			
			// Mail hotemetoten
			$message = "Naam: ".$name."<br>";
			$message .= "Leeftijd: ".$age."<br>";
			$message .= "Te behalen graad: ".$grade."<br>";
			$message .= "Op: " . $date_db . "<br>";
			$message .= "Telefoonnummer: ".$telph."<br>";
			$message .= "E-mailadres: ".$email."<br>";
			SendEmail("examens@hunze.nl", "Nieuwe examenaanmelding", $message);
			//SendEmail("instructie@hunze.nl", "Nieuwe examenaanmelding", $message); //21-4-2014 uitgezet op verzoek van Dagmar
			
			echo "<h1>Hartelijk dank voor uw aanmelding!</h1><br>Deze is doorgegeven aan de Examencommissie.<br>";
			echo "<a href='index.php' class='btn btn-primary'>Terug naar het examenscherm</a></p>";
			exit;
		}
	}
}

// Formulier
if ((!isset($_POST['insert']) && !isset($_POST['cancel'])) || !isset($fail) || $fail == true) {
	echo "<h1>Aanmeldformulier</h1>";
	echo '<form name="form" action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
	
	// naam
	echo "<div class='form-group'><label>Naam</label>";
	echo '<input type="text" name="name" value="' . (isset($name) ? $name : '') . '" class="form-control">';
	if (isset($fail_msg_name)) {
		echo '<div class="help-block">' . $fail_msg_name . '</div>';
	}
	echo "</div>";
	
	// leeftijdscategorie
	echo "<div class='form-group'><label>Leeftijdscategorie</label>";
	echo "<div class='radio'><label><input type=\"radio\" name=\"age\" value=\"jeugd t/m 14 jaar\" ";
	if (isset($age) && $age == 'jeugd t/m 14 jaar') {
		echo "checked='checked'";
	}
	echo "/>Jeugd t/m 14 jaar</label></div>";
	echo "<div class='radio'><label><input type=\"radio\" name=\"age\" value=\"junioren 15 t/m 18 jaar\" ";
	if (isset($age) && $age == 'junioren 15 t/m 18 jaar') {
		echo "checked='checked'";
	}
	echo "/>Junioren 15 t/m 18 jaar</label></div>";
	echo "<div class='radio'><label><input type=\"radio\" name=\"age\" value=\"senioren vanaf 18 jaar\" ";
	if (!isset($age) || $age == 'senioren vanaf 18 jaar') {
		echo "checked='checked'";
	}
	echo "/>Senioren vanaf 18 jaar</label></div>";
	echo "<div class='radio'><label><input type=\"radio\" name=\"age\" value=\"veteranen 50+\" ";
	if (isset($age) && $age == 'veteranen 50+') {
		echo "checked='checked'";
	}
	echo "/>Veteranen 50+</label></div>";
	echo "</div>";
	
	// graad
	echo "<div class='form-group'><label>Te behalen graad</label>";
	echo "<select name=\"grade\" class='form-control'>";
	$query = "SELECT Graden FROM examens WHERE ID='$id';";
	$grade_result = mysql_query($query);
	if (!$grade_result) {
		die("Ophalen van examengraden mislukt.".mysql_error());
	} else {
		if ($row = mysql_fetch_assoc($grade_result)) {
			$grades_db = $row[Graden];
			$grades = split(",", $grades_db);
			foreach($grades as $curr_grade) {
				echo "<option value=\"".$curr_grade."\" ";
				if (isset($grade) && $grade == $curr_grade) {
					echo "selected";
				}
				echo "/>".$curr_grade;
			}
		}
	}
	echo "</select>";
	echo "</div>";
	
	echo "<p>U dient minstens &eacute;&eacute;n van onderstaande velden in te vullen. Als u een e-mailadres opgeeft, ontvangt u een bevestiging per e-mail, met daarin een link die u kunt gebruiken mocht u uw inschrijving weer ongedaan willen maken. De gegevens worden niet op de examenpagina getoond, maar alleen doorgegeven aan de Examencommissie.</p>";
	
	// telefoonnr.
	echo "<div class='form-group'><label>Telefoonnummer (10 cijfers, met streepje)</label>";
	echo '<input type="text" name="telph" value="' . (isset($telph) ? $telph : '') . '" class="form-control">';
	if (isset($fail_msg_contact)) {
		echo '<div class="help-block">' . $fail_msg_contact . '</div>';
	} else {
		if (isset($fail_msg_telph)) {
			echo '<div class="help-block">' . $fail_msg_telph . '</div>';
		}
	}
	echo "</div>";
	
	// e-mail
	echo "<div class='form-group'><label>E-mailadres</label>";
	echo '<input type="text" name="email" value="' . (isset($email) ? $email : '') . '" class="form-control">';
	if (isset($fail_msg_email)) {
		echo '<div class="help-block">' . $fail_msg_email . '</div>';
	}
	echo "</div>";
	
	// knoppen
	echo "<div class='form-group'><input type=\"submit\" name=\"insert\" value=\"Inschrijven\" class='btn btn-primary'></div>";
	echo "</form>";
}
?>

        </div>
        
    </div>
    
</div>
</body>
</html>

<?php 
function generateHash() {
	$hash = generateUltraSecretActivationHash('67TYFGTYF%^RYGVNBS^&');
	$qr = mysql_query(sprintf('SELECT COUNT(*) AS hashCnt FROM examen_inschrijvingen WHERE UniekeHash = "%s"', $hash));
	if (mysql_affected_rows() > 0) {
		$row = mysql_fetch_assoc($qr);
		if ($row['hashCnt'] > 0) {
			return 0;
		}
	}
	return $hash;
}

function generateUltraSecretActivationHash($salt){
	$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$str = '';
	$count = strlen($charset);
	for($i=0;$i<65;$i++){
		mt_srand((double)microtime()*1000000);
		$str .= $charset[mt_rand(0, $count-1)];
	}

	$str .= $salt . time();

	for($i=0;$i<65;$i++){
		mt_srand((double)microtime()*1000000);
		$str .= $charset[mt_rand(0, $count-1)];
	}
	return md5($str);
}

?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
