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
        
        <script src="../scripts/sortable.js"></script>
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">

<?php

if (!$_POST['submit']) {
	echo '<form name="form" action="' . $_SERVER['REQUEST_URI'] . '" method="post" class="form-inline">';
	// jaar
	echo "<div class='form-group'><label for=''>Jaar</label>";
	echo "<input type=\"text\" name=\"jaar\" value=\"$jaar\" size=\"4\" class='form-control' autofocus></div>";
	echo "<div class='form-group'><input type=\"submit\" name=\"submit\" value=\"Toon rapport\" class='btn btn-primary'></div>";
	echo "</form>";
}

if ($_POST['submit']) {
	$jaar = $_POST['jaar'];

	echo "<h1>Gebruikstotalen voor het jaar ".$jaar."</h1>";
	echo "<p>Let op: het totaal aantal dagen uit de vaart kan ingeval van overlappende uit-de-vaart-periodes hoger zijn dan in werkelijkheid.</p>";
	echo "<table class=\"table sortable\" id=\"jaarrapport\">";
	echo "<tr><td>Naam</td><td>Type</td><td>Roeisoort</td>";
	for ($i = 1; $i < 13; $i++) {
		echo "<td width=\"15\">$i</td>";
	}
	echo "<td width=\"15\">Totaal gebruik</td>";
	echo "<td># uit de vaart</td><td>Tot. dagen uit de vaart</td></tr>";
	
	$start_year = $jaar."-01-01";
	$end_year = $jaar."-12-31";
	$query1 = "SELECT boten.ID AS ID, Naam, types.Type AS Type, Roeisoort from boten JOIN types ON boten.Type = types.Type WHERE Datum_start <= '$end_year' AND (Datum_eind IS NULL OR Datum_eind >= '$start_year') ORDER BY Type;";
	$result1 = mysql_query($query1);
	if (!$result1) {
		die("Ophalen van boten mislukt.". mysql_error());
	}
	while ($row = mysql_fetch_assoc($result1)) {
		$boot_id = $row['ID'];
		$boot = $row['Naam'];
		$type = $row['Type'];
		$sort = $row['Roeisoort'];
		$tot = 0;
		echo "<tr><td><strong>$boot</strong></td><td>$type</td><td>$sort</td>";
		for ($maand = 1; $maand < 13; $maand++) {
			$query2 = "SELECT COUNT(*) AS MonthlyTot FROM ".$opzoektabel."_oud WHERE Verwijderd=0 AND Boot_ID='$boot_id' AND DATE_FORMAT(Datum,'%Y')=$jaar AND DATE_FORMAT(Datum,'%c')=$maand;";
			$result2 = mysql_query($query2);
			if (!$result2) {
				die("Tellen van inschrijvingen mislukt.". mysql_error());
			} else {
				$row = mysql_fetch_assoc($result2);
				$maand_tot = $row['MonthlyTot'];
				echo "<td>$maand_tot</td>";
				$tot += $maand_tot;
			}
		}
		echo "<td><strong>$tot</strong></td>";
		
		//Uit de vaart
		$query3 = "SELECT * 
			FROM uitdevaart 
			WHERE Boot_ID='$boot_id' 
			AND ((Startdatum >= '$start_year' AND Startdatum <= '$end_year') OR (Einddatum <= '$end_year' AND Einddatum > '$start_year') OR (Startdatum <= '$start_year' AND (Einddatum = '0000-00-00' OR Einddatum IS NULL)))";
		$result3 = mysql_query($query3);
		if (!$result3) {
			die("Ophalen van uit de vaart-meldingen mislukt.". mysql_error());
		} else {
			$nr_of_udvs = 0;
			$tot_duration = 0;
			while ($row = mysql_fetch_assoc($result3)) {
				$nr_of_udvs++;
				$end_date = $row['Einddatum'];
				if ($end_date == '0000-00-00' || $end_date == null) {
					$end_date = $today_db;
				}
				$end_date_parts = explode("-", $end_date);
				$start_date_parts = explode("-", $row['Startdatum']);
				// Bij meerjarige udv's, alleen gedeelte in gewenste jaar meetellen:
				if ($end_date_parts[0] > $jaar) $end_date_parts = explode("-", $end_year);
				if ($start_date_parts[0] < $jaar) $start_date_parts = explode("-", $start_year);
				$end_date = gregoriantojd($end_date_parts[1], $end_date_parts[2], $end_date_parts[0]);
				$start_date = gregoriantojd($start_date_parts[1], $start_date_parts[2], $start_date_parts[0]);
				$tot_duration += abs($end_date - $start_date);
			}
			echo "<td>$nr_of_udvs</td><td>$tot_duration</td></tr>";
		}
	}
	echo "</table>";
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
