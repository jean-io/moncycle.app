<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "config.php";
require_once "lib/date.php";
require_once "lib/db.php";
require_once "lib/doc.php";
require_once "vendor/fpdf/fpdf/src/Fpdf/Fpdf.php";

session_start();

// VERIFICATION DE LA BONNE OUVERTURE DE LA SESSION
if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
	print("Vous devez etre connecte pour realiser cette action.");
	exit;
}

// LECTURE D'UNE DATE DE DEBUT DE CYCLE
if (isset($_GET['cycle'])) {
	$date = new DateTime($_GET['cycle']);
	$result["date"] = date_sql($date);
}
else {
	print("Date du cycle non indique.");
	exit;
}

// VERIFICATION DU FORMAT DE L'EXPORT
$available_type = ["pdf", "csv"];
if (!isset($_GET['type']) || !in_array($_GET['type'], $available_type)) {
	print("Le format de l'export doit être: ");
	print(implode(", ", $available_type));
	exit;
}

$db = db_open();
$methode = $_SESSION["compte"]["methode"];

// RECUPERATION DE LA DATE DE DEBUT ET DE FIN DU CYCLE
$result["cycle_debut"] = new DateTime(db_select_cycle($db, date_sql($date), $_SESSION["no"])[0]["cycle"]);
$cycle_end = db_select_cycle_end($db, date_sql($date), $_SESSION["no"]);
if (isset($cycle_end[0]["cycle_end"])) {
	$date_tmp = new DateTime($cycle_end[0]["cycle_end"]);
	$date_tmp->modify('-1 day');
	$result["cycle_fin"] = $date_tmp;
}
else $result["cycle_fin"] = new DateTime();

$cycle_gross = db_select_cycle_grossesse($db, date_sql($date), $_SESSION["no"]);
if (isset($cycle_gross[0]["grossesse"])) {	
	$date_tmp = new DateTime($cycle_gross[0]["grossesse"]);
	if ($date_tmp < $result["cycle_fin"]) $result["cycle_fin"] = $date_tmp;
}

// RECUPERATION DU CYCLE
$data = db_select_cycle_complet($db, date_sql($result["cycle_debut"]),date_sql($result["cycle_fin"]), $_SESSION["no"]);

// AJOUT DES JOURS MANQUANTS DU CYCLE
$cycle = doc_ajout_jours_manquant($data, $methode);

if ($_GET['type'] == "csv") {

	// ECRITURE DU CSV
	header("content-type:application/csv;charset=UTF-8");
	header('Content-Disposition: attachment; filename="moncycle_app_'. date_sql($date) .'.csv"');
	$out = fopen('php://output', 'w');
	doc_cycle_vers_csv ($out, $cycle, $methode);
	fclose($out);
}
elseif ($_GET['type'] == "pdf") {
	$pdf = doc_cycle_vers_pdf ($cycle, $methode, $_SESSION["compte"]["nom"]);
	$pdf->Output('I', 'moncycle_app_'. date_humain($date, '_') . '.pdf');
}

