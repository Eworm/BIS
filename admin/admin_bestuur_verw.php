<?php
$locationHeader = 'Bestuurslid verwijderen';
$backLink = '<a href="admin_bestuur.php" class="btn btn-default">Terug naar bestuursmenu</a>';
include 'admin_header.php';
?>

<?php
$function = $_GET['function'];

$query = 'DELETE FROM bestuursleden WHERE Functie = ' . $function;
$result = mysql_query($query);
if (!$result) {
	die("Verwijderen bestuurslid mislukt: " . mysql_error());
} else {
	echo "Verwijderen bestuurslid gelukt.<br>";
}
?>

<?php include 'admin_footer.php'; ?>
