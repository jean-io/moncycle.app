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
require_once "../lib/sec.php";

header('Content-Type: application/json');

$db = db_open();

$compte = sec_auth_jetton($db);
sec_exit_si_non_connecte($compte);

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') parse_str(file_get_contents('php://input'), $_DELETE);

$mise_a_jour = [];

if (isset($_POST["nom"])) {
	db_update_compte_param_str($db, "nom", $_POST["nom"], $compte["no_compte"]);
	$mise_a_jour["nom"] = $_POST["nom"];
}

if (isset($_POST["email2"]) && (empty($_POST["email2"]) || filter_var($_POST["email2"], FILTER_VALIDATE_EMAIL))) {
	db_update_compte_param_str($db, "email2", $_POST["email2"], $compte["no_compte"]);
	$mise_a_jour["email2"] = $_POST["email2"];
}

if (isset($_POST["methode"]) && !empty($_POST["methode"])) {
	$methode = intval($_POST["methode"]);
	if ($methode && $methode >=1 && $methode <= 4) {
		db_update_compte_param_int($db, "methode", $methode, $compte["no_compte"]);
		$mise_a_jour["methode"] = $methode;
	}
}

if (isset($_POST["age"]) && !empty($_POST["age"])) {
	$age = intval($_POST["age"]);
	if ($age && $age >=1) {
		db_update_compte_param_int($db, "age", $age, $compte["no_compte"]);
		$mise_a_jour["age"] = $age;
	}
}

if (isset($_POST["timeline_asc"])) {
	$timeline_asc = boolval($_POST["timeline_asc"]);
	$tet = db_update_compte_param_int($db, "timeline_asc", $timeline_asc ? 1 : 0, $compte["no_compte"]);
	$mise_a_jour["timeline_asc"] = $timeline_asc;
	$mise_a_jour["test"] = $tet;
}

if (isset($_POST["recherche"])) {
	$recherche = boolval($_POST["recherche"]);
	db_update_compte_param_int($db, "recherche", $recherche ? 1 : 0, $compte["no_compte"]);
	$mise_a_jour["recherche"] = $recherche;
}

if (isset($_DELETE["mdp_pour_supprimer"])) {

	if (strlen($_DELETE["mdp_pour_supprimer"])>0){
		$compte = db_select_compte_par_mail($db, $compte["email1"])[0] ?? [];
		
		if (isset($compte["motdepasse"]) && password_verify($_DELETE["mdp_pour_supprimer"], $compte["motdepasse"])) {
			// SUPPRESSION DU COMPTE
			db_delete_compte($db, $compte["no_compte"]);
			setcookie("MONCYCLEAPP_JETTON", '', -1, '/');
			$mise_a_jour = ["suppr" => true, "msg" => "compte supprimé"];
		}
		else {
			$mise_a_jour = ["suppr" => false, "msg" => "mauvais mot de passe"];
		}
	}
	else {
		$mise_a_jour = ["suppr" => false, "msg" => "mot de passe manquant"];
	}
}

echo json_encode($mise_a_jour);

