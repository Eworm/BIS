<?php
$locationHeader = 'Bestuurslid toevoegen/wijzigen';
$backLink = '<a href="admin_bestuur.php">Terug naar bestuursmenu</a>';
include 'admin_header.php';
?>

<?php
// ingeval van editen bestaande mededeling
$function_ex = $_GET['function'];
$query = "SELECT * FROM `bestuursleden` WHERE Functie='$function_ex';";
$result = mysql_query($query);
if ($result) {
	$rows_aff = mysql_affected_rows($link);
	if ($rows_aff > 0) {
		$row = mysql_fetch_assoc($result);
		$name = $row['Naam'];
		$function = $row['Functie'];
		$mail = $row['Email'];
		$mpb = $row['MPB'];
	}
}

// init
if (!$_POST['cancel'] && !$_POST['insert']) {
	$fail = FALSE;
}

// knop gedrukt
if ($_POST['cancel']){
	unset($_POST['name'], $_POST['mail'], $_POST['mpb'], $_POST['function'], $name, $mail, $mpb, $function);
	$fail = FALSE;
}

if ($_POST['insert']){
	$name = $_POST['name'];
	$function = $_POST['function'];
	$mail = $_POST['mail'];
	$mpb = 0;
	if ($_POST['mpb'] == 1) $mpb = 1;
	if ($function_ex) {
		$query = "UPDATE `bestuursleden` SET Naam='$name', Functie='$function', Email='$mail', MPB='$mpb' WHERE Functie='$function_ex';";
	} else {
		$query = "INSERT INTO `bestuursleden` (Naam, Functie, Email, MPB) VALUES ('$name', '$function', '$mail', '$mpb');";
	}
	$result = mysql_query($query);
	if (!$result) {
		die("toevoegen/wijzigen bestuurslid mislukt.". mysql_error());
	} else {
		echo "<p>Bestuurslid succesvol toegevoegd/gewijzigd.</p>";
	}
}

// Formulier
if ((!$_POST['insert'] && !$_POST['delete'] && !$_POST['cancel']) || $fail) {
	echo "<h1>Bestuurslid toevoegen/wijzigen</h1>";
	echo "<form name='form' action=\"$REQUEST_URI\" method=\"post\">";
	
	// naam
	echo "<div class='form-group'><label>Naam</label>";
	echo "<input type=\"text\" name=\"name\" value=\"$name\" class='form-control' autofocus>";
	echo "</div>";
	
	// functie
	echo "<div class='form-group'><label>Functie</label>";
	echo "<input type=\"text\" name=\"function\" value=\"$function\" class='form-control'>";
	echo "</div>";
	
	// mail
	echo "<div class='form-group'><label>E-mailadres</label>";
	echo "<input type=\"text\" name=\"mail\" value=\"$mail\" class='form-control'>";
	echo "</div>";
	
	// MPB
	echo "<div class='checkbox'><label>";
	echo "<input type=\"checkbox\" name=\"mpb\" value=1 ";
	if ($mpb == 1) echo "CHECKED";
	echo " class='radio'/>Geeft MPB?";
	echo "</label></div>";
	
	// knoppen
	echo "<div class='form-group'><input type=\"submit\" name=\"insert\" value=\"Toevoegen\" class='btn btn-primary'></div> ";
	echo "</form>";
}
?>

<?php include 'admin_footer.php'; ?>
