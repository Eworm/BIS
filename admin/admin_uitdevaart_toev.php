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

setlocale(LC_TIME, 'nl_NL');
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
                
        <div class="col-md-12">

<?php

$fail = false;

$boot_id = $_GET['id'];
$query = "SELECT Naam FROM boten WHERE ID=$boot_id;";
$result = mysql_query($query);
$row = mysql_fetch_assoc($result);
$name = $row['Naam'];

$reason = "Uit de vaart";

if (isset($_POST['cancel'])) {
	echo "<p>Er zal niets worden aangemaakt.</p>";
	exit();
}

if (isset($_POST['submit'])) {
	// startdatum
	$startdate = $_POST['startdate'];
	if (CheckTheDate($startdate)) {
		$startdate_db = DateToDBdate($startdate);
	} else {
		$fail_msg_startdate = "U dient een geldige startdatum op te geven.";
	}
	
	// einddatum
	$enddate = $_POST['enddate'];
	if (!$enddate) {
		$enddate_db = '';
	} else {
		if (CheckTheDate($enddate)) {
			$enddate_db = DateToDBdate($enddate);
		} else {
			$fail_msg_enddate = "U dient of dit veld leeg te laten of een geldige einddatum op te geven.";
		}
	}
	
	// datumvolgorde
	if ($enddate_db != '') {
		if (strtotime($enddate_db) < strtotime($startdate_db)) {
			$fail_msg_date = "De einddatum dient na de begindatum te liggen.";
		}
	}
	
	// geen check op reden
	$reason = $_POST['reason'];
	
	// als niet gefaald, Uit de Vaart toevoegen
	if (isset($fail_msg_startdate) || isset($fail_msg_enddate) || isset($fail_msg_date)) {
		$fail = true;
	} else {
		if ($enddate_db != '') {
			$query = "INSERT INTO uitdevaart (Boot_ID, Startdatum, Einddatum, Reden, Verwijderd) VALUES ('" . $boot_id . "', '" . $startdate_db . "', '" . $enddate_db . "', '" . $reason . "', 0);"; 
		} else {
			$query = "INSERT INTO uitdevaart (Boot_ID, Startdatum, Reden, Verwijderd) VALUES ('" . $boot_id . "', '" . $startdate_db . "', '" . $reason . "', 0);"; 
		}
		$result = mysql_query($query);
		if (!$result) {
			die("toevoegen mislukt.". mysql_error());
		} else {
			echo "Uit de Vaart succesvol ingevoerd.";
			// mensen mailen die deze boot hadden ingeschreven
			$datepart_query = "";
			if ($enddate_db != '') {
				$datepart_query = "AND Datum <= '$enddate_db' ";
			}
			$query2 = "SELECT Email, Datum, Begintijd FROM ".$opzoektabel." WHERE Boot_ID = '$boot_id' AND Datum >= '$startdate_db' ".$datepart_query." AND Spits=0 AND Verwijderd=0;";
			$result2 = mysql_query($query2);
			if ($result2) {
				while ($row = mysql_fetch_assoc($result2)) {
					$email_to = $row['Email'];
					$db_datum = $row['Datum'];
					$date_tmp = strtotime($db_datum);
					$date_sh = strftime('%A %d-%m-%Y', $date_tmp);
					$starttijd = $row['Begintijd'];
					$message = "Uw inschrijving van $date_sh vanaf ".substr($starttijd, 0, 5)." komt te vervallen omdat '$name' zojuist uit de vaart gemeld is.";
					SendEmail($email_to, "Wijziging inschrijving", $message);
				}
				echo "<br>Degenen die hadden ingeschreven, zijn via e-mail op de hoogte gesteld.";
			}
		}
	}
}

// HET FORMULIER
if ((!isset($_POST['submit']) && !isset($_POST['cancel'])) || $fail) {
	
	echo '<form name="form" action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
	
	// startdatum
	if (isset($fail_msg_date)) echo "<div class='alert alert-danger'>$fail_msg_date</div>";
	
	echo "<div class='form-group'><label>Startdatum</label>";
	echo "<input type='text' name='startdate' id='startdate'  class='form-control datepicker' maxlength='10' value='" . (isset($startdate) ? $startdate : '') . "'>";
	if (isset($fail_msg_startdate)) echo "<td>$fail_msg_startdate</td>";
	echo "</div>";
	
	// evt. einddatum
	echo "<div class='form-group'><label>Einddatum, of leeg</label>";
	echo "<input type='text' name='enddate' id='enddate'  class='form-control datepicker' maxlength='10' value='" . (isset($enddate) ? $enddate : '') . "'>";
	if (isset($fail_msg_enddate)) echo "<td>$fail_msg_enddate</td>";
	echo "</div>";
	
	// reden
	echo "<div class='form-group'><label>Reden</label>";
	echo "<input type=\"text\" name=\"reason\" value=\"$reason\" class='form-control'>";
	echo "</div>";
	
	// knoppen
	echo "<div class='form-group'><input type=\"submit\" name=\"submit\" value=\"Toevoegen\" class='btn btn-primary'></div>";
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

<script>

function changeInfo(){
	return true;
}

</script>
