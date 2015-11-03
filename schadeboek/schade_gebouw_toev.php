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
                    Nieuwe schademelding gebouw
                </h1>

                <hr>

                <?php
                
                // init
                if (!isset($_POST['cancel']) && !isset($_POST['insert'])) {
                	$fail = FALSE;
                }
                
                if (isset($_POST['insert'])){
                	$name = $_POST['name'];
                	$note = addslashes($_POST['note']);
                	
                	if (!CheckName($name)) {
                		$fail_msg_name = "U dient een geldige voor- en achternaam op te geven. Let op: de apostrof (') wordt niet geaccepteerd.";
                	}
                	
                	if (isset($fail_msg_name)) $fail = TRUE;
                	
                	if (!isset($fail)) {
                		$query = "INSERT INTO `schades_gebouw` (Datum, Naam, Oms_lang) VALUES ('$today_db', '$name', '$note');";
                		$result = mysql_query($query);
                		if (!$result) {
                			die("Invoeren klacht mislukt.". mysql_error());
                		} else {
                		    // mail aan gebcie
                			$message = $name." heeft zojuist een klacht gedaan:<br>".$note."<br>";
                			SendEmail("penningmeester@hunze.nl", "Nieuwe klacht/schademelding", $message);
                			// feedback op scherm
                			echo "<p>Hartelijk dank voor uw melding! De klacht is doorgegeven aan de Gebouwcommissie.<br>";
                			echo "Mocht u de melding nog nader willen toelichten of willen wijzigen, neemt u dan contact op via <a href='mailto:penningmeester@hunze.nl'>e-mail</a>.<br>";
                			echo "<a href='index_gebouw.php'>Terug naar het klachtenoverzicht voor het gebouw&gt;&gt;</a></p>";
                		}
                	}
                }
                
                // Formulier
                if ((!isset($_POST['insert']) && !isset($_POST['delete']) && !isset($_POST['cancel'])) || (isset($fail) && $fail == true)) {
                	echo "<form name='form' action=\"" . (isset($REQUEST_URI) ? $REQUEST_URI : "") . "\" method=\"post\">";
                	
                	// naam
                	echo "<div class='form-group'><label for='name'>Uw naam</label>";
                	echo "<input type=\"text\" name=\"name\" required id=\"name\" autofocus class=\"form-control\" value=\"" . (isset($name) ? $name : "") . "\">";
                	if (isset($fail_msg_name)) echo "<em>" . $fail_msg_name . "</em>";
                	echo "</div>";
                	
                	// mededeling
                	echo "<div class='form-group'><label for='note'>Omschrijf kort de schade (max. 1000 tekens)</label>";
                	echo "<textarea name=\"note\" id=\"note\" required class=\"form-control\" rows=4>" . (isset($note) ? $note : "") . "</textarea>";
                	echo "</div>";
                	
                	// knoppen
                	echo "<div class='form-group'><input type=\"submit\" name=\"insert\" value=\"Toevoegen aan klachtenboek\" class=\"btn btn-primary\"></div>";
                	echo "</form>";
                }
                
                mysql_close($link);
                
                ?>
                
            </main>
            
        </div>
        
    </div>
    
</div>

</body>
</html>
