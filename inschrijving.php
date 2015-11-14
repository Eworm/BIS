<?php
// check login
session_start();
if (!isset($_SESSION['authorized_bis']) || $_SESSION['authorized_bis'] != 'yes') {
	header("Location: bis_login.php");
	exit();
}

include_once("include_globalVars.php");
include_once("include_boardMembers.php");
include_once("include_helperMethods.php");
include_once("inschrijving_methods.php");

setlocale(LC_TIME, 'nl_NL');

$bisdblink = mysql_connect($database_host, $database_user, $database_pass);
if (!mysql_select_db($database, $bisdblink)) {
	echo "<p>Fout: database niet gevonden.</p>";
	exit();
}

$NR_OF_CONCEPTS = 7; // LET OP: aanpassen als het aantal Concept-ergo's verandert! (ivm blokinschrijving)
$fail_msg = "";
$spits = 0;

// var'en die alleen maar dienen om weer door te schuiven naar index; sanity check aldaar
$cat_to_show = $_GET['cat_to_show'];
$grade_to_show = $_GET['grade_to_show'];

$id = $_GET['id']; // 0 indien nieuwe inschrijving
if ($id < 0 || !is_numeric($id)) { // check op ID
	echo "<p>Er is iets misgegaan.</p>";
	exit();
}

if ($id > 0) { // bestaande inschrijving: haal de var'en op t.b.v. show_availability
	$query = "SELECT * FROM ".$opzoektabel." WHERE Volgnummer='$id';";
	$result = mysql_query($query);
	if ($result) {
		$rows_aff = mysql_affected_rows($bisdblink);
		if ($rows_aff > 0) {
			$row = mysql_fetch_assoc($result);
			if (isset($_POST['date'])) { 
				$date = $_POST['date'];
			} else {
				$date_db = $row['Datum'];
				$date = DBdateToDate($date_db);
			}
			if (isset($_POST['start_time_hrs']) && isset($_POST['start_time_mins'])) {
				$start_time = $_POST['start_time_hrs'].":".$_POST['start_time_mins'];
			} else {
				$start_time = $row['Begintijd'];
			}
			if (isset($_POST['end_time_hrs']) && isset($_POST['end_time_mins'])) {
				$end_time = $_POST['end_time_hrs'].":".$_POST['end_time_mins'];
			} else {
				$end_time = $row['Eindtijd'];
			}
			if (isset($_POST['boat_id'])) {
				$boat_id = $_POST['boat_id'];
			} else {
				$boat_id = $row['Boot_ID'];
			}
			if (isset($_POST['pname'])) {
				$pname = $_POST['pname'];
			} else {
				$pname = $row['Pnaam'];
			}
			if (isset($_POST['name'])) {
				$name = $_POST['name'];
			} else {
				$name = $row['Ploegnaam'];
			}
			if (isset($_POST['email'])) {
				$email = $_POST['email'];
			} else {
				$email = $row['Email'];
			}
			if (isset($_POST['mpb'])) {
				$mpb = $_POST['mpb'];
			} else {
				$mpb = $row['MPB'];
			}
			$spits = $row['Spits'];
		} else {
			echo "<p>Deze inschrijving bestaat niet.</p>";
			exit();
		}
	} else {
		echo "<p>De inschrijving kan niet gevonden worden.</p>";
		exit();
	}
}
if ($id == 0) { // nieuwe inschrijving: haal de var'en op t.b.v. show_availability
	if (isset($_POST['boat_id'])) { 
		$boat_id = $_POST['boat_id'];
	} else {
		$boat_id = $_GET['boat_id'];
	}
	if (isset($_POST['date'])) {
		$date = $_POST['date'];
	} else {
		$date = $_GET['date'];
	}
	if (isset($_POST['start_time_hrs']) && isset($_POST['start_time_mins'])) {
		$start_time = $_POST['start_time_hrs'].":".$_POST['start_time_mins'];
	} else {
		$start_time = $_GET['time_to_show'];
	}
	if (isset($_POST['end_time_hrs']) && isset($_POST['end_time_mins'])) {
		$end_time = $_POST['end_time_hrs'].":".$_POST['end_time_mins'];
	}
}

// sanity check op boot
if (!is_numeric($boat_id) || $boat_id < 0) {
	echo "<p>Deze boot bestaat niet.</p>";
	exit();
}
// sanity check op datum
if (!CheckTheDate($date)) {
	echo "<p>Datum (" . $date . ") klopt niet.</p>";
	exit();
} else {
	$date_db = DateToDBdate($date);
}

// indien niet aanwezig, tijden alvast invullen met defaults:
if (!$start_time) {
	if ($date == $today) {
		if ($thehour_q < 6) {
			$start_time_hrs = 6;
			$start_time_mins = 0;
		} else {
			$start_time_hrs = $thehour_q;
			$start_time_mins = $theminute_quarts;
		}
	} else {
		$start_time_hrs = 9;
		$start_time_mins = 0;
	}
	$start_time = $start_time_hrs.":".$start_time_mins;
} else {
	$start_time_fields = explode(":", $start_time);
	$start_time_hrs = $start_time_fields[0];
	$start_time_mins = $start_time_fields[1];
}

if (!isset($end_time)) {
	if ($cat_to_show == "Ergometers en bak") {
		$end_time_hrs = min(23, $start_time_hrs + 1);
	} else {
		$end_time_hrs = min(23, $start_time_hrs + 2);
	}
	if ($start_time_hrs >= 22 && $end_time_hrs == 23) {
		$end_time_mins = 45;
	} else {
		$end_time_mins = $start_time_mins;
	}
	$end_time = $end_time_hrs.":".$end_time_mins;
} else {
	$end_time_fields = explode(":", $end_time);
	$end_time_hrs = $end_time_fields[0];
	$end_time_mins = $end_time_fields[1];
}

// Message bar
echo "<div id='msgbar' class='alert alert-success'></div>"; // To be filled with AJAX after pressing button
echo "<div id='resscreen'>"; // Enables rest of screen to be removed upon success
// Rest of screen
// Show existing reservations
// Firstly disconnect from DB
mysql_close($bisdblink);
echo "<div id=\"AvailabilityInfo\">";
require_once('./show_availability.php');
echo "</div>";
// Reconnect to DB
$bisdblink = mysql_connect($database_host, $database_user, $database_pass);
if (!mysql_select_db($database, $bisdblink)) {
	echo "<p>Fout: database niet gevonden.</p>";
	exit();
}

// Surrounding div
// The form (evaluation happens via AJAX)
echo "<form name='resdetails' class='form-horizontal'>";

// ID, tbv AJAX
echo "<input type=\"hidden\" name=\"id\" id=\"id\" value=\"" . $id . "\">";
// Grade to show in index.php, so we can compare it to the grade of the reserved boat after success
echo "<input type=\"hidden\" name=\"grade\" id=\"grade\" value=\"" . $grade_to_show . "\">";

// Ergo-blokinschrijving, alleen bij een nieuwe inschrijving van Concepts
if ($id == 0 && substr($boat, 0, 7) == "Concept") {
	echo "<div class='row'><div class='col-md-12'><div class='alert alert-info'>Schrijf in &eacute;&eacute;n keer meerdere Concept-ergometers in: bijv. '3 t/m 5' voor Concepts 3, 4 en 5, of gewoon eentje, bijv. '2 t/m 2' voor alleen Concept 2.</div></div></div>";
	
	$ergo_lo = substr($boat, 8, 1);
	echo "<div class='form-group'><label class='col-md-4 control-label'>Blokinschrijving: Concept</label>";
	echo "<div class='col-md-3'><select name=\"ergo_lo\" id='ergo_lo' class='form-control'>";
	for ($t = $ergo_lo; $t <= $NR_OF_CONCEPTS; $t++) {
		echo"<option value=\"".$t."\" ";
		if ($ergo_lo == $t) echo "selected=\"selected\"";
		echo ">".$t."</option>";
	}
	echo "</select></div><div class='col-md-1'> t/m </div>";
	if (!isset($ergo_hi) || $ergo_hi == 0) $ergo_hi = $ergo_lo;
	echo "<div class='col-md-4'><select name=\"ergo_hi\" id='ergo_hi' class='form-control'>";
	for ($t = $ergo_lo; $t <= $NR_OF_CONCEPTS; $t++) {
		echo"<option value=\"".$t."\" ";
		if ($ergo_hi == $t) echo "selected=\"selected\"";
		echo ">".$t."</option>";
	}
	echo "</select></div>";
	echo "</div>";
	
}

// Ingeval van blokinschrijving Concepts, geen mogelijkheid om andere boot te kiezen
if (substr($boat, 0, 7) == "Concept") {
	$hide = " style=\"display:none\"";
}
// Boat
echo "<div class='form-group' ". (isset($hide) ? $hide : "") . ">";
echo "<label class='col-md-4 control-label' for='boat_id'>";
echo "Boot/ergometer";
echo "</label>";
echo "<div class='col-md-8'>";
echo "<select" . (isset($hide) ? $hide : "") . " name=\"boat_id\" onchange=\"changeInfo();\" id=\"boat_id\" class=\"form-control\">";
echo "<option value=0 ";
if ($boat_id == 0) echo "selected=\"selected\"";
echo "></option>";
$query = "SELECT boten.ID AS ID, Naam, Gewicht, `Type`, boten.Roeigraad FROM boten JOIN roeigraden ON boten.Roeigraad=roeigraden.Roeigraad WHERE Datum_eind IS NULL ORDER BY `Type`, roeigraden.ID, Naam;";
$boats_result = mysql_query($query);
if (!$boats_result) {
	die("Ophalen van vlootinformatie mislukt.". mysql_error());
} else {
	$t = 0;
	while ($row = mysql_fetch_assoc($boats_result)) {
		$curr_boat_id = $row['ID'];
		$curr_boat = $row['Naam'];
		$curr_weight = $row['Gewicht'];
		$curr_type = $row['Type'];
		if ($curr_type != $curr_type_mem) {
			if ($t) echo "</optgroup>";
			echo "<optgroup label=\"".$curr_type."\">";
		}
		$curr_type_mem = $curr_type;
		$curr_grade = $row['Roeigraad'];
		echo "<option value=\"".$curr_boat_id."\" ";
		if ($boat_id == $curr_boat_id) echo "selected=\"selected\"";
		echo ">".$curr_boat." (".$curr_weight." kg, ".$curr_grade.")</option>";
		$t++;
	}
	echo "</optgroup>";
}
echo "</select>";
echo "</div>";
echo "</div>";


// persoonsnaam
echo "<div class='form-group'>";
echo "<label class='col-md-4 control-label' for='pname'>";
echo "Voor- en achternaam";
echo "</label>";
echo "<div class='col-md-8'>";
echo "<input type=\"text\" id=\"pname\" name=\"pname\" value=\"" . (isset($pname) ? $pname : "") . "\" class=\"form-control\" autofocus required>";
echo "</div>";
echo "</div>";

// ploegnaam
echo "<div class='form-group'>";
echo "<label class='col-md-4 control-label' for='name'>";
echo "Ploegnaam/omschrijving (optioneel)";
echo "</label>";
echo "<div class='col-md-8'>";
echo "<input type=\"text\" id=\"name\" name=\"name\" value=\"" . (isset($name) ? $name : "") . "\" class=\"form-control\">";
echo "</div>";
echo "</div>";

// e-mailadres
echo "<div class='form-group'>";
echo "<label class='col-md-4 control-label' for='email'>";
echo "E-mailadres (optioneel)";
echo "</label>";
echo "<div class='col-md-8'>";
echo "<input type=\"text\" id=\"email\" name=\"email\" value=\"" . (isset($email) ? $email : "") . "\" class=\"form-control\">";
echo "</div>";
echo "</div>";

// mpb
echo "<div class='form-group'>";
echo "<label class='col-md-4 control-label' for='mpb'>";
echo "MPB (indien nodig)";
echo "</label>";
echo "<div class='col-md-8'>";
echo "<select name=\"mpb\" id='mpb' class='form-control'>";
$cnt = 0;
foreach($mpb_array as $mpb_db) {
	echo "<option value='" . $mpb_db . "'";
	if (isset($mpb) && $mpb == $mpb_db) echo " selected='selected'";
	echo ">" . $mpb_array_sh[$cnt] . "</option>";
	$cnt++;
}
echo "</select>";
echo "</div>";
echo "</div>";

// datum
echo "<div class='form-group'>";
echo "<label class='col-md-4 control-label' for='resdate'>";
echo "Datum (dd-mm-jjjj)";
echo "</label>";
echo "<div class='col-md-4'>";
echo "<input type='text' onchange=\"changeInfo();\" name='resdate' id='resdate' maxlength='10' class='form-control datepicker' value='" . $date . "'>";
echo "</div>";
echo "</div>";

// begintijd
echo "<div class='form-group'>";
echo "<label class='col-md-4 control-label' for='start_time_hrs'>";
echo "Begintijd";
echo "</label>";
echo "<div class='col-md-2'>";
echo "<select name='start_time_hrs' onchange=\"changeInfo();\" id='start_time_hrs' class='form-control'>";
	for ($t=6; $t<24; $t++) {
		echo"<option value=\"".$t."\" ";
		if ($start_time_hrs == $t) echo "selected=\"selected\"";
		echo ">".$t."</option>";
	}
echo "</select>";
echo "</div>";
echo "<div class='col-md-2'>";
echo "<select name='start_time_mins' onchange=\"changeInfo();\" id='start_time_mins' class='form-control'>";
	echo "<option value=\"00\" ";
	if ($start_time_mins == 0) echo "selected=\"selected\"";
	echo ">00</option>";
	echo "<option value=\"15\" ";
	if ($start_time_mins == 15) echo "selected=\"selected\"";
	echo ">15</option>";
	echo "<option value=\"30\" ";
	if ($start_time_mins == 30) echo "selected=\"selected\"";
	echo ">30</option>";
	echo "<option value=\"45\" ";
	if ($start_time_mins == 45) echo "selected=\"selected\"";
	echo ">45</option>";
echo "</select>";
echo "</div>";
echo "</div>";

// eindtijd
echo "<div class='form-group'>";
echo "<label class='col-md-4 control-label' for='end_time_hrs'>";
echo "Eindtijd";
echo "</label>";
echo "<div class='col-md-2'>";
echo "<select name='end_time_hrs' onchange=\"changeInfo();\" id='end_time_hrs' class='form-control'>";
	for ($t=6; $t<24; $t++) {
		echo"<option value=\"".$t."\" ";
		if ($end_time_hrs == $t) echo "selected=\"selected\"";
		echo ">".$t."</option>";
	}
echo "</select>";
echo "</div>";
echo "<div class='col-md-2'>";
echo "<select name='end_time_mins' onchange=\"changeInfo();\" id='end_time_mins' class='form-control'>";
	echo "<option value=\"00\" ";
	if ($end_time_mins == 0) echo "selected=\"selected\"";
	echo ">00</option>";
	echo "<option value=\"15\" ";
	if ($end_time_mins == 15) echo "selected=\"selected\"";
	echo ">15</option>";
	echo "<option value=\"30\" ";
	if ($end_time_mins == 30) echo "selected=\"selected\"";
	echo ">30</option>";
	echo "<option value=\"45\" ";
	if ($end_time_mins == 45) echo "selected=\"selected\"";
	echo ">45</option>";
echo "</select>";
echo "</div>";
echo "</div>";

// knoppen
echo "<div class='form-group'><div class='col-md-12 col-md-offset-4'><input type=\"button\" class='btn btn-primary' value=\"";
if ($id) {
	echo "Opslaan";
} else {
	if ($spits) {
		echo "Bevestigen";
	} else {
		echo "Inschrijven";
	}
}
echo "\" onclick=\"makeRes(" . $id .  ", '" . $start_time . "', '" . $cat_to_show . "', '" . $grade_to_show . "');\">";
if ($id) {
	echo "&nbsp;<input type=\"button\" class='bisbtn btn btn-danger' value=\"Verwijderen\" onclick=\"delRes(" . $id . ", '" . $start_time . 
		 "', '" . $cat_to_show . "', '" . $grade_to_show . "');\">";
}
echo "</div></div></form>";

mysql_close($bisdblink);
