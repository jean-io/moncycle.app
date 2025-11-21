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

// https://json-schema.org/draft/2020-12
// https://www.jsonschemavalidator.net/

$data = $nfp_data;

$jsonSchemaAsString = <<<'JSON'
{
  "type": "object",
  "properties": {
	"schemaVersion" : {
		"type": "string",
		"pattern": "^[0-9]\\.[0-9]$",
		"description":"Version of the schema the file refers to. See 'version' key for actual schema"
	},
	"source_app" : {
		"type": "string",
		"pattern": "^.+$"
	},
	"source_app_version" : {
		"type": "string",
		"pattern": "^.+$"
	},
	"file_creation_timestamp" : {
		"type": "string",
		"pattern": "^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}$"
	},
	"cycles" : {
		"type" : "array",
		"items": {
			"type": "object",
			"properties": {
				"method" : {
					"type": "string",
					"description":"Planification method used for this cycle. If exists, could be used for cycle validation using the _methodValidation fields",
					"pattern" : "^\\s*(?i)(billings|fertilityCare|symptothermic_fr)\\s*$"
				},
				"cycleStartDate" : {
					"type": "string",
					"pattern" : "^\\d{4}-\\d{2}-\\d{2}$"
				},
				"days" : {
					"type": "array",
					"items": {
						"type": "object",
						"properties": {}
					}
				}
			},
			"required": ["method", "cycleStartDate", "days"]
		}
	}
	},
	"required": ["schemaVersion","source_app", "source_app_version", "file_creation_timestamp", "cycles"]
}
JSON;

$jsonSchema = json_decode($jsonSchemaAsString);
if ($jsonSchema === null) {
    echo "JSON schema decode error: " . json_last_error_msg();
	die();
}
$schemaStorage = new SchemaStorage();
$schemaStorage->addSchema('internal://mySchema', $jsonSchema);
$validator = new Validator(new Factory($schemaStorage));

$validator->validate($data, $jsonSchema);
if ($validator->isValid()) {
	$outcome["ok"] = "The supplied JSON validates against the schema.\n";
}
else {
	$outcome["err_detail"] = "JSON does not validate.";
	$outcome["err_list"] = [];
	foreach ($validator->getErrors() as $err) {
		array_push($outcome["err_list"], sprintf("[%s] %s", $err['property'], $err['message']));
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
