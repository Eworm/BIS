<?php
$locationHeader = 'Wedstrijdblokken - Wedstrijdblok verwijderen';
$backLink = '<a href="admin_blokken.php" class="btn btn-default">Terug naar de wedstrijdblokken</a>';
include 'admin_header.php';
?>

<?php
$id = $_GET['id'];
$result = mysql_query(sprintf('DELETE FROM %s WHERE Wedstrijdblok = %d', $opzoektabel, $id));
if (!$result) {
	die("Verwijderen mislukt: " . mysql_error());
} else {
	echo "Wedstrijdblok succesvol verwijderd.";
}
?>

<?php include 'admin_footer.php'; ?>
