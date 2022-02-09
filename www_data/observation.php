<?php

require_once "password.php";

header('Content-Type: application/json');


session_start();
$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);


function format_date($date) {
	$d = $date['day']<10? "0" . $date['day'] : "" . $date['day'];
	$m = $date['month']<10? "0" . $date['month'] : "" . $date['month'];
	return "" . $date['year'] . "-" . $m . "-" . $d;
}

function read_observation ($db, $date) {
	$sql = "SELECT * FROM observation WHERE date_obs = :date AND no_compte = :no_compte LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $_SESSION["no"], PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function create_observation ($db, $date) {
	$sql = "INSERT INTO observation (no_compte, date_obs, gommette) VALUES (:no_compte, :date, '')";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $_SESSION["no"], PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function update_observation ($db, $date, $gommette='', $sensation=null, $jour_sommet=null, $union_sex=null, $premier_jour=null, $commentaire=null) {
	$sql = "UPDATE observation SET gommette = :gommette, sensation = :sensation, jour_sommet = :jour_sommet, union_sex = :union_sex, premier_jour = :premier_jour, commentaire = :commentaire WHERE date_obs = :date AND no_compte = :no_compte";

	$statement = $db->prepare($sql);
	$statement->bindValue(":gommette", $gommette, PDO::PARAM_STR);
	$statement->bindValue(":sensation", $sensation, PDO::PARAM_STR);
	$statement->bindValue(":jour_sommet", $jour_sommet, PDO::PARAM_INT);
	$statement->bindValue(":union_sex", $union_sex, PDO::PARAM_INT);
	$statement->bindValue(":premier_jour", $premier_jour, PDO::PARAM_INT);
	$statement->bindValue(":commentaire", $commentaire, PDO::PARAM_STR);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $_SESSION["no"], PDO::PARAM_INT);
	
	$statement->execute();
	//$statement->debugDumpParams();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_cycle($db, $date) {
	$sql = "SELECT date_obs AS cycle FROM observation WHERE premier_jour=1 AND date_obs<=:date AND no_compte = :no_compte ORDER BY date_obs DESC LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $_SESSION["no"], PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);

}

$result = [];

try {

	$db = new PDO('mysql:host=nas_ovpn;dbname=dev_moncyle_app_nas', 'jean_dev', DB_PASSWORD);


	// VERIFICATION DE LA BONNE OUVERTURE DE LA SESSION
	if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
		$result["err"] = "please login first";
	}


	// LECTURE D'UNE OBSERVATION
	elseif (isset($_GET['date'])) {

		$result["command"] = "GET";

		$date = date_parse($_GET['date']);
		$result["date"] = format_date($date);
		
		$output = read_observation ($db, $date);
		$cycle = get_cycle($db, $date);
		
		$interval = date_diff(date_create($cycle[0]["cycle"]), date_create($result["date"]));
		$result["pos"] = intval($interval->format('%a'))+1;

		$result = array_merge($result, $cycle[0]);
		
		if(isset($output[0])) $result = array_merge($result, $output[0]);
		else $result["err"] = "no data at this date"; 
	}

	// CREATION ET MISE A JOUR D'UNE OBSERVATION
	elseif(isset($_POST['date'])) {

		$result["command"] = "POST";
		
		$date = date_parse($_POST['date']);
		$result["date"] = format_date($date);

		$output = read_observation ($db, $date);

		if(!isset($output[0])){
			create_observation($db, $date);
		}
		
		$sensation = [];
		foreach ($_POST as $key => $p) {
			if (!str_starts_with($key, "ob_") || $p=="") continue;
			array_push($sensation, trim($p));
		}
		$sensation_db = implode(", ", $sensation);
		if ($sensation_db == "") $sensation_db = null;

		update_observation ($db, $date, $_POST["gommette"] ?? '', $sensation_db, $_POST["jour_sommet"] ?? null, $_POST["union_sex"] ?? null, $_POST["premier_jour"] ?? null, $_POST["commentaire"] ?? null);
		$result["outcome"] = "ok";
		$result["args"] = $_POST;
	}

	else {
		$result["err"] = "missing an action and a date";
	}

	$db = null;
}
catch (Exception $e) {
	$result["err"] = $e->getMessage();
	$result["line"] = $e->getLine();
}


print(json_encode($result));

