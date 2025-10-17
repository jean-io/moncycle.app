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

$description_types = [0 => "undefined", 1 => "observation", 2 => "sensation"];

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') parse_str(file_get_contents('php://input'), $_DELETE);

$db = db_open();

$user_account = sec_auth_token($db);
sec_exit_si_non_connecte($user_account);

$ret = [];

// creation/modification of a description
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	
	if (isset($_POST["no_description"]) && !empty($_POST["no_description"]) && !filter_var($_POST["no_description"], FILTER_VALIDATE_INT)) {
		$ret["err"] = "Error : POST variable 'no_description' should be an integer - it should match the description to edit.";
	}
	elseif (isset($_POST["no_description"]) && !empty($_POST["no_description"]) && !boolval(db_select_description_no_exist($db,$_POST["no_description"],$user_account["no_user_account"]))) {
		$ret["err"] = "Error : POST variable 'no_description' does not match a known description.";
	}
	elseif (!isset($_POST["name"]) || empty($_POST["name"]) || !isset($_POST["type"])) {
		$ret["err"] = "Error : missing POST variable 'name' or 'type'.";
	}
	elseif (boolval(db_select_description_name_exist($db, $_POST["name"], $user_account["no_user_account"], $_POST["no_description"] ?? 0))) {
		$ret["err"] = "Error : this description 'name' already exist : " . $_POST["name"];
	}
	elseif (!ctype_digit($_POST["type"]) || !isset($description_types[intval($_POST["type"])])) {
		$ret["err"] = "Error : 'type' argument is empty or contains an invalid number. Possibilities : 0 => undefined, 1 => observation, 2 => sensation.";
	}
	else {
		$desc_name = trim($_POST["name"]);
		$desc_type = intval($_POST["type"]);
		$desc_no = null;
		$last_write_client_UTC = "";

		if (isset($_POST["no_description"]) && !empty($_POST["no_description"])) $desc_no = intval($_POST["no_description"]);

		if (isset($_POST["last_write_client_UTC"]) && date_validate_timestamp(trim($_POST['last_write_client_UTC']))) $last_write_client_UTC = trim($_POST['last_write_client_UTC']);
		else $last_write_client_UTC = date('Y-m-d H:i:s');

		try {

			$db->exec("START TRANSACTION");

			// CREATION OF A NEW DESCRIPTION
			if (is_null($desc_no)) {
				$ret["action"] = "insert";
				$desc_no = db_insert_description($db, $user_account["no_user_account"], $desc_name, $desc_type, $last_write_client_UTC);
			}

			// EDIT OF AN EXISTING DESCRIPTION
			else {
				$ret["action"] = "update";
				db_update_description_name_type($db, $user_account["no_user_account"], $desc_no, $desc_name, $desc_type, $last_write_client_UTC);
			}

			$ret["no_description"] = $desc_no;
			$ret["name"] = $desc_name;
			$ret["type"] = $desc_type;
			$ret["last_write_client_UTC"] = $last_write_client_UTC;

			$db->exec("COMMIT");

			$ret["outcome"] = "ok";

		} catch (\Throwable $th) {
			$db->exec("ROLLBACK");
			$result["outcome"] = "ko";
			throw $th;
		}
	}

}

// deletion of a description
elseif ($_SERVER['REQUEST_METHOD'] == "DELETE") {

	if (isset($_DELETE["no_description"]) && !empty($_DELETE["no_description"]) && !filter_var($_DELETE["no_description"], FILTER_VALIDATE_INT)) {
		$ret["err"] = "Error : DELETE variable 'no_description' should be an integer - it should match the description to delete.";
	}
	elseif (isset($_DELETE["no_description"]) && !empty($_DELETE["no_description"]) && !boolval(db_select_description_no_exist($db,$_DELETE["no_description"],$user_account["no_user_account"]))) {
		$ret["err"] = "Error : DELETE variable 'no_description' does not match a known description.";
	}
	else {
		$ret["nb_deleted"] = db_delete_descriptions($db, $_DELETE["no_description"], $user_account["no_user_account"]);
		$ret["deleted"] = "Deleted " . $ret["nb_deleted"] . " description no " . $_DELETE["no_description"];
		$ret["no_description"] = $_DELETE["no_description"];
	}

}

// HTTP GET method, getting all descriptions
else {

	$ret = $description = db_select_description_with_count($db, $user_account["no_user_account"]);

}

echo json_encode($ret);
