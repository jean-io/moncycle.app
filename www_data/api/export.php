<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "../config.php";
require_once "../lib/date.php";
require_once "../lib/db.php";
require_once "../lib/doc.php";
require_once "../lib/sec.php";

require_once "../vendor/autoload.php";


$db = db_open();

$result = [];
$compte = sec_auth_jetton($db);
sec_redirect_non_connecte($compte);


// LECTURE D'UNE DATE DE DEBUT DE CYCLE
if (isset($_GET['start_date']) && preg_match("/^\s*\d{4}-\d{2}-\d{2}$/", $_GET["start_date"])) {
	$result["start_date"] = trim($_GET['start_date']);
}
else {
	http_response_code(400);
	print("ERREUR: date de démarrage non indiquée ou au mauvais format.");
	exit;
}

// LECTURE D'UNE DATE DE FIN DE CYCLE
if (isset($_GET['end_date']) && preg_match("/^\s*\d{4}-\d{2}-\d{2}$/", $_GET["end_date"])) {
	$result["end_date"] = trim($_GET['end_date']);
}
else {
	http_response_code(400);
	print("ERREUR: date de fin non indiquée ou au mauvais format.");
	exit;
}

// VERIFICATION D'ANTERIORITE
if (new DateTime($result["start_date"]) >= new DateTime($result["end_date"])) {
	http_response_code(400);
	print("ERREUR: la 'start_date' doit être antérieure à la 'end_date'.");
	exit;
}

// VERIFICATION DU FORMAT DE L'EXPORT
$available_type = ["pdf", "csv"];
if (!isset($_GET['type']) || !in_array($_GET['type'], $available_type)) {
	http_response_code(400);
	print("ERREUR: le format de l'export doit être: ");
	print(implode(", ", $available_type));
	exit;
}

// RECUPERATION DU CYCLE
$data = db_select_cycle_complet($db, $result["start_date"],$result["end_date"], $compte["no_compte"]);

// VERIFICATION SI IL Y A DE LA DONNEE
if (!isset($data[0])) {
	http_response_code(400);
	print("ERREUR: il n'y a pas d'observation pour la période demandée.");
	exit;
}

// AJOUT DES JOURS MANQUANTS DU CYCLE
$cycle = doc_preparation_jours_pour_affichage($data, $compte["methode"]);

$filename_start_date = date_humain(new DateTime($result["start_date"]), '_');

if ($_GET['type'] == "csv") {

	// ECRITURE DU CSV
	header("content-type:application/csv;charset=UTF-8");
	header('Content-Disposition: attachment; filename="moncycle_app_'. $filename_start_date .'.csv"');
	$out = fopen('php://output', 'w');
	doc_cycle_vers_csv ($out, $cycle, $compte["methode"]);
	fclose($out);
}
elseif ($_GET['type'] == "pdf") {
	header("content-type:application/pdf");
	header('Content-Disposition: attachment; filename="moncycle_app_'. $filename_start_date .'.pdf"');
	$pdf = null;
	if ($compte["methode"] == 3 || $compte["methode"] == 4) $pdf = doc_cycle_fc_vers_pdf($cycle, $compte["methode"], $compte["nom_compte"]);
	else $pdf = doc_cycle_bill_vers_pdf($cycle, $compte["methode"], $compte["nom_compte"]);
	$pdf->Output('I', 'moncycle_app_'. $filename_start_date . '.pdf');
}

