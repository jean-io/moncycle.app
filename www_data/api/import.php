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
];

$overide = false;
if (isset($_GET['overide']) && !in_array($_GET['overide'], ["1", "0"])) {
	$error = 101;
}
else $overide = boolval($_GET['overide']);

if (!$error) {
	try {
		$raw = file_get_contents('php://input');
		$nfp_data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
	} catch (JsonException $e) {
		$error = 102;
		$outcome["err_detail"] = $e->getMessage();
	}
}

if (!$error) {
	if (!isset($nfp_data["schemaVersion"]) || empty($nfp_data["schemaVersion"])) {
		$error = 103;
	}
	else if (!preg_match("/^\s*\d+\.\d+\s*$/", $nfp_data["schemaVersion"])) {
		$error = 104;
	}
	else if (version_compare(trim($nfp_data["schemaVersion"]), "1.0") != 0) {
		$error = 105;
		$outcome["err_detail"] = $nfp_data["schemaVersion"] . " is not supported";
	}
}

$validator = new JsonSchema\Validator;

$request = (object)[
    'processRefund'=>"true",
    'refundAmount'=>"17"
];

$outcome["test0"] = $validator->validate(
    $request, (object) [
        "type"=>"object",
        "properties"=>(object)[
            "processRefund"=>(object)[
                "type"=>"boolean"
            ],
            "refundAmount"=>(object)[
                "type"=>"number"
            ]
        ]
    ],
    Constraint::CHECK_MODE_COERCE_TYPES
); // validates!

$outcome["test1"] = is_bool($request->processRefund); // true
$outcome["test2"] = is_int($request->refundAmount); // true

header('Content-Type: application/json');

if ($error > 0) {
	http_response_code(400);
	$outcome["err_code"] = $error;
	$outcome["err_message"] = $error_list[$error] ?? "";
}
else $outcome["import"] = "ok";

print(json_encode($outcome));
