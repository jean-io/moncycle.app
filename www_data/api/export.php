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

$compte = sec_auth_jetton($db);
sec_redirect_non_connecte($compte);


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

$methode = $compte["methode"];

// RECUPERATION DE LA DATE DE DEBUT ET DE FIN DU CYCLE
$result["cycle_debut"] = new DateTime(db_select_cycle($db, date_sql($date), $compte["no_compte"])[0]["cycle"]);
$cycle_end = db_select_cycle_end($db, date_sql($date), $compte["no_compte"]);
if (isset($cycle_end[0]["cycle_end"])) {
	$date_tmp = new DateTime($cycle_end[0]["cycle_end"]);
	$date_tmp->modify('-1 day');
	$result["cycle_fin"] = $date_tmp;
}
else $result["cycle_fin"] = new DateTime();

$cycle_gross = db_select_cycle_grossesse($db, date_sql($date), $compte["no_compte"]);
if (isset($cycle_gross[0]["grossesse"])) {	
	$date_tmp = new DateTime($cycle_gross[0]["grossesse"]);
	if ($date_tmp < $result["cycle_fin"]) $result["cycle_fin"] = $date_tmp;
}

// RECUPERATION DU CYCLE
$data = db_select_cycle_complet($db, date_sql($result["cycle_debut"]),date_sql($result["cycle_fin"]), $compte["no_compte"]);

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
	$pdf = doc_cycle_vers_pdf ($cycle, $methode, $compte["nom_compte"]);
	$pdf->Output('I', 'moncycle_app_'. date_humain($date, '_') . '.pdf');
}

