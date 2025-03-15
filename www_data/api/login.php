<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "../vendor/autoload.php";

require_once "../config.php";
require_once "../lib/db.php";
require_once "../lib/date.php";
require_once "../lib/sec.php";

use OTPHP\TOTP;

header('Content-Type: application/json');

$result_code = [
	0 => "",
	1 => "Login is disabled.",
	2 => "Email and/or password missing.",
	3 => "Account deactivated.",
	4 => "Correct password, but TOTP code is incorrect or missing.",
	5 => "Incorrect password or non-existent account.",
	6 => "Missing data",
	100 => "Account successfuly authentificated (password only).",
	101 => "Account successfuly authentificated (password + TOTP).",
	102 => "Account already authentificated."
];

$output = [];
$output["outcome"] = 0;


try {

	$db = db_open();

	$compte = sec_auth_jetton($db);
	if (!is_null($compte)) {
		$output["no_compte"] = $compte["no_compte"];
		$output["outcome"] = 102;
	}

	elseif (isset($_POST["email1"]) && isset($_POST["password"]) && filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) {

		$compte = db_select_compte_par_mail($db, $_POST["email1"])[0] ?? [];

		if (isset($compte["nb_co_echoue"]) && intval($compte["nb_co_echoue"])>=5) sleep(5);
		elseif (!isset($compte["nb_co_echoue"]) && rand(0,5)==0) sleep(5);

		if (!CONNEXION_COMPTE) $output["outcome"] = 1;
		elseif (empty($_POST["email1"]) || empty($_POST["password"])) {
			$output["outcome"] = 2;
		}
		elseif (isset($compte["actif"]) && !boolval($compte["actif"])) {
			$output["outcome"] = 3;
		}
		elseif (isset($compte["motdepasse"]) && password_verify($_POST["password"], $compte["motdepasse"])) {
			unset($compte["motdepasse"]);
			unset($_POST["password"]);

			$usr_totp_code = 0;
			if (isset($_POST["code"]) && strlen($_POST["code"])>0) $usr_totp_code = intval(preg_replace('/\s+/','',$_POST["code"]));

			if ($compte["totp_etat"] != TOTP_STATE_ACTIVE) {
				// AUTH SUCCESS
				$output["jetton"] = sec_auth_succes($db, $compte);
				$output["outcome"] = 100;
				$output["no_compte"] = $compte["no_compte"];
				
			}
			elseif ($usr_totp_code>0 && (TOTP::createFromSecret($compte["totp_secret"]))->verify($usr_totp_code)) {
				unset($compte["totp_secret"]);
				unset($_POST["code"]);
				// AUTH SUCCESS
				$output["outcome"] = sec_auth_succes($db, $compte);
				$output["outcome"] = 101;
				$output["no_compte"] = $compte["no_compte"];
			}
			else {
				db_update_co_echoue($db, $_POST["email1"]);
				$output["outcome"] = 4;
			}

		}
		else {
			db_update_co_echoue($db, $_POST["email1"]);
			$output["outcome"] = 5;
		}
	}


	else {
		$output["outcome"] = 6;
	}

}
catch (Exception $e){
	
	$output .= $e->getMessage();

}

$output["message"] = $result_code[$output["outcome"]];
echo json_encode($output);
