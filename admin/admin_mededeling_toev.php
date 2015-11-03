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
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">


<?php

// ingeval van editen bestaande mededeling
$id = $_GET['id'];
if ($id && ($id < 0 || !is_numeric($id))) { // check op ID
	echo "Er is iets misgegaan.";
	exit();
}
$query = "SELECT * FROM `mededelingen` WHERE ID='$id';";
$result = mysql_query($query);
if ($result) {
	$rows_aff = mysql_affected_rows($link);
	if ($rows_aff > 0) {
		$row = mysql_fetch_assoc($result);
		$name = $row['Bestuurslid'];
		$summary = $row['Betreft'];
		$note = $row['Mededeling'];
	}
}

// init
if (!$_POST['cancel'] && !$_POST['insert']) {
	$fail = FALSE;
}

// knop gedrukt
if ($_POST['cancel']){
	unset($_POST['name'], $_POST['summary'], $_POST['note'], $name, $summary, $note);
	$fail = FALSE;
}

if ($_POST['insert']){
	$name = $_POST['name'];
	$summary = addslashes($_POST['summary']);
	$note = addslashes($_POST['note']);
	if ($id) {
		$query = "UPDATE `mededelingen` SET Datum='$today_db', Bestuurslid='$name', Betreft='$summary', Mededeling='$note' WHERE ID='$id';";
	} else {
		$max1 = 1;
		$max2 = 1;
		$query = "SELECT MAX(ID) AS Max1 FROM `mededelingen`;";
		$result = mysql_query($query);
		if ($result) {
			$row = mysql_fetch_assoc($result);
			$max1 = $row['Max1'];
		}
		$query = "SELECT MAX(ID) AS Max2 FROM `mededelingen_oud`;";
		$result = mysql_query($query);
		if ($result) {
			$row = mysql_fetch_assoc($result);
			$max2 = $row['Max2'];
		}
		$new_id = max($max1, $max2) + 1;
		$query = "INSERT INTO `mededelingen` (ID, Datum, Bestuurslid, Betreft, Mededeling) VALUES ('$new_id', '$today_db', '$name', '$summary', '$note');";
	}
	$result = mysql_query($query);
	if (!$result) {
		die("toevoegen mededeling mislukt.". mysql_error());
	} else {
		echo "<p>Mededeling succesvol toegevoegd/gewijzigd.</p>";
	}
}

// Formulier
if ((!$_POST['insert'] && !$_POST['delete'] && !$_POST['cancel']) || $fail) {
	echo "<h1>Bestuursmededeling toevoegen</h1>";
	echo "<form name='form' action=\"$REQUEST_URI\" method=\"post\">";
	
	// naam
	echo "<div class='form-group'><label>Naam</label>";
	echo "<input type=\"text\" name=\"name\" value=\"$name\" class='form-control' autofocus />";
	echo "</div>";
	
	// betreft
	echo "<div class='form-group'><label>Betreft</label>";
	echo "<input type=\"text\" name=\"summary\" value=\"$summary\" class='form-control' />";
	echo "</div>";
	
	// mededeling
	echo "<div class='form-group'><label>Mededeling (max. 1000 tekens)</label>";
	echo "<textarea name=\"note\" rows=4 class='form-control'/>$note</textarea>";
	echo "</div>";
	
	// knoppen
	echo "<input type=\"submit\" name=\"insert\" value=\"toevoegen\" class='btn btn-primary' /> ";
	echo "</form>";
}

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
