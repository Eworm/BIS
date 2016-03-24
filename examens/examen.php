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
            
            <a href='index.php' class='btn btn-primary'>Naar examenoverzicht</a>


<?php
$id = $_GET['id'];
$result = mysql_query('SELECT Datum, Quotum, Omschrijving FROM examens WHERE ID = ' . $id);
if (!$result) {
	die('Ophalen van examengegevens mislukt: ' . mysql_error());
} else {
	if ($row = mysql_fetch_assoc($result)) {
		$exdate = $row['Datum'];
		$exdate_sh = strtotime($exdate);
		$quotum = $row['Quotum'];
		$description = $row['Omschrijving'];
		echo "<h3>Inschrijven voor " . $description . ' op ' . strftime('%A %d-%m-%Y', $exdate_sh) . "</h3>";
		echo "<table class=\"table\"><tr><th>&nbsp;</th><th>Naam</th><th>Examen</th></tr>";
		$result2 = mysql_query('SELECT Naam, Graad FROM examen_inschrijvingen WHERE Ex_ID = ' . $id);
		if (!$result2) {
			echo("Ophalen van exameninschrijvingen mislukt.".mysql_error());
		} else {
			$rows_aff2 = mysql_affected_rows($link);
			$c2 = 0;
			while ($row2 = mysql_fetch_assoc($result2)) {
				echo "<tr><td>".($c2+1)."</td><td>".$row2['Naam']."</td><td>".$row2['Graad']."</td></tr>";
				$c2++;
			}
			while ($c2 < $quotum) {
				echo "<tr><td>".($c2+1)."</td><td><a href='examen_inschr.php?id=$id' class='btn btn-default'>Aanmelden</a></td><td>&nbsp;</td></tr>";
				$c2++;
			}
		}
		echo "</table>";
	}
}
?>

        </div>
        
    </div>
    
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

</body>
</html>
