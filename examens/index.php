<?php
// check login
session_start();
if (!isset($_SESSION['authorized_bis']) || $_SESSION['authorized_bis'] != 'yes') {
	header("Location: ./bis_login.php");
	exit();
}

include_once("../include_globalVars.php");
include_once("../mail.php");

$link = mysql_connect($database_host, $database_user, $database_pass);
if (!mysql_select_db($database, $link)) {
	die('Fout: database niet gevonden.');
}

setlocale(LC_TIME, 'nl_NL');
?>

<!DOCTYPE html>
<html lang="nl">

    <head>
        <title>Examens - BIS</title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <link type="text/css" href="../css/bis.css" rel="stylesheet">
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-8">
            
            <h1>
                Examens
            </h1>

            <?php
            $openExamens = false;
            $query = "SELECT ID, Datum, Omschrijving, ToonOpSite FROM examens WHERE Datum > '" . $today_db . "' ORDER BY Datum";
            $result = mysql_query($query);
            if (!$result) {
            	echo("Ophalen van examendata mislukt: " . mysql_error());
            } else {
            	if (mysql_affected_rows($link) > 0) {
            		echo "<h2>Komende examens</h2><table class='table'>";
            		while ($row = mysql_fetch_assoc($result)) {
            			echo '<tr><td>' . $row['Omschrijving'] . ' op ' . strftime('%A %d-%m-%Y', strtotime($row['Datum']));
            			if ($row['ToonOpSite']) {
            				echo '</td><td><a href="examen.php?id=' . $row['ID'] . '" class="btn btn-primary">Aanmelden</a>';
            			} else {
            				echo '</td><td>Nog niet/niet meer open voor inschrijving';
            			}
            			echo '</td></tr>';
            		}
            		echo "</table>";
            	} else {
            		echo '<strong>Er zijn de komende tijd geen examens ingepland.</strong>';
            	}
            }
            ?>
            
            <?php if ($examenregels == "hunze") { ?>
                
                <hr>
                    
                <h2 class="h3">
                    Spelregels exameninschrijving
                </h2>
                
                <ul>
                  <li>Er kan per persoon per examen voor maximaal &eacute;&eacute;n te behalen graad worden ingeschreven.</li>
                  <li>Er zal eerst theorie-examen (T-1 of T-2) gedaan moeten worden voordat je aan een praktijkexamen mag deelnemen.</li>
                  <li>Inschrijven van boten en/of het regelen van roeiers bij een stuurexamen is de verantwoordelijkheid van de kandidaat.</li>
                  <li>Controleer het <a href="../../sites/default/files/Roei&examenreglement_2011.doc">Roei- en Examenreglement</a> op theorie- en exameneisen.</li>
                  <li>Op het <a href="../../sites/default/files/beoordelingalleexamens13_0.pdf">beoordelingsformulier</a> kunt u zien welke criteria de examinator hanteert bij het afnemen van het examen.</li>
                  <li>Je kunt <a href="../../sites/default/files/Examenvragen.doc">hier de examenvragen</a> T1 en T2 bekijken.</li>
                  <li>Zonder tegenbericht gaan examens altijd door.</li>
                  <li>Enige dagen voor het examen ontvangt u een indeling van roeiers/tijdstippen/examinatoren.</li>
                  <li>De duur van het theorie-examen en praktijkexamen is gemiddeld &#233;&#233;n uur.</li>
                </ul>
                
            <?php } ?>
            
        </div>
        
        <div class="col-md-4">
            
            <div class="well">

                <h4>
                    Mededelingen
                </h4>
                <p>
                    Er zijn met ingang van maart 2011 twee theorie-examens: T1 en T2. Pas wanneer men een T1-diploma op zak heeft is het mogelijk elk '&#233;&#233;n'-niveau praktijkexamen (i.e. skiff-1, wherry-1, C-1 en giek-1) te doen op elk gewenst algemeen examen.
                    <br><br>
                    Met een T2-'diploma' op zak kan men vervolgens alle overige roei- en stuurgraden doen.<br><br>
                    <strong>Voor T1 moet men hoofdstuk 1 en 2 en bijlage A, B, C: 5 basiscommando's (zie beoordelingsschema), E + G beheersen.</strong>
                    <br><br>
                    <strong>Voor T2 hoofdstuk 1 en 2 en alle bijlagen (behalve H).</strong>
                </p>

            </div>

        </div>
        
    </div>
    
</div>

</body>
</html>
