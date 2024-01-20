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
require_once "../vendor/autoload.php";

use OTPHP\TOTP;

define("TOTP_STATE_NEVER_USED", 0);
define("TOTP_STATE_DISABLED", 1);
define("TOTP_STATE_INIT", 2);
define("TOTP_STATE_ACTIVE", 3);

header('Content-Type: application/json');

$db = db_open();

$compte = sec_auth_jetton($db);
sec_exit_si_non_connecte($compte);

$ret = [];
$ret["totp_actif"] = $compte["totp_etat"];

if (isset($_GET["init"])) {
	$ret["init_secret"] = (TOTP::generate())->getSecret();
	db_update_compte_totp_secret($db, $ret["init_secret"], $compte["no_compte"]);
	db_update_compte_totp_etat($db, TOTP_STATE_INIT, $compte["no_compte"]);
	$ret["totp_actif"] = TOTP_STATE_INIT;
}

if (isset($_GET["activation"])) {

	if (isset($_POST["tmp_code"]) && !empty($_POST["tmp_code"]) && intval($_POST["tmp_code"])>0) {
		$otp_obj = TOTP::createFromSecret($compte["totp_secret"]);
		$otp = intval($otp_obj->now());

		if (intval($_POST["tmp_code"]) == $otp) {
			db_update_compte_totp_etat($db, TOTP_STATE_ACTIVE, $compte["no_compte"]);
			$ret["msg"] = "authentification multi-facteur activé";
			$ret["totp_actif"] = TOTP_STATE_ACTIVE;
		}
		else {
			$ret["msg"] = "le code renseigné ne correspond pas";
		}

	}
	else {
		$ret["msg"] = "code temporaire non renseigné";
	}

}

if (isset($_GET["desactivation"])) {

	if (isset($_POST["tmp_code"]) && !empty($_POST["tmp_code"]) && intval($_POST["tmp_code"])>0) {
		$otp_obj = TOTP::createFromSecret($compte["totp_secret"]);
		$otp = intval($otp_obj->now());

		if (intval($_POST["tmp_code"]) == $otp) {
			db_update_compte_totp_etat($db, TOTP_STATE_DISABLED, $compte["no_compte"]);
			db_update_compte_totp_secret($db, null, $compte["no_compte"]);
			$ret["msg"] = "authentification multi-facteur désactivé";
			$ret["totp_actif"] = TOTP_STATE_DISABLED;
		}
		else {
			$ret["msg"] = "le code renseigné ne correspond pas";
		}

	}
	else {
		$ret["msg"] = "code temporaire non renseigné";
	}

}

echo json_encode($ret);
