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
        
    	<script language="JavaScript" src="../scripts/kalender.js"></script>
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">

<?php

$sortby = $_GET['sortby'];
if (!$sortby) $sortby = "Datum";

if (isset($_GET['mode'])) $mode = $_GET['mode'];


echo "<h1>Werkstroom Gebouwcommissie";
echo "<a href='admin_schade_gebouw_edit.php' class='btn btn-primary'>Maak zelf een schademelding aan</a>";
if (!isset($mode)) {
	echo "<p><a href='admin_schade_gebouw.php?mode=Arch' class='btn btn-default'>Toon gearchiveerde schades</a>";
} else {
	echo "<p><a href='admin_schade_gebouw.php' class='btn btn-default'>Toon actuele schades</a>";
}
echo "<a href='admin_schade_gebouw_export.php?mode=" . (isset($mode) ? $mode : "") . "' class='btn btn-default'>Exporteer onderstaande als CSV-bestand</a></h1>";

$source = "schades_gebouw";
if (isset($mode)) $source .= "_oud";
$query = "SELECT ".$source.".ID AS ID, Datum, Datum_gew, ".$source.".Naam AS Meldernaam, Oms_lang, Actiehouder, Prio, Realisatie, Datum_gereed FROM ".$source." ORDER BY ".$sortby.";";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van schades mislukt.". mysql_error());
}
echo "<br><table class=\"table\">";
echo "<tr><th><div><a href='admin_schade_gebouw.php?sortby=Datum'>Melddatum</a></div></th><th><div><a href='admin_schade_gebouw.php?sortby=Datum_gew'>Laatst gew.</a></div></th><th><div>Naam melder</div></th><th><div>Omschrijving</div></th><th><div>Actiehouder</div></th><th><div><a href='admin_schade_gebouw.php?sortby=Prio'>Prio</a></div></th><th><div><a href='admin_schade_gebouw.php?sortby=Realisatie'>Real. (%)</a></div></th><th><div>Gereed</div></th><th><div>&nbsp;</div></th>";
if (!isset($mode)) echo "<th><div>&nbsp;</div></th>";
echo "</tr>";
$c = 0;
while ($row = mysql_fetch_assoc($result)) {
	$id = $row['ID'];
	$date = $row['Datum'];
	$date_sh = DBdateToDate($date);
	$date_gew = $row['Datum_gew'];
	$date_gew_sh = DBdateToDate($date_gew);
	if ($date_gew_sh == "00-00-0000") $date_gew_sh = "-";
	$name = $row['Meldernaam'];
	$note = $row['Oms_lang'];
	$action = $row['Actiehouder'];
	$prio = $row['Prio'];
	$real = $row['Realisatie'];
	$date_ready = $row['Datum_gereed'];
	$date_ready_sh = DBdateToDate($date_ready);
	if ($date_ready_sh == "00-00-0000") $date_ready_sh = "-";
	echo "<tr>";
	echo "<td><div>$date_sh</div></td>";
	echo "<td><div>$date_gew_sh</div></td>";
	echo "<td><div>$name</div></td>";
	echo "<td><div>$note</div></td>";
	echo "<td><div>$action</div></td>";
	echo "<td><div>$prio</div></td>";
	echo "<td><div>$real</div></td>";
	echo "<td><div>$date_ready_sh</div></td>";
	if (!isset($mode)) echo "<td><div><a href='admin_schade_gebouw_edit.php?id=$id'>Bekijk/<br>bewerk</a></div></td>";
	if (isset($mode)) {
		echo "<td><div><a href='admin_schade_gebouw_verw.php?id=$id&mode=Arch'>De-arch.</a></div></td>";
	} else {
		echo "<td><div><a href='admin_schade_gebouw_verw.php?id=$id'>Arch.</a></div></td>";
	}
	echo "</tr>";
	$c++;
}
echo "</table>";

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
