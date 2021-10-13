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

function export_cycle($db, $date_start, $date_end) {
	$sql = "SELECT date_obs, gommette, COALESCE(sensation,'') as sensation, COALESCE(jour_sommet, '') as sommet, COALESCE(union_sex, '') as 'unions', commentaire FROM observation WHERE date_obs>=:date_start AND date_obs<=:date_end";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date_start", format_date($date_start), PDO::PARAM_STR);
	$statement->bindValue(":date_end", format_date($date_end), PDO::PARAM_STR);
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

	$result["cycle_debut"] = date_parse(get_cycle($db, $date)[0]["cycle"]);
	$cycle_end = get_cycle_end($db, $date);
	if (isset($cycle_end[0]["cycle_end"])) $result["cycle_fin"] = date_parse($cycle_end[0]["cycle_end"]);
	else $result["cycle_fin"] = date_parse(date("Y-m-d"));

	$data = export_cycle($db, $result["cycle_debut"],$result["cycle_fin"]);

	header('Content-type: application/octet-stream');
	header('Content-Disposition: attachment; filename="bill_cycle_'. format_date($date) .'.csv"');

	$i = 1;
	print(implode(";",["jour","date","gommette","sensation","sommet", "unions", "commentaire"]));
	print(PHP_EOL);
	
	foreach ($data as $line){
		print($i . ";");
		print(implode(";",$line));
		print(PHP_EOL);
		$i += 1;
	}

	$db = null;
}
catch (Exception $e) {
	$result["err"] = $e->getMessage();
	$result["line"] = $e->getLine();
}


#print(json_encode($result));

