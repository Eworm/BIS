<?php
$locationHeader = 'Bestuur';
$backLink = '<a href="index.php">Terug naar admin-menu</a>';
include 'admin_header.php';
?>

<?php
echo "<p><div><a href='./admin_bestuur_toev.php' class='btn btn-primary'>Bestuurslid toevoegen</a></div></p>";

$query = "SELECT * from bestuursleden ORDER BY Functie;";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van bestuursleden mislukt.". mysql_error());
}
echo "<br><table class=\"table\">";
echo "<tr><th><div>Functie</div></th><th><div>Naam</div></th><th><div>Email</div></th><th><div>Geeft MPB?</div></th><th colspan=2><div>&nbsp;</div></th></tr>";

$c = 0;
while ($row = mysql_fetch_assoc($result)) {
	$function = $row['Functie'];
	$name = $row['Naam'];
	$mail = $row['Email'];
	$mpb = $row['MPB'];
	echo "<tr>";
	echo "<td><div>$function</div></td>";	
	echo "<td><div>$name</div></td>";
	echo "<td><div>$mail</div></td>";
	echo "<td><div>";
	if ($mpb) {
		echo "ja";
	} else {
		echo "nee";
	}
	echo "</div></td>";
	echo "<td><div><a href=\"./admin_bestuur_toev.php?function=$function\">Wijzigen</a></div></td>";
	echo "<td><div><a href='admin_bestuur_verw.php?function=$function'>Verwijderen</a></div></td>";
	echo "</tr>";
	$c++;
}
echo "</table>";
?>

<?php include 'admin_footer.php'; ?>
