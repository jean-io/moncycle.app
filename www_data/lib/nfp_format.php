<?php
/* MONCYCLE.APP
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/moncycle-app/backend-api-web-app
*/

// https://json-schema.org/draft/2020-12
// https://www.jsonschemavalidator.net/

const NFP_MAIN_FILE_SCHEMA = <<<'JSON'
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
					"type": "array"
				}
			},
			"required": ["method", "cycleStartDate", "days"]
		}
	}
	},
	"required": ["schemaVersion","source_app", "source_app_version", "file_creation_timestamp", "cycles"]
}
JSON;


const NFP_BILLINGS_DAY_SCHEMA = <<<'JSON'
{
	"type": "object",
	"properties": {
		"stampColor" : {
			"description":"Stamp color",
			"type": "string",
			"pattern" : "^\\s*(?i)(green|red|yellow|white)\\s*$"
		},
		"stampBaby" : {
			"description":"Baby label on top of the stamp color",
			"type": "string",
			"pattern" : "^\\s*(?i)(true|false)\\s*$"
		},
		"isPeak" : {
			"description":"Peak tag associated to this date. Could be multiple isPeak days in one cycle. May trigger a counting mechanism computed by softwares.",
			"type": "string",
			"pattern" : "^\\s*(?i)(true|false)\\s*$"
		},
		"counterStart" : {
			"description":"Show a counter from this day for X specified days. Trigger a counting mechanism computed by software.",
			"type": "string"
		},
		"sexUnion" : {
			"description":"Sexual intercourse (Union) or reserved unions (ReservedUnion). The user may indicate that the following unions in this cycle won't be noted any longer (LastReportedUnion)",
			"type": "string",
			"pattern" : "^\\s*(?i)(union|lastreportedunion)\\s*$"
		},
		"freeMucusSensation" : {
			"description":"List of keywords representing a sensation for that day",
			"type" : "array",
			"items": { "type": "string" }
		},
		"freeMucusObservation" : {
			"description":"List of keywords representing an observation for that day",
			"type" : "array",
			"items": { "type": "string" }
		},
		"mucusNotObserved" : {
			"description":"User indicated no data for that day",
			"type": "string",
			"pattern" : "^\\s*(?i)(true|false)\\s*$"
		},
		"booleanPregnancyDetected" : {
			"type": "string",
			"pattern" : "^\\s*(?i)(true|false)\\s*$"
		},
		"temperature" : {
			"description": "body temperature",
			"type": "number"
		},
		"temperatureTime" : {
			"type": "string",
			"description":"time temperature taken in hh:mm:ss format",
			"pattern" : "^\\s*\\d\\d:\\d\\d:\\d\\d\\s*$"
		},
		"comment" : {"type": "string"}
	},
	"required": []
}
JSON;
