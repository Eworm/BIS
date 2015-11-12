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
                
        <script src="../scripts/sortable.js"></script>
    	
    </head>
    
<body>
    
<?php
  
    include('../includes/navbar-admin.php');
  
  
    if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];
    if (!isset($sortby)) $sortby = "Datum";

    if (isset($_GET['mode'])) $mode = $_GET['mode'];
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-12">
            
            <h1>
                Werkstroom Materiaalcommissie
                <a href='admin_schade_edit.php' class="btn btn-primary">Schademelding toevoegen</a>

                <?php                
                if (!isset($mode)) {
                	echo "<a href='admin_schade.php?mode=Arch' class='btn btn-default'>Gearchiveerde schades</a>";
                } else {
                	echo "<a href='admin_schade.php' class='btn btn-default'>Actuele schades</a>";
                }
                ?>
                
                <a href='admin_schade_export.php?mode="<?php (isset($mode) ? $mode : "") ?>'>Exporteer als Excel</a>
            </h1>
            
<?php

$source = "schades";
if (isset($mode)) $source .= "_oud";
$query = "SELECT ".$source.".ID AS ID, Datum, Datum_gew, ".$source.".Naam AS Meldernaam, Boot_ID, boten.Naam AS Bootnaam, Oms_lang, Actiehouder, Prio, Realisatie, Datum_gereed FROM ".$source." LEFT JOIN boten ON ".$source.".Boot_ID=boten.ID ORDER BY " . $sortby . (preg_match("/^Datum/", $sortby) ? " DESC" : "") . ";";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van schades mislukt.". mysql_error());
}
echo "<br><table class=\"table\">";
echo "<tr><th><div><a href='admin_schade.php?sortby=Datum" . (isset($mode) ? ("&mode=" . $mode) : "") . "'>Melddatum</a></div></th>";
echo "<th><div><a href='admin_schade.php?sortby=Datum_gew" . (isset($mode) ? ("&mode=" . $mode) : "") . "'>Laatst gew.</a></div></th>";
echo "<th><div>Naam melder</div></th>";
echo "<th><div><a href='admin_schade.php?sortby=boten.Naam" . (isset($mode) ? ("&mode=" . $mode) : "") . "'>Boot/ergometer</a></div></th>";
echo "<th><div>Omschrijving</div></th>";
echo "<th><div>Actiehouder</div></th>";
echo "<th><div><a href='admin_schade.php?sortby=Prio" . (isset($mode) ? ("&mode=" . $mode) : "") . "'>Prio</a></div></th>";
echo "<th><div><a href='admin_schade.php?sortby=Realisatie" . (isset($mode) ? ("&mode=" . $mode) : "") . "'>Real. (%)</a></div></th>";
echo "<th><div>Gereed</div></th>";
echo "<th><div>&nbsp;</div></th>";
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
	$boat_id = $row['Boot_ID'];
	$boat = $row['Bootnaam'];
	if ($boat == "") {
		$boat = "algemeen";
	}
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
	echo "<td><div>$boat</div></td>";
	echo "<td><div>$note</div></td>";
	echo "<td><div>$action</div></td>";
	echo "<td><div>$prio</div></td>";
	echo "<td><div>$real</div></td>";
	echo "<td><div>$date_ready_sh</div></td>";
	if (!isset($mode)) echo "<td><div><a href='admin_schade_edit.php?id=$id'>Bekijk/<br>bewerk</a></div></td>";
	if (isset($mode)) {
		echo "<td><div><a href='admin_schade_verw.php?id=$id&mode=Arch'>De-arch.</a></div></td>";
	} else {
		echo "<td><div><a href='admin_schade_verw.php?id=$id'>Arch.</a></div></td>";
	}
	echo "</tr>";
	$c++;
}
echo "</table>";

mysql_close($link);

?>

            <br><br>
            
        </div>
        
    </div>

</div>
</body>
</html>
