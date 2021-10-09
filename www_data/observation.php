<?php


require_once "password.php";

header('Content-Type: application/json');


session_start();


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

function create_observation ($db, $date) {
	$sql = "INSERT INTO observation (date_obs, gommette) VALUES (:date, '')";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function update_observation ($db, $date, $gommette='', $sensation=null, $jour_sommet=null, $union_sex=null, $premier_jour=null, $commentaire=null) {
	$sql = "UPDATE observation SET gommette = :gommette, sensation = :sensation, jour_sommet = :jour_sommet, union_sex = :union_sex, premier_jour = :premier_jour, commentaire = :commentaire WHERE date_obs = :date";

	$statement = $db->prepare($sql);
	$statement->bindValue(":gommette", $gommette, PDO::PARAM_STR);
	$statement->bindValue(":sensation", $sensation, PDO::PARAM_STR);
	$statement->bindValue(":jour_sommet", $jour_sommet, PDO::PARAM_INT);
	$statement->bindValue(":union_sex", $union_sex, PDO::PARAM_INT);
	$statement->bindValue(":premier_jour", $premier_jour, PDO::PARAM_INT);
	$statement->bindValue(":commentaire", $commentaire, PDO::PARAM_STR);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	
	$statement->execute();
	//$statement->debugDumpParams();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_cycle($db, $date) {
	$sql = "SELECT date_obs AS cycle FROM observation WHERE premier_jour=1 and date_obs<=:date ORDER BY date_obs DESC LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);

}

$result = [];

try {

	$db = new PDO('mysql:host=nas_ovpn;dbname=bill_nas', 'bill', DB_PASSWORD);


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

