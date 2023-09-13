<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "../config.php";
require_once "../lib/db.php";
require_once "../lib/date.php";

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
	http_response_code(403);
	echo json_encode(["auth" => False, "err" => "accès interdit"]);
	exit;
}

$db = db_open();

if (!isset($_SESSION["sess_refresh"]) || $_SESSION["sess_refresh"] != date_sql(new DateTime())) {
	$_SESSION["sess_refresh"] = date_sql(new DateTime());
	$compte = db_select_compte_par_nocompte($db, $_SESSION["no"])[0] ?? [];
	unset($compte["motdepasse"]);
	$_SESSION["compte"] = $compte;
}

$cycles = db_select_cycles($db, $_SESSION["no"]);
$grossesses = db_select_grossesses($db, $_SESSION["no"]);

$methode = [1 => "temp", 2 => "glaire", 3 => "fc"];

echo json_encode([
	"id_utilisateur" => $_SESSION["no"],
	"methode" => $_SESSION["compte"]["methode"],
	"methode_diminutif" => $methode[$_SESSION["compte"]["methode"]],
	"nom" => $_SESSION["compte"]["nom"],
	"donateur" => ($_SESSION["compte"]["donateur"] == 1), 
	"tous_les_cycles" => $cycles,
	"toutes_les_grossesses" => $grossesses
]);

