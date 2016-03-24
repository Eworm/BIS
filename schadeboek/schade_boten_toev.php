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
        <title>Nieuwe schademelding - BIS</title>
        
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

<div class="container-fluid main-container">
    	
    <div class="mainbox col-md-6 col-md-offset-3">
        	
    	<div class="panel panel-default">
        	
        	<main class="panel-body">
        
                <h1 class="h3">
                    Nieuwe schademelding boot
                </h1>
                
                <hr>
    
                <?php
                
                // init
                if (!isset($_POST['cancel']) && !isset($_POST['insert'])) {
                	$fail = FALSE;
                }
                
                // knop gedrukt
                if (isset($_POST['cancel'])){
                	unset($_POST['name'], $_POST['boat_id'], $_POST['note'], $name, $boat_id, $note);
                	$fail = FALSE;
                	echo "<p>De schade zal niet worden gemeld.<br>";
                	echo "<a href='index_boten.php'>Terug naar het schadeoverzicht voor de boten</a></p>";
                }
                
                if (isset($_POST['insert'])){
                	$name = $_POST['name'];
                	$boat_id = $_POST['boat_id'];
                	// bootnaam
                	if ($boat_id == 0) {
                		$boat = "algemeen";
                	} else {
                		$query2 = "SELECT Naam from boten WHERE ID=$boat_id;";
                		$result2 = mysql_query($query2);
                		$row2 = mysql_fetch_assoc($result2);
                		$boat = $row2['Naam'];
                	}
                	//
                	$note = addslashes($_POST['note']);
                	
                	if (!CheckName($name)) {
                		$fail_msg_name = "U dient een geldige voor- en achternaam op te geven. Let op: de apostrof (') wordt niet geaccepteerd.";
                	}
                	
                	if (isset($fail_msg_name)) $fail = TRUE;
                	
                	if (!isset($fail)) {
                		$query = "INSERT INTO `schades` (Datum, Naam, Boot_ID, Oms_lang) VALUES ('$today_db', '$name', '$boat_id', '$note');";
                		$result = mysql_query($query);
                		if (!$result) {
                			die("toevoegen klacht mislukt.". mysql_error());
                		} else {
                		    // mail aan matcom
                			$message = $name." heeft zojuist een schade gemeld betreffende '".$boat."'.<br>";
                			SendEmail("materiaal@hunze.nl", "Nieuwe schademelding", $message);
                			// feedback op scherm
                			echo "<p>Uw schademelding is doorgegeven aan de Materiaalcommissie.<br>";
                			echo "Mocht u de melding nog nader willen toelichten of willen wijzigen, neemt u dan contact op via <a href='mailto:materiaal@hunze.nl'>e-mail</a>.<br>";
                			echo "<br><br><a href='index_boten.php'>Terug naar het schadeoverzicht voor de boten</a></p>";
                		}
                	}
                }
                
                // Formulier
                if ((!isset($_POST['insert']) && !isset($_POST['delete']) && !isset($_POST['cancel'])) || (isset($fail) && $fail == true)) {
                	echo "<form name='form' action=\"" . (isset($REQUEST_URI) ? $REQUEST_URI : "") . "\" method=\"post\">";
                	
                	// naam
                	echo "<div class='form-group'><label for='name'>Uw naam</label>";
                	echo "<input type=\"text\" name=\"name\" id=\"name\" autofocus required value=\"" . (isset($name) ? $name : "") . "\" class=\"form-control\"></div>";
                	if (isset($fail_msg_name)) echo "<td><em>" . $fail_msg_name . "</em></td>";
                	echo "</tr>";
                	
                	// boot
                	echo "<div class='form-group'><label for='boat_id'>Naam boot/ergometer</label>";
                	echo "<select name=\"boat_id\" id=\"boat_id\" required class=\"form-control\">";
                	// optie 'algemeen' verwijderd op verzoek van Karel Engbers d.d. 03-10-2011
                	//echo "<option value=0 ";
                	//if ($boat_id == 0) echo "selected=\"selected\"";
                	//echo ">algemeen</option>";
                	$query = "SELECT ID, Naam, Type FROM boten WHERE Datum_eind IS NULL AND Type<>\"soc\" ORDER BY Naam;";
                	$boats_result = mysql_query($query);
                	if (!$boats_result) {
                		die("Ophalen van vlootinformatie mislukt.". mysql_error());
                	} else {
                		while ($row = mysql_fetch_assoc($boats_result)) {
                			$curr_boat_id = $row['ID'];
                			$curr_boat = $row['Naam'];
                			$type = $row['Type'];
                			echo "<option value=".$curr_boat_id." ";
                			if (isset($boat_id) && $boat_id == $curr_boat_id) echo "selected=\"selected\"";
                			echo ">".$curr_boat." (".$type.")</option>";
                		}
                	}
                	echo "</select>";
                	echo "</div>";
                	
                	// mededeling
                	echo "<div class='form-group'><label for='note'>Omschrijf kort de schade (max. 1000 tekens)</label>";
                	echo "<textarea name=\"note\" id=\"note\" required class=\"form-control\" rows=4>" . (isset($note) ? $note : "") . "</textarea>";
                	echo "</div>";
                	
                	// knoppen
                	echo "<p><input type=\"submit\" name=\"insert\" value=\"Toevoegen aan schadeboek\" class=\"btn btn-primary\" /> ";
                	echo "</form>";
                }
                
                mysql_close($link);
                
                ?>
            
            </main>
        
        </div>
        
    </div>
    
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

</body>
</html>
