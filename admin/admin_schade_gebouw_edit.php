<?php
// check login
session_start();
if (!isset($_SESSION['authorized']) || $_SESSION['authorized'] != 'yes' || $_SESSION['restrict'] != 'gebcie') {
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
	$query = "SELECT * from schades_gebouw WHERE ID='$id';";
	$result = mysql_query($query);
	if (!$result) {
		die("Ophalen van schade mislukt.". mysql_error());
	}
	$row = mysql_fetch_assoc($result);
	$name = $row['Naam'];
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
	unset($_POST['name'], $_POST['note'], $_POST['feedback'], $_POST['action'],  $_POST['action_holder'], $_POST['prio'], $_POST['real'], $_POST['date_ready_sh'], $_POST['repair'], $_POST['notes'], $name, $boat_id, $note, $feedback, $action, $action_holder, $prio, $real, $date_ready_sh, $repair, $notes);
	$fail = FALSE;
	echo "<a href='admin_schade_gebouw.php'>Terug naar de werkstroom</a></p>";
}

if ($_POST['delete']){
	$query = "DELETE FROM `schades_gebouw` WHERE ID='$id';";
	$result = mysql_query($query);
	if (!$result) {
		die("Verwijderen schade mislukt.". mysql_error());
	} else {
		echo "<p>Schade succesvol definitief verwijderd.<br>";
		echo "<a href='admin_schade_gebouw.php'>Terug naar de werkstroom</a></p>";
	}
}

if ($_POST['insert']){
	$name = $_POST['name'];
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
		$query = "UPDATE `schades_gebouw` SET Datum_gew='$today_db', Naam='$name', Oms_lang='$note', Feedback='$feedback', Actie='$action', Actiehouder='$action_holder', Prio='$prio', Realisatie='$real', Datum_gereed='$date_ready', Noodrep='$repair', Opmerkingen='$notes' WHERE ID='$id';";
	} else {
		$query = "INSERT INTO `schades_gebouw` (Datum, Datum_gew, Naam, Oms_lang, Feedback, Actie, Actiehouder, Prio, Realisatie, Datum_gereed, Noodrep, Opmerkingen) VALUES ('$today_db', '$today_db', '$name', '$note', '$feedback', '$action', '$action_holder', '$prio', '$real', '$date_ready', '$repair', '$notes');";
	}
	$result = mysql_query($query);
	if (!$result) {
		die("Aanmaken/bewerken schade mislukt.". mysql_error());
	} else {
		echo "<p>Schade succesvol aangemaakt/bewerkt.<br>";
		echo "<a href='admin_schade_gebouw.php'>Terug naar de werkstroom</a></p>";
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
	
	// mededeling
	echo "<div class='form-group'><label>Omschrijving (max. 1000 tekens)</label>";
	echo "<textarea name=\"note\" rows='4' class='form-control'/>$note</textarea>";
	echo "</div>";
	
	// feedback
	echo "<div class='form-group'><label>Feedback MatCie aan melder (max. 1000 tekens)</label>";
	echo "<textarea name=\"feedback\" rows='4' class='form-control'/>$feedback</textarea>";
	echo "</div>";
	
	// actie
	echo "<div class='form-group'><label>Actie (max. 1000 tekens)</label>";
	echo "<textarea name=\"action\" rows='4' class='form-control'/>$action</textarea>";
	echo "</div>";
	
	// actiehouder
	echo "<div class='form-group'><label>Actiehouder</label>";
	echo "<input type=\"text\" name=\"action_holder\" value=\"$action_holder\" class='form-control'>";
	echo "</div>";
	
	// prioriteit
	echo "<div class='form-group'><label>Prioriteit (1-3, 1 is hoogst)</label>";
	echo "<select name=\"prio\"class='form-control'>";
	for ($i = 1; $i < 4; $i++) {
		echo "<option value=\"".$i."\" ";
		if ($prio == $i) echo "selected";
		echo "/>".$i;
	}
	echo "</select>";
	echo "</div>";
	
	// realisatie
	echo "<div class='form-group'><label>% gerealiseerd (0-100)</label>";
	echo "<input type=\"text\" name=\"real\" value=\"$real\" class='form-control'>";
	echo "</div>";
	
	// datum gereed
	echo "Datum gereed</label>";
	echo "<input type='text' name='date_ready_sh' id='date_ready_sh' class='form-control' maxlength='10' value='$date_ready_sh'>";
	echo "</div>";
	
	// noodreparatie
	echo "<div class='form-group'><label>Noodreparatie (max. 1000 tekens)</label>";
	echo "<textarea name=\"repair\" rows='4' class='form-control'/>$repair</textarea>";
	echo "</div>";
	
	// opmerkingen
	echo "<div class='form-group'><label>Opmerkingen (max. 1000 tekens)</label>";
	echo "<textarea name=\"notes\" rows='4' class='form-control'/>$notes</textarea>";
	echo "</div>";
	
	// knoppen
	echo "<div class='form-group'>";
	echo "<input type=\"submit\" name=\"insert\" value=\"toevoegen\">&nbsp;";
	echo "<input type=\"submit\" name=\"delete\" value=\"Verwijderen\">";
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
