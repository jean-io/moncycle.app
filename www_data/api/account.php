<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/moncycle-app/backend-api-web-app
*/

require_once "../config.php";
require_once "../lib/db.php";
require_once "../lib/sec.php";
require_once "../lib/date.php";

header('Content-Type: application/json');

$db = db_open();

$user_account = sec_auth_token($db);
sec_exit_si_non_connecte($user_account);

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') parse_str(file_get_contents('php://input'), $_DELETE);

$data_update = [];
$data_update["field_update"] = [];

$updated_account = $user_account;

if (isset($_POST["name"]) && strlen($_POST["name"])>0) {
	$updated_account["name"] = $_POST["name"];
	array_push($data_update["field_update"], "name");
}
else {
	$updated_account["name"] = $user_account["name_user_account"];
}

if (isset($_POST["email2"]) && (empty($_POST["email2"]) || filter_var($_POST["email2"], FILTER_VALIDATE_EMAIL))) {
	$updated_account["email2"] = $_POST["email2"];
	array_push($data_update["field_update"], "email2");
}

if (isset($_POST["nfp_method"]) && !empty($_POST["nfp_method"])) {
	$nfp_method = intval($_POST["nfp_method"]);
	if ($nfp_method && $nfp_method >=1 && $nfp_method <= 4) {
		$updated_account["nfp_method"] = $nfp_method;
		array_push($data_update["field_update"], "nfp_method");
	}
}

if (isset($_POST["age"]) && !empty($_POST["age"])) {
	$age = intval($_POST["age"]);
	if ($age && $age >=1) {
		$updated_account["age"] = $age;
		array_push($data_update["field_update"], "age");
	}
}

if (isset($_POST["timeline_asc"]) && strlen($_POST["timeline_asc"])>0) {
	$updated_account["timeline_asc"] = boolval($_POST["timeline_asc"]) ? 1 : 0;
	array_push($data_update["field_update"], "timeline_asc");
}

if (isset($_POST["research"]) && strlen($_POST["research"])>0) {
	$updated_account["research"] = boolval($_POST["research"]) ? 1 : 0;
	array_push($data_update["field_update"], "research");
}

if (isset($_POST["sponsor"]) && strlen(($_POST["sponsor"]))>0) {
	$updated_account["sponsor"] = boolval($_POST["sponsor"]) ? 1 : 0;
	array_push($data_update["field_update"], "sponsor");
}

if (isset($_POST["last_write_client_UTC"]) && !empty($_POST['last_write_client_UTC']) && date_validate_timestamp(trim($_POST['last_write_client_UTC']))) {
	$updated_account["last_write_client_UTC"] = trim($_POST['last_write_client_UTC']);
	array_push($data_update["field_update"], "last_write_client_UTC");
}
else $updated_account["last_write_client_UTC"] = date('Y-m-d H:i:s');

if (!empty($data_update["field_update"])) {
	db_update_user_account_param($db, $updated_account["name"], $updated_account["email2"], $updated_account["nfp_method"], $updated_account["age"], $updated_account["sponsor"], $updated_account["timeline_asc"], $updated_account["research"], $updated_account["last_write_client_UTC"], $user_account["no_user_account"]);
}

if (isset($_DELETE["pw_before_deletion"])) {

	if (strlen($_DELETE["pw_before_deletion"])>0){
		$user_account = db_select_user_account_par_mail($db, $user_account["email1"])[0] ?? [];
		
		if (isset($user_account["password"]) && password_verify($_DELETE["pw_before_deletion"], $user_account["password"])) {
			// SUPPRESSION DU COMPTE
			db_delete_user_account($db, $user_account["no_user_account"]);
			setcookie("MONCYCLEAPP_TOKEN", '', -1, '/');
			$data_update = ["suppr" => true, "msg" => "user_account supprimé"];
		}
		else {
			$data_update = ["suppr" => false, "msg" => "mauvais mot de passe"];
		}
	}
	else {
		$data_update = ["suppr" => false, "msg" => "mot de passe manquant"];
	}
}

echo json_encode($data_update);

