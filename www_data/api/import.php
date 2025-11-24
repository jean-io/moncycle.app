<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/moncycle-app/backend-api-web-app
*/

require_once "../vendor/autoload.php";

require_once "../config.php";
require_once "../lib/db.php";
require_once "../lib/sec.php";
require_once "../lib/data.php";
require_once "../lib/nfp_file.php";
require_once "../lib/nfp_format.php";
require_once "../lib/date.php";

use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use JsonSchema\Constraints\Factory;
use JsonSchema\Constraints\Constraint;

$db = db_open();

$user_account = sec_auth_token($db);
sec_redirect_non_connecte($user_account);

$outcome = [];
$error = 0;
$nfp_data = null;

$error_list = [
	101 => "'overide' param should be 1 or 0",
	102 => "Invalid JSON payload",
	103 => "'schemaVersion' is missing of empty in NFP data",
	104 => "'schemaVersion' does not contain a valid version number",
	105 => "'schemaVersion' is not a supported version number",
	120 => "JSON file structure is not a valid NFP input",
	121 => "At least one day is not matching Billings NFP schema"
];

$overide = false;
if (isset($_GET['overide']) && !in_array($_GET['overide'], ["1", "0"])) {
	$error = 101;
}
else $overide = boolval($_GET['overide']);

if (!$error) {
	try {
		$raw = file_get_contents('php://input');
		$nfp_data = json_decode($raw, false, 512, JSON_THROW_ON_ERROR);
	} catch (JsonException $e) {
		$error = 102;
		$outcome["err_detail"] = $e->getMessage();
	}
}

if (!$error) {
	if (!isset($nfp_data->schemaVersion) || empty($nfp_data->schemaVersion)) {
		$error = 103;
	}
	else if (!preg_match("/^\s*\d+\.\d+\s*$/", $nfp_data->schemaVersion)) {
		$error = 104;
	}
	else if (version_compare(trim($nfp_data->schemaVersion), "1.0") != 0) {
		$error = 105;
		$outcome["err_detail"] = $nfp_data->schemaVersion . " is not supported";
	}
}

if (!$error) {
	$jsonSchema = json_decode(NFP_MAIN_FILE_SCHEMA);
	$schemaStorage = new SchemaStorage();
	$schemaStorage->addSchema('internal://mySchema', $jsonSchema);
	$validator = new Validator(new Factory($schemaStorage));
	$validator->validate($nfp_data, $jsonSchema);
	if (!$validator->isValid()) {
		$error = 120;
		$outcome["err_list"] = [];
		foreach ($validator->getErrors() as $err) {
			array_push($outcome["err_list"], sprintf("[%s] %s", $err['property'], $err['message']));
		}
	}
}

if (!$error) {
	foreach ($nfp_data->cycles as $cycle_data) {
		$jsonSchema = json_decode(NFP_BILLINGS_DAY_SCHEMA);
		$schemaStorage = new SchemaStorage();
		$schemaStorage->addSchema('internal://mySchema', $jsonSchema);
		$validator = new Validator(new Factory($schemaStorage));
		foreach ($cycle_data->days as $no => $day_data) {
			$validator->validate($day_data, $jsonSchema);
			if (!$validator->isValid()) {
				$error = 121;
				$outcome["err_list"] = [];
				foreach ($validator->getErrors() as $err) {
					array_push($outcome["err_list"], sprintf("[%s - %d][%s] %s", $cycle_data->cycleStartDate, $no, $err['property'], $err['message']));
				}
			}
		}
	}
}

header('Content-Type: application/json');

if ($error > 0) {
	http_response_code(400);
	$outcome["err_code"] = $error;
	$outcome["err_message"] = $error_list[$error] ?? "";
}
else $outcome["import"] = "ok";

print(json_encode($outcome));
