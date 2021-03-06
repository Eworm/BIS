<?php
// check login
session_start();
if (!isset($_SESSION['authorized']) || $_SESSION['authorized'] != 'yes' || $_SESSION['restrict'] != 'matcie') {
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
                
        <div class="col-md-12">

<?php

// reeds ingevulde waardes ophalen (indien aanwezig)
$id = $_GET['id'];
if ($id) {
	$query = "SELECT * from schades WHERE ID='$id';";
	$result = mysql_query($query);
	if (!$result) {
		die("Ophalen van schade mislukt.". mysql_error());
	}
	$row = mysql_fetch_assoc($result);
	$name = $row['Naam'];
	// bootnaam
	$boat_id = $row['Boot_ID'];
	if ($boat_id == 0) {
		$boat = "algemeen";
	} else {
		$query2 = "SELECT Naam from boten WHERE ID=$boat_id;";
		$result2 = mysql_query($query2);
		$row2 = mysql_fetch_assoc($result2);
		$boat = $row2['Naam'];
	}
	//
	$note = $row['Oms_lang'];
	$feedback = $row['Feedback'];
	$action = $row['Actie'];
	$action_holder = $row['Actiehouder'];
	$prio = $row['Prio'];
	$real = $row['Realisatie'];
	$date_ready = $row['Datum_gereed'];
	$date_ready_sh = DBdateToDate($date_ready);
	$repair = $row['Noodrep'];
	$notes = $row['Opmerkingen'];
}

// init
if (!$_POST['cancel'] && !$_POST['insert'] && !$_POST['delete']) {
	$fail = FALSE;
}

// knop gedrukt
if ($_POST['cancel']){
	unset($_POST['name'], $_POST['boatid'], $_POST['note'], $_POST['feedback'], $_POST['action'],  $_POST['action_holder'], $_POST['prio'], $_POST['real'], $_POST['date_ready_sh'], $_POST['repair'], $_POST['notes'], $name, $boat_id, $note, $feedback, $action, $action_holder, $prio, $real, $date_ready_sh, $repair, $notes);
	$fail = FALSE;
	echo "<a href='admin_schade.php'>Terug naar de werkstroom</a></p>";
}

if ($_POST['delete']){
	$query = "DELETE FROM `schades` WHERE ID='$id';";
	$result = mysql_query($query);
	if (!$result) {
		die("Verwijderen schade mislukt.". mysql_error());
	} else {
		echo "<p>Schade succesvol definitief verwijderd.<br>";
		echo "<a href='admin_schade.php'>Terug naar de werkstroom</a></p>";
	}
}

if ($_POST['insert']){
	$name = $_POST['name'];
	$boat_id = $_POST['boat_id'];
	$note = addslashes($_POST['note']);
	$feedback = addslashes($_POST['feedback']);
	$action = addslashes($_POST['action']);
	$action_holder = $_POST['action_holder'];
	$prio = $_POST['prio'];
	$real = $_POST['real'];
	$date_ready_sh = $_POST['date_ready_sh'];
	$date_ready = DateToDBdate($date_ready_sh);
	if ($real == 100 && $date_ready == "0000-00-00") $date_ready = $today_db;
	$repair = addslashes($_POST['repair']);
	$notes = addslashes($_POST['notes']);
	if ($id) {
		$query = "UPDATE `schades` SET Datum_gew='$today_db', Naam='$name', Boot_ID='$boat_id', Oms_lang='$note', Feedback='$feedback', Actie='$action', Actiehouder='$action_holder', Prio='$prio', Realisatie='$real', Datum_gereed='$date_ready', Noodrep='$repair', Opmerkingen='$notes' WHERE ID='$id';";
	} else {
		$query = "INSERT INTO `schades` (Datum, Datum_gew, Naam, Boot_ID, Oms_lang, Feedback, Actie, Actiehouder, Prio, Realisatie, Datum_gereed, Noodrep, Opmerkingen) VALUES ('$today_db', '$today_db', '$name', '$boat_id', '$note', '$feedback', '$action', '$action_holder', '$prio', '$real', '$date_ready', '$repair', '$notes');";
	}
	$result = mysql_query($query);
	if (!$result) {
		die("Aanmaken/bewerken schade mislukt.". mysql_error());
	} else {
		echo "<p>Schade succesvol aangemaakt/bewerkt.<br>";
		echo "<a href='admin_schade.php' class='btn btn-primary'>Terug naar de werkstroom</a></p>";
	}
}

// Formulier
if ((!$_POST['insert'] && !$_POST['delete'] && !$_POST['cancel']) || $fail) {
	echo "<h1>Schademelding aanmaken/bewerken</h1>";
	echo "<form name='form' action=\"$REQUEST_URI\" method=\"post\">";
	
	// naam
	echo "<div class='form-group'><label>Naam</label>";
	echo "<input type=\"text\" name=\"name\" value=\"$name\" class='form-control'>";
	echo "</div>";
	
	// boot
	echo "<div class='form-group'><label>Boot/ergometer</label>";
	echo "<select name=\"boat_id\" class='form-control'>";
	echo "<option value=0 ";
	if ($boat_id == 0) echo "selected=\"selected\"";
	echo ">algemeen</option>";
	$query = "SELECT ID, Naam FROM boten WHERE Datum_eind IS NULL AND Type<>\"soc\" ORDER BY Naam;";
	$boats_result = mysql_query($query);
	if (!$boats_result) {
		die("Ophalen van vlootinformatie mislukt.". mysql_error());
	} else {
		while ($row = mysql_fetch_assoc($boats_result)) {
			$curr_boat_id = $row[ID];
			$curr_boat = $row[Naam];
			echo "<option value=".$curr_boat_id." ";
			if ($boat_id == $curr_boat_id) echo "selected=\"selected\"";
			echo ">".$curr_boat."</option>";
		}
	}
	echo "</select>";
	echo "</div>";
	
	// mededeling
	echo "<div class='form-group'><label>Omschrijving (max. 1000 tekens)</label>";
	echo "<textarea name=\"note\" rows='4' class='form-control'/>$note</textarea>";
	echo "</div>";
	
	// feedback
	echo "<div class='form-group'><label>Feedback MatCie aan melder (max. 1000 tekens)</label>";
	echo "<textarea name=\"feedback\" rows='4'  class='form-control'/>$feedback</textarea>";
	echo "</div>";
	
	// actie
	echo "<div class='form-group'><label>Actie (max. 1000 tekens)</label>";
	echo "<textarea name=\"action\" rows='4'  class='form-control'/>$action</textarea>";
	echo "</div>";
	
	// actiehouder
	echo "<div class='form-group'><label>Actiehouder</label>";
	echo "<input type=\"text\" name=\"action_holder\" value=\"$action_holder\"  class='form-control'>";
	echo "</div>";
	
	// prioriteit
	echo "<div class='form-group'><label>Prioriteit (1-3, 1 is hoogst)</label>";
	echo "<select name=\"prio\" class='form-control'>";
	for ($i = 1; $i < 4; $i++) {
		echo "<option value=\"".$i."\" ";
		if ($prio == $i) echo "selected";
		echo "/>".$i;
	}
	echo "</select>";
	echo "</div>";
	
	// realisatie
	echo "<div class='form-group'><label>% gerealiseerd (0-100)</label>";
	echo "<input type=\"text\" name=\"real\" value=\"$real\"  class='form-control'>";
	echo "</div>";
	
	// datum gereed
	echo "<div class='form-group'><label>Datum gereed</label>";
	echo "<input type='text' name='date_ready_sh' id='date_ready_sh' class='form-control' maxlength='10' value='$date_ready_sh'>";
	echo "</div>";
	
	// noodreparatie
	echo "<div class='form-group'><label>Noodreparatie (max. 1000 tekens)</label>";
	echo "<textarea name=\"repair\" rows='4'  class='form-control'/>$repair</textarea>";
	echo "</div>";
	
	// opmerkingen
	echo "<div class='form-group'><label>Opmerkingen (max. 1000 tekens)</label>";
	echo "<textarea name=\"notes\" rows='4'  class='form-control'/>$notes</textarea>";
	echo "</div>";
	
	// knoppen
	echo "<div class='form-group'>";
	echo "<input type=\"submit\" name=\"insert\" class='btn btn-primary' value=\"Toevoegen\">";
	echo "<input type=\"submit\" name=\"delete\" class='btn btn-danger' value=\"Verwijderen\">";
	echo "</div>";
	echo "</form>";
	
	echo "<p>NB: Verwijderen alleen gebruiken ingeval van bijv. een onzin-melding. Anders de melding na afhandeling via de werkstroom archiveren.</p>";
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
