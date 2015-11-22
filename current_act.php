<?php
// check login
session_start();
if (!isset($_SESSION['authorized_bis']) || $_SESSION['authorized_bis'] != 'yes') {
	header("Location: bis_login.php");
	exit();
}

include_once("include_globalVars.php");
include_once("include_helperMethods.php");

setlocale(LC_TIME, 'nl_NL');

$link = mysql_connect($database_host, $database_user, $database_pass);
if (!mysql_select_db($database, $link)) {
	echo "Fout: database niet gevonden.<br>";
	exit();
}

?>

<!DOCTYPE html>
<html lang="nl">

    <head>
        <title><?php echo $systeemnaam; ?></title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <link type="text/css" href="css/bis.css" rel="stylesheet">
    	
    </head>
    
<body>

<?php
  
  include('includes/navbar.php');
    
?>

<div class="container-fluid main-container">
    	
	<div class="mainbox col-md-6 col-md-offset-3">
        	
    	<div class="panel panel-default">
        	
        	<main class="panel-body">
        
                <h1 class="h3">
                    Wie is nu op het water?
                </h1>
                
                <hr>
                
                <?php
    
                $date_tmp = strtotime($today_db);
                $date_sh = strftime('%A %d-%m-%Y', $date_tmp);
                echo "<p><strong>Het is $date_sh, $thetime</strong></p>";
                
                $query = "SELECT boten.ID AS ID, boten.Naam AS Boot, Pnaam, Ploegnaam, Eindtijd from ".$opzoektabel." JOIN boten ON ".$opzoektabel.".Boot_ID=boten.ID WHERE Verwijderd=0 AND Datum='$today_db' AND Begintijd<='$thetime' AND Eindtijd>='$thetime' AND boten.Type<>\"ergo\" AND boten.Type<>\"soc\" ORDER BY Eindtijd;";
                $result = mysql_query($query);
                if (!$result) {
                	die("Ophalen van inschrijvingen mislukt.". mysql_error());
                } else {
                	$rows_aff = mysql_affected_rows($link);
                	if ($rows_aff > 0) {
                		echo "<table class='table'>";
                		while ($row = mysql_fetch_assoc($result)) {
                			$boat_id = $row['ID'];
                			$db_boat = $row['Boot'];
                			$query2 = "SELECT * 
                				FROM uitdevaart 
                				WHERE Verwijderd=0 
                				AND Boot_ID='$boat_id' 
                				AND Startdatum<='$today_db' 
                				AND (Einddatum='0' OR Einddatum='0000-00-00' OR Einddatum IS NULL OR Einddatum>='$today_db');";
                			$result2 = mysql_query($query2);
                			if (!$result2) {
                				die("Ophalen van Uit de Vaart-informatie mislukt.". mysql_error());
                			} else {
                				$rows_aff2 = mysql_affected_rows($link);
                				if ($rows_aff2 == 0) {
                    				echo '<tr><td>';
                					$db_pname = $row['Pnaam'];
                					$db_name = "(".$row['Ploegnaam'].")";
                					if ($db_name == "()") $db_name = "";
                					$db_endtime = substr($row['Eindtijd'], 0, 5);
                					echo "$db_pname $db_name</td><td>$db_boat</td><td>Tot $db_endtime";
                					echo '</tr></td>';
                				}
                			}
                		}
                		echo "</table>";
                	} else {
                		echo "<p>Er zijn geen boten ingeschreven.</p>";
                	}
                }
                
                $tot_ergo = 0;
                $query = "SELECT count(*) AS TotErgo from ".$opzoektabel." JOIN boten ON ".$opzoektabel.".Boot_ID=boten.ID WHERE Verwijderd=0 AND Datum='$today_db' AND Begintijd<='$thetime' AND Eindtijd>='$thetime' AND boten.Type=\"ergo\";";
                $result = mysql_query($query);
                if (!$result) {
                	die("Ophalen van inschrijvingen mislukt.". mysql_error());
                } else {
                	$row = mysql_fetch_assoc($result);
                	$tot_ergo = $row['TotErgo'];
                }
                echo "<p>Aantal ergometers dat nu is ingeschreven: $tot_ergo</p>";
                echo "</div>";
                
                mysql_close($link);
                
                ?>
            </main>
            
        </div>

    </div>
    
</div>

</body>
</html>
