<?php
// check login
session_start();
if (!isset($_SESSION['authorized_bis']) || $_SESSION['authorized_bis'] != 'yes') {
	header("Location: ./bis_login.php");
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
        <title>Klachten gebouw/algemeen - BIS</title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
        <script type="text/javascript" language="javascript" src="../scripts/datatables/jquery.js"></script> 
        <script type="text/javascript" language="javascript" src="../scripts/datatables/jquery.dataTables.js"></script> 
    	
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
                
        <div class="col-md-12">
            
            <h1>
                Klachtenboek gebouw/algemeen
                <a href='schade_gebouw_toev.php' class='btn btn-primary'>Nieuwe klacht/schademelding</a>
            </h1>

            <?php
            
            $query = "SELECT Datum, Naam, Oms_lang, Feedback from schades_gebouw;";
            $result = mysql_query($query);
            if (!$result) {
            	die("Ophalen van klachten mislukt.". mysql_error());
            }
            echo "<table id='schades' class='table'>";
            echo "<thead><tr><th>Melddatum</th><th>Naam melder</th><th>Omschrijving</th><th>Terugkoppeling GebouwCie</th></tr></thead><tbody>";
            $c = 0;
            while ($row = mysql_fetch_assoc($result)) {
            	$date = $row['Datum'];
            	$name = $row['Naam'];
            	$note = $row['Oms_lang'];
            	if (!$note) $note = "&nbsp;";
            	$feedback = $row['Feedback'];
            	if (!$feedback) $feedback = "&nbsp;";
            	echo "<tr>";
            	echo "<td>$date</td>";	
            	echo "<td>$name</td>";
            	echo "<td>$note</td>";
            	echo "<td>$feedback</td>";
            	echo "</tr>";
            	$c++;
            }
            echo "</tbody></table>";
            
            mysql_close($link);
            
            ?>
            
        </div>
        
    </div>
    
</div>

</body>

<script type="text/javascript" charset="utf-8"> 
	$(document).ready(function() {
		$('#schades').dataTable( {
			"bPaginate": true,
		"sPaginationType": "full_numbers",
		"bLengthChange": true,
		"bAutoWidth": false,
		"bFilter": true,
		"bSort": true,
		"aaSorting": [[ 0, "desc" ]],
		"oLanguage": {
			"sLengthMenu": "Toon _MENU_ meldingen per pagina",
			"sZeroRecords": "Niets gevonden",
			"sInfo": "_START_ tot _END_ van _TOTAL_ meldingen",
			"sInfoEmpty": "Er zijn geen meldingen om te tonen",
			"sInfoFiltered": "(gefilterd uit _MAX_ meldingen)",
			"sSearch": "Zoek:",
			"oPaginate": {
				"sFirst":    "Eerste",
				"sPrevious": "Vorige",
				"sNext":     "Volgende",
				"sLast":     "Laatste"
			}
		},
		"aoColumns" : [
			{"sWidth": '100px'},
			{"sWidth": '100px'},
			{"sWidth": '300px'},
			{"sWidth": '200px'}
		]
	} );
} );
</script> 

</html>
