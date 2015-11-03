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
        
        <script type="text/javascript" src="../scripts/sortable.js"></script>
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">

<?php
$fail = false;
$ploeg_te_tonen = "alle";
if (isset($_POST['ploeg_te_tonen'])) {
	$ploeg_te_tonen = $_POST['ploeg_te_tonen'];
} else {
	if (isset($_GET['ploeg_te_tonen'])) {
		$ploeg_te_tonen = $_GET['ploeg_te_tonen'];
	}
}

echo "<h1>Actieve repeterende spitsblokken</h1>";
echo "<p><a href=\"./admin_spits_toev.php\" class='btn btn-primary'>Toevoegen</a></p>";

echo '<form class="form-inline" name="form" action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
echo "<div class='form-group'>Beperk tot ploeg <select name=\"ploeg_te_tonen\" class='form-control'>";
echo "<option value=\"alle\"";
if ($ploeg_te_tonen == "alle") echo "selected=\"selected\"";
echo ">alle</option>";
echo "<option value=\"geen ploegnaam\"";
if ($ploeg_te_tonen == "geen ploegnaam") echo "selected=\"selected\"";
echo ">geen ploegnaam</option>";
$query = "SELECT DISTINCT Ploegnaam from ".$opzoektabel." WHERE Verwijderd=0 AND Spits>0 ORDER BY Ploegnaam;";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van informatie mislukt.". mysql_error());
} else {
	while ($row = mysql_fetch_assoc($result)) {
		$ploegnaam = $row['Ploegnaam'];
		if ($ploegnaam != "") {
			echo"<option value=\"".$ploegnaam."\" ";
			if ($ploeg_te_tonen == $ploegnaam) echo "selected=\"selected\"";
			echo ">".$ploegnaam."</option>";
		}
	}
}
echo "</select></div>";
echo "<div class='form-group'><input type=\"submit\" name=\"submit_ploegnaam\" value=\"Toon spitsblokken\" class='btn btn-default'></div>";
echo "</form><br /><br />";

// tabel
echo "<table class=\"table sortable\" id=\"spits\">";
echo "<tr><td>MPB</td>";
echo "<td>Startdatum</td>";
echo "<td>Einddatum</td>";
echo "<td>Starttijd</td>";
echo "<td>Eindtijd</td>";
echo "<td>Boot</td>";
echo "<td>Naam</td>";
echo "<td>Ploegnaam</td>";
echo "<td>E-mail</td>";
echo "<td colspan=\"2\"></td></tr>";

$restrict_query = "";
if ($ploeg_te_tonen != "alle") {
	if ($ploeg_te_tonen == "geen ploegnaam") {
		$restrict_query = "AND Ploegnaam=\"\" ";
	} else {
		$restrict_query = "AND Ploegnaam=\"$ploeg_te_tonen\" ";
	}
}
$query = sprintf('SELECT DISTINCT Spits 
		FROM %s 
		WHERE Verwijderd=0 
		AND Spits>0 
		%s 
		ORDER BY Spits', $opzoektabel, $restrict_query);
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van informatie mislukt: " . mysql_error());
} else {
	while ($row = mysql_fetch_assoc($result)) {
		$spits_id = $row['Spits'];
		$query2 = "SELECT MPB, Datum, Begintijd, Eindtijd, Boot_ID, Pnaam, Ploegnaam, Email from ".$opzoektabel." WHERE Verwijderd=0 AND Spits=$spits_id ORDER BY Datum;";
		$result2 = mysql_query($query2);
		if (!$result2) {
			die("Ophalen van informatie mislukt: " . mysql_error());
		} else {
		    // uit eerste record kun je alles al halen, behalve -bij meer dan 1 inschrijving- de einddatum
			$row2 = mysql_fetch_assoc($result2);
			$mpb = $row2['MPB'];
			$startdate = $row2['Datum'];
			$startdate_sh = strftime('%A %d-%m-%Y', strtotime($startdate));
			//$startdate_sh = DBdateToDate($startdate);
			$starttime = $row2['Begintijd'];
			$endtime = $row2['Eindtijd'];
			$boat_id = $row2['Boot_ID'];
			// bootnaam
			$query_boatname = "SELECT Naam from boten WHERE ID=$boat_id;";
			$result_boatname = mysql_query($query_boatname);
			$row_boatname = mysql_fetch_assoc($result_boatname);
			$boat = $row_boatname['Naam'];
			//
			$pname = $row2['Pnaam'];
			$name = $row2['Ploegnaam'];
			$email = $row2['Email'];
			$enddate = $row2['Datum'];
			while ($row2 = mysql_fetch_assoc($result2)) {
				$enddate = $row2['Datum'];
			}
			$enddate_sh = strftime('%A %d-%m-%Y', strtotime($enddate));
			echo "<tr>";
			echo "<td>$mpb</td>";
			echo "<td>$startdate_sh</td>";	
			echo "<td>$enddate_sh</td>";
			echo "<td>$starttime</td>";
			echo "<td>$endtime</td>";
			echo "<td>$boat</td>";
			echo "<td>$pname</td>";
			echo "<td>$name</td>";
			echo "<td>$email</td>";
			echo "<td><a href=\"./admin_spits_toev.php?id=$spits_id\">Wijzigen</a></td>";
			echo "<td><a href=\"./admin_spits_verw.php?id=$spits_id\">Verwijderen</a></td>";
			echo "</tr>";
		}
	}
}

echo "</table>";

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
