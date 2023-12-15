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

header('Content-Type: application/json');

$db = db_open();

$compte = sec_auth_jetton($db);
sec_exit_si_non_connecte($compte);

$ret = [];

if (isset($_GET["init"])) {
	$ret["init_secret"] = (TOTP::generate())->getSecret();
}

if (isset($_GET["activation"])) {
	
	$ret["totp_actif"] = false;
	$ret["msg"] = "";
	$ret["php_req"] = $_REQUEST;

	if (isset($_POST["totp_secret"]) && isset($_POST["tmp_code"]) && !empty($_POST["totp_secret"]) && !empty($_POST["tmp_code"]) && intval($_POST["tmp_code"])>0) {
		$otp_obj = TOTP::createFromSecret($_POST["totp_secret"]);
		$otp = intval($otp_obj->now());

		if (intval($_POST["tmp_code"]) == $otp) {
			db_update_compte_param_str($db, "totp", $_POST["totp_secret"], $compte["no_compte"]);
			$ret["msg"] = "authentification multi-facteur activé";
			$ret["totp_actif"] = true;
		}
		else {
			$ret["msg"] = "le code renseigné ne correspond pas à votre compte";
		}

	}
	else {
		$ret["msg"] = "données manquantes ou mal formaté";
	}

}

echo json_encode($ret);
