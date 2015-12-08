<?php
// check login
session_start();
if (!isset($_SESSION['authorized']) || $_SESSION['authorized'] != 'yes' || $_SESSION['restrict'] != 'excie') {
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

$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$id = $_GET['id'];

if ($mode == "d") {
	$query = "DELETE FROM examen_inschrijvingen WHERE ID=" . $_GET['part_id'];
	$result = mysql_query($query);
	header('Location: admin_examinandi.php?id=' . $id);
	exit;
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
        <link type="text/css" href="../css/bis.css" rel="stylesheet">
            	
    </head>
    
<body>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-12">
    
<h1>Deelnemers</h1>

<?php
$query = "SELECT * FROM examen_inschrijvingen WHERE Ex_ID=" . $id;
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van kandidaten mislukt.". mysql_error());
}
echo "<table class=\"table\">";
echo "<tr><th><div>datum</div></th><th><div>tijd</div></th><th><div>Naam</div></th><th><div>10-11</div></th><th><div>11-12</div></th><th><div>s-1</div></th><th><div>s-2</div></th><th><div>s-3</div></th><th><div>w-1</div></th><th><div>w-2</div></th><th><div>s</div></th><th><div>S</div></th><th><div>g-1</div></th><th><div>g-2</div></th><th><div>g-3</div></th><th><div>T-1</div></th><th><div>T-2</div></th><th><div>examinator</div></th><th><div>tel.nr.</div></th><th><div>email</div></th><th><div>instr.eis</div></th><th><div>resultaat</div></th><th><div>webm</div></th><th><div>captain</div></th><th></th></tr>";
$c = 0;
while ($row = mysql_fetch_assoc($result)) {
	$part_id = $row['ID'];
	$name = $row['Naam'];
	$grade = $row['Graad'];
	$telph = $row['TelNr'];
	$email = $row['Email'];
	echo "<tr>";
	echo "<td><div>&nbsp;</div></td>";
	echo "<td><div>&nbsp;</div></td>";
	echo "<td><div>$name</div></td>";
	echo "<td><div>&nbsp;</div></td>";
	echo "<td><div>&nbsp;</div></td>";
	echo "<td><div>";
	if ($grade == "skiff-1") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>";
	if ($grade == "skiff-2") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>";
	if ($grade == "skiff-3") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>";
	if ($grade == "wherry-1") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>";
	if ($grade == "wherry-2") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>";
	if ($grade == "kleine-s") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>";
	if ($grade == "grote-S") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>";
	if ($grade == "giek-1") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>";
	if ($grade == "giek-2") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>";
	if ($grade == "giek-3") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>";
	if ($grade == "theorie-1") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>";
	if ($grade == "theorie-2") {echo "v";} else {echo "&nbsp;";}
	echo "</div></td>";
	echo "<td><div>&nbsp;</div></td>";
	echo "<td><div>$telph</div></td>";
	echo "<td><div>$email</div></td>";
	echo "<td><div>&nbsp;</div></td>";
	echo "<td><div>&nbsp;</div></td>";
	echo "<td><div>&nbsp;</div></td>";
	echo "<td><div>&nbsp;</div></td>";
	echo "<td><a href='admin_examinandi.php?mode=d&id=$id&part_id=$part_id' class='btn btn-danger'>Verwijder</a></td>";
	echo "</tr>";
}
echo "</table>";
echo "&nbsp;";

mysql_close($link);

?>

            <br><br>

        </div>
        
    </div>
    
</div>
</body>
</html>
