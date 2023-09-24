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
require_once "../lib/sec.php";

header('Content-Type: application/json');

$db = db_open();

$compte = sec_auth_jetton($db);
sec_exit_si_non_connecte($compte);

$cycles = db_select_cycles($db, $compte["no_compte"]);
$grossesses = db_select_grossesses($db, $compte["no_compte"]);

$methode = [1 => "temp", 2 => "glaire", 3 => "fc"];

echo json_encode([
	"id_utilisateur" => $compte["no_compte"],
	"methode" => $compte["methode"],
	"methode_diminutif" => $methode[$compte["methode"]],
	"nom" => $compte["nom_compte"],
	"donateur" => boolval($compte["donateur"]), 
	"tous_les_cycles" => $cycles,
	"toutes_les_grossesses" => $grossesses
]);

