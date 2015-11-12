<?php
$locationHeader = 'Vlootbeheer - In/uit de vaart';
$backLink = '<a href="admin_vloot.php">Terug naar vlootbeheer</a>';
include 'admin_header.php';
?>

<?php
$arch = isset($_GET['arch']);
$boot_id = $_GET['id'];
$result = mysql_query('SELECT Naam, Type from boten WHERE ID = ' . $boot_id);
$row = mysql_fetch_assoc($result);
$name = $row['Naam'];

echo '<h1>' . ($arch ? 'Gearchiveerde u' : 'U') . 'it de Vaart-info voor: ' . $name . ' (' . $row['Type'] . ').</h1>';
echo ($arch ? '<a href="admin_inuitdevaart.php?id=' . $boot_id . '" class="btn btn-default">actueel</a>' : '<a href="admin_inuitdevaart.php?id=' . $boot_id . '&arch" class="btn btn-default">Archief</a>') . '&nbsp;';
echo '<a href="admin_uitdevaart_toev.php?id=' . $boot_id . '" class="btn btn-primary">Toevoegen</a><br><br>';

// tabel
$query = 'SELECT * from uitdevaart WHERE Verwijderd=' . ($arch ? '1' : '0') . ' AND Boot_ID=' . $boot_id . ' ORDER BY Startdatum DESC';
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van informatie mislukt.". mysql_error());
}
echo "<table class=\"table\">";
echo '<tr><th>Startdatum</th><th>Einddatum</th><th>Reden</th>';
if (!$arch) echo '<th><div>Aanpassen</div></th>';
echo '</tr>';
$c = 0;
while ($row = mysql_fetch_assoc($result)) {
	$udv_id = $row['ID'];
	$startdate = $row['Startdatum'];
	$startdate_sh = DBdateToDate($startdate);
	$enddate = $row['Einddatum'];
	if ($enddate == '' || $enddate == null) {
		$enddate_sh = '';
	} else {
		$enddate_sh = DBdateToDate($enddate);
	}
	$reason = $row['Reden'];
	echo "<tr>";
	echo "<td><div>$startdate_sh</div></td>";	
	echo "<td><div>$enddate_sh</div></td>";
	echo "<td><div>$reason</div></td>";
	if (!$arch) echo '<td><div><a href="admin_uitdevaart_verw.php?udv_id=' . $udv_id . '&boot_id=' . $boot_id . '" class="btn btn-default">Be&euml;indigen</a></div></td>';
	echo "</tr>";
	$c++;
}
echo "</table>";
echo "<p>NB: Meldingen met een verlopen einddatum worden automatisch gearchiveerd.</p>";
?>

<?php include 'admin_footer.php'; ?>
