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

$compte = sec_auth_jetton($db);
sec_exit_si_non_connecte($compte);

$ret = [];

// creation of a description
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	
	if (!isset($_POST["name"]) || empty($_POST["name"]) || !isset($_POST["type"])) {
		$ret["err"] = "Error : missing POST variable 'name' or 'type'.";
	}
	elseif (!ctype_digit($_POST["type"]) || !isset($description_types[intval($_POST["type"])])) {
		$ret["err"] = "Error : 'type' argument is empty or contains an invalid number. Possibilities : 0 => undefined, 1 => observation, 2 => sensation.";
	}
	else {
		$desc_name = trim($_POST["name"]);
		$desc_type = intval($_POST["type"]);

		try {

			$db->exec("START TRANSACTION");

			$raw_description = db_select_description_with_count($db, $compte["no_compte"]);

			$old_description = [];
			foreach ($raw_description as $desc) if ($desc["name"] == $desc_name) $old_description = $desc;

			// IF DESCRIPTION ALREDAY EXIST --> JUST UPDATES
			if (isset($old_description["no_description"])) {
				$ret["ok"] = "Description of observation " . $desc_name . " already existing.";

				// UPDATING NAME
				if (!empty($_POST["new_name"]) && strpos(',',$_POST["new_name"])==false) {
					$new_desc_name = trim($_POST["new_name"]);

					$already_exist_rename = false;
					foreach ($raw_description as $desc) if ($desc["name"] == $new_desc_name) $already_exist_rename = true;

					if ($already_exist_rename) {
						$ret["err"] = "Error : 'new_name' argument give a name that already exist.";
					}
					else {
						db_update_description_name($db, $compte["no_compte"], $old_description["no_description"], $new_desc_name);
						$ret["renamed"] = "Description of observation " . $desc_name . " renamed to " . $new_desc_name;
					}
				}

				// UPDATING TYPE
				if ($old_description["type"] != $desc_type) {
					db_update_description_type ($db, $compte["no_compte"], $old_description["no_description"], $desc_type);
					$ret["type_updated"] = "Type of description updated from " . $old_description["type"] . " to " . $desc_type;
				}
			}
			else {
				$old_description["no_description"] = db_insert_description($db, $compte["no_compte"], $desc_name, $desc_type);
				$ret["ok"] = "Description of observation " . $desc_name . " added.";
			}

			// ADDING CLIENT SIDE UPDATE TIMESTAMP
			$last_write_client_UTC = "";
			if (isset($_POST["last_write_client_UTC"]) && date_validate_timestamp(trim($_POST['last_write_client_UTC']))) $last_write_client_UTC = trim($_POST['last_write_client_UTC']);
			else $last_write_client_UTC = date('Y-m-d H:i:s');
			db_update_description_client_timestamp ($db, $compte["no_compte"], $old_description["no_description"], $last_write_client_UTC);

			$db->exec("COMMIT");

		} catch (\Throwable $th) {
			$db->exec("ROLLBACK");
			$result["outcome"] = "ko";
			throw $th;
		}
	}

}

// deletion of a description
elseif ($_SERVER['REQUEST_METHOD'] == "DELETE") {

	if (!isset($_DELETE["name"]) || empty($_DELETE["name"])) {
		$ret["err"] = "Error : missing DELETE variable 'name'.";
	}
	else {
		$ret["nb_deleted"] = db_delete_descriptions($db, $_DELETE["name"], $compte["no_compte"]);
		$ret["deleted"] = "Deleted " . $ret["nb_deleted"] . " description with name " . $_DELETE["name"];
	}

}

// HTTP GET method, getting all descriptions
else {

	$ret = $description = db_select_description_with_count($db, $compte["no_compte"]);

}

echo json_encode($ret);
