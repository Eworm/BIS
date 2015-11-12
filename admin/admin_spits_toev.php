<?php
// check login
session_start();
if (!isset($_SESSION['authorized']) || $_SESSION['authorized'] != 'yes') {
	header("Location: admin_login.php");
	exit();
}

include_once("../include_globalVars.php");
include_once("../include_boardMembers.php");
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
        <link type="text/css" href="../bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
    	
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

$spits_id = 0;
if (isset($_GET['id'])) {
	$spits_id = $_GET['id'];
	$query = "SELECT MPB, Datum, Begintijd, Eindtijd, Boot_ID, Pnaam, Ploegnaam, Email from ".$opzoektabel." WHERE Spits=$spits_id ORDER BY Datum;";
	$result = mysql_query($query);
	if (!$result) {
		die("Ophalen van informatie mislukt.". mysql_error());
	} else {
		// uit eerste record kun je alles al halen, behalve -bij meer dan 1 inschrijving- de einddatum
		$row = mysql_fetch_assoc($result);
		$mpb = $row['MPB'];
		$startdate = $row['Datum'];
		$startdate = DBdateToDate($startdate);
		$start_time = $row['Begintijd'];
		$start_time_fields = explode(":", $start_time);
		$start_time_hrs = $start_time_fields[0];
		$start_time_mins = $start_time_fields[1];
		$end_time = $row['Eindtijd'];
		$end_time_fields = explode(":", $end_time);
		$end_time_hrs = $end_time_fields[0];
		$end_time_mins = $end_time_fields[1];
		$boat_id = $row['Boot_ID'];
		// bootnaam
		$query_boatname = "SELECT Naam from boten WHERE ID=$boat_id;";
		$result_boatname = mysql_query($query_boatname);
		$row_boatname = mysql_fetch_assoc($result_boatname);
		$boat = $row_boatname['Naam'];
		//
		$pname = $row['Pnaam'];
		$name = $row['Ploegnaam'];
		$email = $row['Email'];
		$enddate = $row['Datum'];
		while ($row = mysql_fetch_assoc($result)) {
			$enddate = $row['Datum'];
		}
		$enddate = DBdateToDate($enddate);
	}
}

if (isset($_POST['cancel'])) {
	echo "<p>Er zal niets worden aangemaakt/gewijzigd.</p>";
	exit();
}

if (isset($_POST['submit'])) {
	// bestuurslid
	$mpb = $_POST['mpb'];
	if (!$mpb) $fail_msg_mpb = "U dient uw functie te selecteren.";
	
	// startdatum
	$startdate = $_POST['startdate'];
	if (CheckTheDate($startdate)) {
		$startdate_db = DateToDBdate($startdate);
		if (strtotime($startdate_db) - strtotime($today_db) <= (24 * 3600)) {
			$fail_msg_startdate = "De startdatum moet minstens 2 dagen na vandaag liggen.";
		} 
	} else {
		$fail_msg_startdate = "U dient een geldige startdatum op te geven.";
	}
	
	// einddatum
	$enddate = $_POST['enddate'];
	if (CheckTheDate($enddate)) {
		$enddate_db = DateToDBdate($enddate);
	} else {
		$fail_msg_enddate = "U dient een geldige einddatum op te geven.";
	}
	
	// datumvolgorde
	if (strtotime($enddate_db) < strtotime($startdate_db)) {
		$fail_msg_date = "De einddatum dient na de begindatum te liggen.";
	}
	if (date("w", strtotime($enddate_db)) <> date("w", strtotime($startdate_db))) {
		$fail_msg_date = "De begin- en einddatum dienen op dezelfde weekdag te vallen.";
	}
	
	// tijden
	$start_time_hrs = $_POST['start_time_hrs'];
	$start_time_mins = $_POST['start_time_mins'];
	$start_time = $start_time_hrs.":".$start_time_mins;	
	$end_time_hrs = $_POST['end_time_hrs'];
	$end_time_mins = $_POST['end_time_mins'];
	$end_time = $end_time_hrs.":".$end_time_mins;	
	$duration = (($end_time_hrs - $start_time_hrs) * 60) + ($end_time_mins - $start_time_mins);
	if ($duration <= 0) {
		$fail_msg_time = "De eindtijd van een outing dient later dan de begintijd te zijn.";
	}
	
	// boot
	$boat_id = $_POST['boat_id'];
	
	// naam
	$pname = $_POST['pname'];
	if (!CheckName($pname)) {
		$fail_msg_pname = "U dient een geldige voor- en achternaam op te geven. Let op: de apostrof (') wordt niet geaccepteerd.";
	}
	
	// ploegnaam
	$name = $_POST['name'];
	
	// e-mail
	$email = $_POST['email'];
	// niet verplicht, maar moet wel correct zijn
	if ($email && !CheckEmail($email)) {
		$fail_msg_email = "U dient een geldig e-mailadres op te geven.";
	}
	
	// als niet gefaald, repeterend spitsblok toevoegen
	if (isset($fail_msg_startdate) || isset($fail_msg_enddate) || 
		isset($fail_msg_date) || isset($fail_msg_time) || isset($fail_msg_pname) || 
		isset($fail_msg_email)
	) {
		$fail = true;
	} else {
		if ($spits_id) {
			// wijzigen bestaand blok
			$query = "DELETE FROM ".$opzoektabel." WHERE Spits='$spits_id';";
			mysql_query($query);
			echo "Bestaande versie van dit spitsblok verwijderd.<br>";
		} else {
			// toevoegen nieuw blok
			$query = "SELECT DISTINCT Spits FROM ".$opzoektabel." ORDER BY Spits;";
			$result = mysql_query($query);
			while ($row = mysql_fetch_assoc($result)) {
				$spits_id = $row['Spits'];
			}
			$spits_id += 1;
		}
		$name = addslashes($name); // speciale tekens in ploegnaam "redden"
		$day_tmp = explode("-", $startdate_db);
		$c_start = gregoriantojd($day_tmp[1], $day_tmp[2], $day_tmp[0]);
		$day_tmp = explode("-", $enddate_db);
		$c_end = gregoriantojd($day_tmp[1], $day_tmp[2], $day_tmp[0]);
		for ($c = $c_start; $c <= $c_end; $c += 7) {
			$day_tmp = jdtogregorian($c);
			$day_tmp2 = explode("/", $day_tmp);
			$date_ins_db = $day_tmp2[2]."-".$day_tmp2[0]."-".$day_tmp2[1];
			// Check inschrijving tegen de database
			$query = "SELECT * FROM ".$opzoektabel." WHERE ((Begintijd >= '$start_time' AND Begintijd < '$end_time') OR (Eindtijd > '$start_time' AND Eindtijd <= '$end_time') OR (Begintijd <= '$start_time' AND Eindtijd >= '$end_time')) AND Datum = '$date_ins_db' AND Boot_ID = '$boat_id';";
			$result = mysql_query($query);
			if (!$result) {
				echo "Het controleren van uw inschrijving is mislukt.<br>";
			} else {
				$rows_aff = mysql_affected_rows($link);
				if ($rows_aff > 0) {
					echo "Inschrijving $date_ins mislukt omdat deze conflicteert met een al bestaande inschrijving.<br>";
				} else {
					$query2 = "INSERT INTO ".$opzoektabel." (Datum, Inschrijfdatum, Begintijd, Eindtijd, Boot_ID, Pnaam, Ploegnaam, Email, MPB, Spits, Controle) VALUES ('$date_ins_db', '$today_db', '$start_time', '$end_time', '$boat_id', '$pname', '$name', '$email', '$mpb', '$spits_id', '0');";
					$result2 = mysql_query($query2);
					$date_ins = strftime('%A %d-%m-%Y', strtotime($date_ins_db));
					echo 'Inschrijving ' . $date_ins;
					if ($result2) {
						echo 'geslaagd.';
					} else {
						echo 'mislukt.';
					}
					echo '<br>';
				}
			}
		}
		echo "<p><a href='./admin_spits.php?ploeg_te_tonen=$name'>Ga terug</a></p>";
	}
}

// HET FORMULIER
if ((!isset($_POST['submit']) && !isset($_POST['cancel'])) || $fail) {
	echo "<h1>Toevoegen repeterend spitsblok</h1>";
	echo '<form name="form" action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
	
	// bestuurslid
	echo "<div class='form-group'><label>Uw functie</label>";
	echo "<select name=\"mpb\" class='form-control'>";
	$cnt = 0;
	foreach ($mpb_array as $mpb_db) {
		if ($cnt > 0) { // eerste veld is leeg
			echo "<option value=\"$mpb_db\" ";
			if (isset($mpb) && $mpb == $mpb_db) {
				echo "selected=\"selected\"";
			}
			echo ">$mpb_array_sh[$cnt]</option>";
		}
		$cnt++;
	}
	echo "</select>";
	if (isset($fail_msg_mpb)) {
		echo '' . $fail_msg_mpb . '';
	}
	echo "</div>";
	
	// startdatum
	if (isset($fail_msg_date)) {
		echo '' . $fail_msg_date . '';
	}
	echo "<div id='js-daterange'>";
	echo "<div class='row'>";
	echo "<div class='col-md-6'>";
	echo "<div class='form-group'><label>Startdatum</label>";
	echo '<input type="text" name="startdate" id="startdate" class="form-control datepicker-start" maxlength="10" value="' . (isset($startdate) ? $startdate : '') . '">';
	if (isset($fail_msg_startdate)) {
		echo '' . $fail_msg_startdate . '';
	}
	echo "</div>";
	echo "</div>";
	
	// einddatum
	echo "<div class='col-md-6'>";
	echo "<div class='form-group'><label>Einddatum</label>";
	echo '<input type="text" name="enddate" id="enddate" class="form-control datepicker-end" maxlength="10" value="' . (isset($enddate) ? $enddate : '') . '">';
	if (isset($fail_msg_enddate)) {
		echo '' . $fail_msg_enddate . '';
	}
	echo "</div>";
	echo "</div>";
	echo "</div>";
    echo "</div>";
	
	// starttijd
	echo "<div class='form-group'><label>Begintijd</label><div class='row'>";
	echo "<div class='col-md-6'><select name=\"start_time_hrs\" class='form-control'>";
		for ($t=6; $t<24; $t++) {
			echo"<option value=\"".$t."\" ";
			if (isset($start_time_hrs) && $start_time_hrs == $t) {
				echo "selected=\"selected\"";
			}
			echo ">".$t."</option>";
		}
	echo "</select></div>";
	echo "<div class='col-md-6'><select name=\"start_time_mins\" class='form-control'>";
		echo "<option value=\"00\" ";
		if (isset($start_time_mins) && $start_time_mins == 0) {
			echo "selected=\"selected\"";
		}
		echo ">00</option>";
		echo "<option value=\"15\" ";
		if (isset($start_time_mins) && $start_time_mins == 15) {
			echo "selected=\"selected\"";
		}
		echo ">15</option>";
		echo "<option value=\"30\" ";
		if (isset($start_time_mins) && $start_time_mins == 30) {
			echo "selected=\"selected\"";
		}
		echo ">30</option>";
		echo "<option value=\"45\" ";
		if (isset($start_time_mins) && $start_time_mins == 45) {
			echo "selected=\"selected\"";
		}
		echo ">45</option>";
	echo "</select></div></div>";
	if (isset($fail_msg_time)) {
		echo '' . $fail_msg_time . '';
	}
	echo "</div>";
	
	// eindtijd
	echo "<div class='form-group'><label>Eindtijd</label><div class='row'>";
	echo "<div class='col-md-6'><select name=\"end_time_hrs\" class='form-control'>";
		for ($t=6; $t<24; $t++) {
			echo"<option value=\"".$t."\" ";
			if (isset($end_time_hrs) && $end_time_hrs == $t) {
				echo "selected=\"selected\"";
			}
			echo ">".$t."</option>";
		}
	echo "</select></div>";
	echo "<div class='col-md-6'><select name=\"end_time_mins\" class='form-control'>";
		echo "<option value=\"00\" ";
		if (isset($end_time_mins) && $end_time_mins == 0) {
			echo "selected=\"selected\"";
		}
		echo ">00</option>";
		echo "<option value=\"15\" ";
		if (isset($end_time_mins) && $end_time_mins == 15) {
			echo "selected=\"selected\"";
		}
		echo ">15</option>";
		echo "<option value=\"30\" ";
		if (isset($end_time_mins) && $end_time_mins == 30) {
			echo "selected=\"selected\"";
		}
		echo ">30</option>";
		echo "<option value=\"45\" ";
		if (isset($end_time_mins) && $end_time_mins == 45) {
			echo "selected=\"selected\"";
		}
		echo ">45</option>";
	echo "</select></div></div>";
	echo "</div>";
	
	// boot
	echo "<div class='form-group'><label>Boot/ergometer</label>";
	echo "<select name=\"boat_id\" class='form-control'>";
	$query = 'SELECT ID, Naam FROM boten WHERE Datum_eind IS NULL AND Type<>"soc" ORDER BY Naam';
	$boats_result = mysql_query($query);
	if (!$boats_result) {
		die("Ophalen van vlootinformatie mislukt.". mysql_error());
	} else {
		while ($row = mysql_fetch_assoc($boats_result)) {
			$curr_boat_id = $row['ID'];
			$curr_boat = $row['Naam'];
			echo"<option value=\"".$curr_boat_id."\" ";
			if (isset($boat_id) && $boat_id == $curr_boat_id) {
				echo "selected=\"selected\"";
			}
			echo ">".$curr_boat."</option>";
		}
	}
	echo "</select>";
	echo "</div>";
	
	// PersoonsNaam (pname)
	echo "<div class='form-group'><label>Voor- en achternaam ploegcaptain</label>";
	echo '<input type="text" name="pname" value="' . (isset($pname) ? $pname : '') . '" class="form-control">';
	if (isset($fail_msg_pname)) {
		echo '' . $fail_msg_pname . '';
	}
	echo "</div>";
	
	// Ploegnaam
	echo "<div class='form-group'><label>Ploegnaam (optioneel)</label>";
	echo '<input type="text" name="name" value="' . (isset($name) ? $name : '') . '" class="form-control">';
	echo "</div>";
	
	// e-mailadres
	echo "<div class='form-group'><label>E-mailadres (optioneel)</label>";
	echo '<input type="text" name="email" value="' . (isset($email) ? $email : '') . '" class="form-control">';
	if (isset($fail_msg_email)) {
		echo '' . $fail_msg_email . '';
	}
	echo "</div>";
	
	// knoppen
	echo "<input type=\"submit\" name=\"submit\" value=\"Toevoegen\" class='btn btn-primary'> ";
	echo "</form>";
}

mysql_close($link);

?>

        </div>
        
    </div>
    
</div>
</body>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    
    <script src="../bower_components/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="../scripts/datepicker.js"></script>

</html>

<script>

function changeInfo(){
	return true;
}

</script>
