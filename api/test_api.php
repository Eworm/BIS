<?php
$username = $_GET['username'];
$password = $_GET['password'];

// 1. get token
$url = "http://www.hunze.nl/test_bis/api/index.php?getToken=1&username=" . $username . "&password=" . md5($password);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$token = curl_exec($ch);
curl_close($ch);
echo "My token is: " . $token . "<br /><br />";

// 2. get boats
$url = "http://www.hunze.nl/test_bis/api/index.php?token=" . $token . "&entity=boten";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$boats = curl_exec($ch);
curl_close($ch);
$boatsArray = json_decode($boats);
$nrOfBoats = sizeof($boatsArray);
echo "I got " . $nrOfBoats . " boats from BIS<br />";
if ($nrOfBoats > 20 && $nrOfBoats < 200) {
	echo "Seems OK<br /><br />";
} else {
	echo "Seems wrong, but I'll try to keep going...<br /><br />";
}

// 3. make valid reservation
$tomorrow = date("d-m-Y", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
$url = "http://www.hunze.nl/test_bis/api/index.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "token=" . $token . "&res_id=0&boat_id=2&name=Erik%20Roos&date=" . $tomorrow . "&start_time_hrs=9&start_time_mins=00&end_time_hrs=11&end_time_mins=00");
$res = curl_exec($ch);
curl_close($ch);
echo "I tried to make a valid reservation for tomorrow 9:00-11:00 and I got as feedback: " . $res . "<br />";
if (substr($res, 0, 8) == "<p>Beste") {
	echo "Went OK<br /><br />";
} else {
	echo "Went wrong!<br /><br />";
	die;
}

// 4. retrieve reservation
$tomorrow_db = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
$url = "http://www.hunze.nl/test_bis/api/index.php?token=" . $token . "&entity=test_inschrijvingen&date=" . $tomorrow_db;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$boats = curl_exec($ch);
curl_close($ch);
$resArray = json_decode($boats);
$resId = -1;
foreach ($resArray as $res) {
	if ($res->Verwijderd == 0 && $res->Boot_ID == 2 && $res->Begintijd == '09:00:00') {
		$resId = $res->Volgnummer;
		echo "Found back reservation, ID = " . $resId . "<br /><br />";
		break;
	}
}
if ($resId == -1) {
	echo "Reservation not found, something went wrong!<br /><br />";
	die;
}

// 5. change reservation
$url = "http://www.hunze.nl/test_bis/api/index.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "token=" . $token . "&res_id=" . $resId . "&boat_id=2&name=Erik%20Roos&date=" . $tomorrow . "&start_time_hrs=10&start_time_mins=00&end_time_hrs=12&end_time_mins=00");
$res = curl_exec($ch);
curl_close($ch);
echo "I tried to change the reservation to tomorrow 10:00-12:00 and I got as feedback: " . $res . "<br />";
if (substr($res, 0, 8) == "<p>Beste") {
	echo "Went OK<br /><br />";
} else {
	echo "Went wrong!<br /><br />";
	die;
}

// 4. retrieve altered reservation
$url = "http://www.hunze.nl/test_bis/api/index.php?token=" . $token . "&entity=test_inschrijvingen&date=" . $tomorrow_db;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$boats = curl_exec($ch);
curl_close($ch);
$resArray = json_decode($boats);
$resId = -1;
foreach ($resArray as $res) {
	if ($res->Verwijderd == 0 && $res->Boot_ID == 2 && $res->Begintijd == '10:00:00') {
		$resId = $res->Volgnummer;
		echo "Found back altered reservation, ID = " . $resId . "<br /><br />";
		break;
	}
}
if ($resId == -1) {
	echo "Altered reservation not found, something went wrong!<br /><br />";
	die;
}

// 7. delete reservation
$url = "http://www.hunze.nl/test_bis/api/index.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "token=" . $token . "&delete=1&res_id=" . $resId);
$del = curl_exec($ch);
curl_close($ch);
echo "I tried to delete the reservation and I got as feedback: " . $del . "<br />";
if (substr($del, 0, 33) == "<p>De inschrijving is verwijderd.") {
	echo "Went OK<br /><br />";
} else {
	echo "Went wrong!<br /><br />";
	die;
}

// 8. make a deliberately wrong reservation
$url = "http://www.hunze.nl/test_bis/api/index.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "token=" . $token . "&res_id=0&boat_id=2&name=Erik%20Roos&date=" . $tomorrow . "&start_time_hrs=14&start_time_mins=00&end_time_hrs=17&end_time_mins=00");
$res = curl_exec($ch);
curl_close($ch);
echo "I tried to make an invalid reservation for tomorrow 14:00-17:00 without MPB and I got as feedback: " . $res . "<br />";
if (substr($res, 0, 8) == "<p>Beste") {
	echo "Reservation was accepted, so something went wrong!<br /><br />";
	die;
} else {
	echo "Went OK<br /><br />";
}

echo "SUCCESS! - END OF TEST";