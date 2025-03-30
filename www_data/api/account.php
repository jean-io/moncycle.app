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

$user_account = sec_auth_token($db);
sec_exit_si_non_connecte($user_account);

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') parse_str(file_get_contents('php://input'), $_DELETE);

$mise_a_jour = [];

if (isset($_POST["name"])) {
	db_update_user_account_param_str($db, "name", $_POST["name"], $user_account["no_user_account"]);
	$mise_a_jour["name"] = $_POST["name"];
}

if (isset($_POST["email2"]) && (empty($_POST["email2"]) || filter_var($_POST["email2"], FILTER_VALIDATE_EMAIL))) {
	db_update_user_account_param_str($db, "email2", $_POST["email2"], $user_account["no_user_account"]);
	$mise_a_jour["email2"] = $_POST["email2"];
}

if (isset($_POST["nfp_method"]) && !empty($_POST["nfp_method"])) {
	$nfp_method = intval($_POST["nfp_method"]);
	if ($nfp_method && $nfp_method >=1 && $nfp_method <= 4) {
		db_update_user_account_param_int($db, "nfp_method", $nfp_method, $user_account["no_user_account"]);
		$mise_a_jour["nfp_method"] = $nfp_method;
	}
}

if (isset($_POST["age"]) && !empty($_POST["age"])) {
	$age = intval($_POST["age"]);
	if ($age && $age >=1) {
		db_update_user_account_param_int($db, "age", $age, $user_account["no_user_account"]);
		$mise_a_jour["age"] = $age;
	}
}

if (isset($_POST["timeline_asc"])) {
	$timeline_asc = boolval($_POST["timeline_asc"]);
	$tet = db_update_user_account_param_int($db, "timeline_asc", $timeline_asc ? 1 : 0, $user_account["no_user_account"]);
	$mise_a_jour["timeline_asc"] = $timeline_asc;
	$mise_a_jour["test"] = $tet;
}

if (isset($_POST["research"])) {
	$research = boolval($_POST["research"]);
	db_update_user_account_param_int($db, "research", $research ? 1 : 0, $user_account["no_user_account"]);
	$mise_a_jour["research"] = $research;
}

if (isset($_DELETE["pw_before_deletion"])) {

	if (strlen($_DELETE["pw_before_deletion"])>0){
		$user_account = db_select_user_account_par_mail($db, $user_account["email1"])[0] ?? [];
		
		if (isset($user_account["password"]) && password_verify($_DELETE["pw_before_deletion"], $user_account["password"])) {
			// SUPPRESSION DU COMPTE
			db_delete_user_account($db, $user_account["no_user_account"]);
			setcookie("MONCYCLEAPP_TOKEN", '', -1, '/');
			$mise_a_jour = ["suppr" => true, "msg" => "user_account supprimé"];
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

