<?php
$locationHeader = 'Wedstrijdblokken - Wedstrijdblok toevoegen';
$backLink = '<a href="./admin_blokken.php" class="btn btn-default">Terug naar de wedstrijdblokken</a>';
include 'admin_header.php';
?>

<?php
$fail = false;
$blok_id = 0;
if (isset($_GET['id'])) {
	$blok_id = $_GET['id'];
	$result = mysql_query(sprintf('SELECT MPB, Datum, Begintijd, Eindtijd, boten.Naam AS Bootnaam, Pnaam 
			FROM %s
			JOIN boten ON %s.Boot_ID = boten.ID
			WHERE Wedstrijdblok = %d 
			ORDER BY Datum', $opzoektabel, $opzoektabel, $blok_id));
	if (!$result) {
		die('Ophalen van informatie mislukt: ' . mysql_error());
	}
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
	$boat = $row['Bootnaam'];
	$pname = $row['Pnaam'];
	$enddate = $row['Datum'];
	while ($row = mysql_fetch_assoc($result)) {
		$enddate = $row['Datum'];
	}
	$enddate = DBdateToDate($enddate);
}

if (isset($_POST['cancel'])) {
	header('Location: admin_blokken.php');
}

if (isset($_POST['submit'])) {
	// bestuurslid
	$mpb = $_POST['mpb'];
	if (!$mpb) {
		$fail_msg_mpb = "U dient uw functie te selecteren.";
	}
	// startdatum
	$startdate = $_POST['startdate'];
	if (CheckTheDate($startdate)) {
		$startdate_db = DateToDBdate($startdate);
		if (strtotime($startdate_db) - strtotime($today_db) < 0) {
			$fail_msg_startdate = "De startdatum moet op of na vandaag liggen.";
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
	// tijden
	$start_time_hrs = $_POST['start_time_hrs'];
	$start_time_mins = $_POST['start_time_mins'];
	$start_time = $start_time_hrs . ":" . $start_time_mins;	
	$end_time_hrs = $_POST['end_time_hrs'];
	$end_time_mins = $_POST['end_time_mins'];
	$end_time = $end_time_hrs . ":" . $end_time_mins;
	// datum-/tijdvolgorde
	if (strtotime($enddate_db . ' ' . $end_time) <= strtotime($startdate_db . ' ' . $start_time)) {
		$fail_msg_date = "Het einde van het blok dient na het begin te liggen.";
	}
	// boot
	$boat_id = $_POST['boat_id'];
	$result = mysql_query(sprintf('SELECT Naam FROM boten WHERE ID = %d', $boat_id));
	if ($row = mysql_fetch_assoc($result)) {
		$boatname = $row['Naam'];
	} else {
		die('Onbekende boot.');
	}
	// naam (omschrijving)
	$pname = $_POST['pname'];
	// als niet gefaald, wedstrijdblok toevoegen
	if (isset($fail_msg_startdate) || isset($fail_msg_enddate) || isset($fail_msg_date)) {
		$fail = true;
	} else {
		if ($blok_id) {
			// wijzigen bestaand blok
			mysql_query(sprintf('DELETE FROM %s WHERE Wedstrijdblok = %d', $opzoektabel, $blok_id));
			echo "Bestaande versie van dit wedstrijdblok verwijderd.<br>";
		} else {
			// toevoegen nieuw blok
			$result = mysql_query(sprintf('SELECT MAX(Wedstrijdblok) AS MaxId FROM %s', $opzoektabel));
			if ($row = mysql_fetch_assoc($result)) {
				$blok_id = $row['MaxId'] + 1;
			} else {
				$blok_id = 1;
			}
		}
		$day_tmp = explode("-", $startdate_db);
		$c_start = gregoriantojd($day_tmp[1], $day_tmp[2], $day_tmp[0]);
		$day_tmp = explode("-", $enddate_db);
		$c_end = gregoriantojd($day_tmp[1], $day_tmp[2], $day_tmp[0]);
		for ($c = $c_start; $c <= $c_end; $c++) {
			// Datum
			$day_tmp = jdtogregorian($c);
			$day_tmp2 = explode("/", $day_tmp);
			$date_ins_db = $day_tmp2[2] . "-" . $day_tmp2[0] . "-" . $day_tmp2[1];
			// Tijden
			if ($c == $c_start) {
				$start_time_tmp = $start_time;
			} else {
				$start_time_tmp = '6:00';
			}
			if ($c == $c_end) {
				$end_time_tmp = $end_time;
			} else {
				$end_time_tmp = '23:45';
			}
			// Check inschrijving tegen de database
			$result = mysql_query('SELECT * 
					FROM ' . $opzoektabel . ' 
					WHERE ((Begintijd >= "' . $start_time_tmp . '" AND Begintijd < "' . $end_time_tmp . '") 
						OR (Eindtijd > "' . $start_time_tmp . '" AND Eindtijd <= "' . $end_time_tmp . '") 
						OR (Begintijd <= "' . $start_time_tmp . '" AND Eindtijd >= "' . $end_time_tmp . '")) 
					AND Datum = "' . $date_ins_db . '" 
					AND Boot_ID = ' . $boat_id);
			if (!$result) {
				echo 'Het controleren van inschrijving ' . $date_ins . ' is mislukt.<br>';
			} else {
				$rows_aff = mysql_affected_rows($link);
				if ($rows_aff > 0) {
					// Conflicten -> verwijderen en mailtje sturen
					while ($row = mysql_fetch_assoc($result)) {
						$date_sh = strftime('%A %d-%m-%Y', strtotime($row['Datum']));
						$message = sprintf('Uw inschrijving op %s vanaf %s komt te vervallen omdat "%s" zojuist geblokt is voor een wedstrijd.', $date_sh, substr($row['Begintijd'], 0, 5), $boatname);
						SendEmail($row['Email'], "Verwijdering inschrijving", $message);
						mysql_query(sprintf('DELETE FROM %s WHERE Volgnummer = %d', $opzoektabel, $row['Volgnummer']));
					}
					echo 'Conflicterende inschrijvingen verwijderd en e-mails verstuurd.<br>';
				}
				$result2 = mysql_query('INSERT INTO ' . $opzoektabel . ' (Datum, Inschrijfdatum, Begintijd, Eindtijd, Boot_ID, Pnaam, Ploegnaam, MPB, Spits, Wedstrijdblok, Controle) 
						VALUES ("' . $date_ins_db . '", "' . $today_db . '", "' . $start_time_tmp . '", "' . $end_time_tmp . '", ' . $boat_id . ', "' . $pname . '", "", "' . $mpb . '", 0, ' . $blok_id . ', 0)');
				$date_ins = strftime('%A %d-%m-%Y', strtotime($date_ins_db));
				echo 'Inschrijving ' . $date_ins . ' van ' . $start_time_tmp . ' tot ' . $end_time_tmp;
				if ($result2) {
					echo ' geslaagd.';
				} else {
					echo ' mislukt.';
				}
				echo '<br><br>';
			}
		} // end for
		echo '<p><a href="admin_blokken.php?boot_te_tonen=' . $boatname . '">Ga terug</a></p>';
	}
}

// HET FORMULIER
if ((!isset($_POST['submit']) && !isset($_POST['cancel'])) || $fail) {
	echo "<h1>Toevoegen/bewerken wedstrijdblok</h1>";
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
	echo "<div class='form-group'><label>Begindatum</label>";
	echo '<input type="text" name="startdate" id="startdate" class="form-control datepicker" maxlength="10" value="' . (isset($startdate) ? $startdate : '') . '">';
	if (isset($fail_msg_startdate)) {
		echo '' . $fail_msg_startdate . '';
	}
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
	echo "</select></div>";
	if (isset($fail_msg_time)) {
		echo '' . $fail_msg_time . '';
	}
	echo "</div></div>";
	
	// einddatum
	echo "<div class='form-group'><label>Einddatum</label>";
	echo '<input type="text" name="enddate" id="enddate"  class="form-control datepicker" maxlength="10" value="' . (isset($enddate) ? $enddate : '') . '">';
	if (isset($fail_msg_enddate)) {
		echo '' . $fail_msg_enddate . '';
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
	echo '<select name="boat_id" class="form-control"">';
	$query = 'SELECT ID, Naam, Type FROM boten WHERE Datum_eind IS NULL AND Type <> "soc" ORDER BY Naam';
	$boats_result = mysql_query($query);
	if (!$boats_result) {
		die("Ophalen van vlootinformatie mislukt: " . mysql_error());
	} else {
		while ($row = mysql_fetch_assoc($boats_result)) {
			$curr_boat_id = $row['ID'];
			echo '<option value="' . $curr_boat_id . '" ';
			if (isset($boat_id) && $boat_id == $curr_boat_id) {
				echo 'selected="selected"';
			}
			echo '>' . $row['Naam'] . ' (' . $row['Type'] . ')</option>';
		}
	}
	echo "</select>";
	echo "</div>";
	
	// Omschrijving (pname)
	echo "<div class='form-group'><label>Omschrijving</label>";
	echo '<input type="text" name="pname" value="' . (isset($pname) ? $pname : '') . '" class="form-control">';
	if (isset($fail_msg_pname)) {
		echo '' . $fail_msg_pname . '';
	}
	echo "</div>";
	
	// knoppen
	echo "<div class='form-group'>";
	echo "<input type=\"submit\" name=\"submit\" value=\"Toevoegen\" class='btn btn-primary'></div>";
	echo "</form>";
}
?>

<?php include 'admin_footer.php'; ?>
