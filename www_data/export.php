<?php

require_once "password.php";

// header('Content-Type: application/json');


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

function get_cycle($db, $date) {
	$sql = "SELECT date_obs AS cycle FROM observation WHERE premier_jour=1 and date_obs<=:date ORDER BY date_obs DESC LIMIT 1";

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
		
		$output = read_observation ($db, $date);
		$cycle = get_cycle($db, $date);
		
	}
	else {
		print("Vous devez etre connecte pour realiser cette action.");
		exit;

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

