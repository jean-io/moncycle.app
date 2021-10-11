<?php

require_once "password.php";

// header('Content-Type: application/json');

session_start();
$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);


function format_date($date) {
	$d = $date['day']<10? "0" . $date['day'] : "" . $date['day'];
	$m = $date['month']<10? "0" . $date['month'] : "" . $date['month'];
	return "" . $date['year'] . "-" . $m . "-" . $d;
}

function read_observation ($db, $date) {
	$sql = "SELECT * FROM observation WHERE date_obs = :date LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_cycle($db, $date) {
	$sql = "SELECT date_obs AS cycle FROM observation WHERE premier_jour=1 and date_obs<=:date ORDER BY date_obs DESC LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_cycle_end($db, $date) {
	$sql = "SELECT date_obs AS cycle_end FROM observation WHERE premier_jour=1 and date_obs>:date ORDER BY date_obs ASC LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}



try {

	$db = new PDO('mysql:host=nas_ovpn;dbname=bill_nas', 'bill', DB_PASSWORD);


	// VERIFICATION DE LA BONNE OUVERTURE DE LA SESSION
	if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
		print("Vous devez etre connecte pour realiser cette action.");
		exit;
	}


	// LECTURE D'UNE DATE DE DEBUT DE CYCLE
	if (isset($_GET['cycle'])) {
		$date = date_parse($_GET['cycle']);
		$result["date"] = format_date($date);
	}
	else {
		print("Date du cycle non indique.");
		exit;
	}


//	SELECT date_obs, gommette, COALESCE(sensation,"") as sensation, COALESCE(jour_sommet, "") as j_sommet, COALESCE(union_sex, "") as "unions", commentaire FROM observation WHERE date_obs>=
//(SELECT date_obs AS cycle_debut FROM observation WHERE premier_jour=1 and date_obs<="2021-08-01" ORDER BY date_obs DESC LIMIT 1) and
//(date_obs < (SELECT date_obs AS cycle_fin FROM observation WHERE premier_jour=1 and date_obs>"2021-08-01" ORDER BY date_obs ASC LIMIT 1) or date_obs<CURRENT_DATE())

	$result["cycle_debut"] = get_cycle($db, $date)[0]["cycle"];
	$cycle_end = get_cycle_end($db, $date);
	if (isset($cycle_end[0]["cycle_end"])) $result["cycle_fin"] = $cycle_end[0]["cycle_end"];
	else $result["cycle_fin"] = date("Y-m-d");



	$db = null;
}
catch (Exception $e) {
	$result["err"] = $e->getMessage();
	$result["line"] = $e->getLine();
}


print(json_encode($result));

