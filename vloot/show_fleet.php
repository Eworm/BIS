<?php

// NOTE: these links are only suitable for use from within DRUPAL!
include_once("bis/include_globalVars.php");
include_once("bis/include_helperMethods.php");

$bisdblink = mysql_connect($database_host, $database_user, $database_pass);
if (!mysql_select_db($database, $bisdblink)) {
	echo "Fout: database niet gevonden.<br>";
	exit();
}

?>

<h1>De vloot</h1>

<?php

// Categorie-selectie door gebruiker
$cat_to_show = 'Skiffs en C1en';
if ($_POST['cat_to_show']) {
	$cat_to_show = $_POST['cat_to_show'];
}
echo "<form name='form' action=\"$REQUEST_URI\" method=\"post\">";
echo "<div class='form-group'><label>Categorie</label>";
echo "<select name=\"cat_to_show\" class='form-control' />";
	$query = "SELECT DISTINCT Categorie FROM types ORDER BY Categorie;";
	$result = mysql_query($query);
	if (!$result) {
		die("Ophalen van categorie&euml;n mislukt.". mysql_error());
	}
	$c = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$cat_db = $row['Categorie'];
		echo "<option value=\"$cat_db\" ";
		if ($cat_to_show == $cat_db) echo "selected";
		echo "/> $cat_db";
		$c++;
	}
echo "</select></div>";
echo "<div class='form-group'><input type=\"submit\" name=\"submit\" value=\"Toon\" class='btn btn-default'/></div>";
echo "</form>";

$restrict_query_type = "";
$query = "SELECT Type FROM types WHERE Categorie='$cat_to_show';";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van types mislukt.". mysql_error());
}
$c = 0;
while ($row = mysql_fetch_assoc($result)) {
	if ($c > 0) $restrict_query_type .= " OR ";
	$restrict_query_type .= "Type='".$row['Type']."'";
	$c++;
}
$query = "SELECT Naam, Gewicht, Type, boten.Roeigraad FROM boten JOIN roeigraden ON boten.Roeigraad=roeigraden.Roeigraad WHERE Datum_eind IS NULL AND (".$restrict_query_type.") ORDER BY roeigraden.ID;";

// Mochten we ooit uit-de-vaart nog willen markeren:
//$query2 = "SELECT Reden FROM uitdevaart WHERE Verwijderd=0 AND Boot_ID='$boat_ids_array[$c]' AND Startdatum<='$date_to_show_db' AND (Einddatum='0' OR Einddatum='0000-00-00' OR Einddatum IS NULL OR Einddatum>='$date_to_show_db');";

$boats_result = mysql_query($query);
if (!$boats_result) {
	die("Ophalen van boten-informatie mislukt.". mysql_error());
} else {
	if (mysql_affected_rows() > 0) {
		echo "<table class=\"table\">";
		echo "<tr>";
		echo "<th><div align=\"left\">Naam</div></th>";
		echo "<th><div align=\"left\">Gewicht</div></th>";
		echo "<th><div align=\"left\">Type</div></th>";
		echo "<th><div align=\"left\">Graad</div></th>";
		echo "</tr>";
		$c = 0;
		$grade = "";
		$bgcolor = "";
		while ($row = mysql_fetch_assoc($boats_result)) {
			$boats_array[$c] = $row['Naam'];
			$boat_tmp = addslashes($boats_array[$c]);
			$weight = $row['Gewicht'];
			$type = $row['Type'];
			if ($grade != $row['Roeigraad']) {
				$grade = $row['Roeigraad'];
				$query_color = "SELECT KleurInBIS FROM roeigraden WHERE Roeigraad='$grade';";
				$result_color = mysql_query($query_color);
				if (!$result_color) {
					die("Ophalen van kleuren mislukt: ".mysql_error());
				} else {
					$row_color = mysql_fetch_assoc($result_color);
					$bgcolor = $row_color['KleurInBIS'];
				}
			}
			echo "<tr>";
			echo "<th bgcolor=$bgcolor><div align=\"left\">$boats_array[$c]</div></th>";
			echo "<td bgcolor=$bgcolor><div align=\"left\">$weight kg</div></td>";
			echo "<td bgcolor=$bgcolor><div align=\"left\">$type</div></td>";
			echo "<td bgcolor=$bgcolor><div align=\"left\">$grade</div></td>";
			echo "</tr>";
			$c++;
		}
		echo "</table>";
	}
}

mysql_close($bisdblink);

?>
