<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

header('Content-Type: application/json');

require_once "../config.php";
require_once "../lib/db.php";
require_once "../lib/sec.php";
require_once "../vendor/autoload.php";

use OTPHP\TOTP;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

$output = [];
$output["outcome"] = 0;
$output["message"] = "";

$result_code = [
	0 => "",
	1 => "Two-factor authentication is already enabled.",
	2 => "Two-factor authentication is not enabled.",
	3 => "Entered TOTP code is not valid.",
	4 => "TOTP one-time code not provided.",
	100 => "Two-factor authentication via TOTP successfully enabled.",
	101 => "Two-factor authentication via TOTP successfully disabeled.",
	102 => "Two-factor authentication via TOTP successfully initiated."
];

$db = db_open();

$compte = sec_auth_jetton($db);
sec_exit_si_non_connecte($compte);

$output["totp_state"] = $compte["totp_state"];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if ($compte["totp_state"] == TOTP_STATE_ACTIVE) $output["outcome"] = 1;
	else {
		$totp = TOTP::generate();
		$totp->setLabel($compte["email1"]);
		$totp->setIssuer('MONCYCLE.APP');
		$totp->setParameter('image', APP_URL . "img/moncycleapp512.jpg");
		db_update_compte_totp_secret($db, $totp->getSecret(), $compte["no_compte"]);
		db_update_compte_totp_state($db, TOTP_STATE_INIT, $compte["no_compte"]);
		$renderer = new ImageRenderer(new RendererStyle(150), new SvgImageBackEnd());
		$writer = new Writer($renderer);
		$output["init_secret"] = $totp->getSecret();
		$output["otpauth"] = $totp->getProvisioningUri();
		$output["qrcode"] = $writer->writeString($totp->getProvisioningUri());
		$output["totp_state"] = TOTP_STATE_INIT;
		$output["outcome"] = 102;
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ($compte["totp_state"] == TOTP_STATE_ACTIVE) $output["outcome"] = 1;
	elseif (isset($_POST["tmp_code"]) && !empty($_POST["tmp_code"]) && intval($_POST["tmp_code"])>0) {
		$otp_obj = TOTP::createFromSecret($compte["totp_secret"]);
		if ($otp_obj->verify(intval($_POST["tmp_code"]))) {
			db_update_compte_totp_state($db, TOTP_STATE_ACTIVE, $compte["no_compte"]);
			$output["outcome"] = 100;
			$output["totp_state"] = TOTP_STATE_ACTIVE;
		}
		else $output["outcome"] = 3;
	}
	else $output["outcome"] = 4;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
	parse_str(file_get_contents('php://input'), $_DELETE);
	if ($compte["totp_state"] != TOTP_STATE_ACTIVE) $output["outcome"] = 1;
	elseif (isset($_DELETE["tmp_code"]) && !empty($_DELETE["tmp_code"]) && intval($_DELETE["tmp_code"])>0) {
		$otp_obj = TOTP::createFromSecret($compte["totp_secret"]);
		if ($otp_obj->verify(intval($_DELETE["tmp_code"]))) {
			db_update_compte_totp_state($db, TOTP_STATE_DISABLED, $compte["no_compte"]);
			db_update_compte_totp_secret($db, null, $compte["no_compte"]);
			$output["totp_state"] = TOTP_STATE_DISABLED;
			$output["outcome"] = 101;
		}
		else $output["outcome"] = 3;
	}
	else $output["outcome"] = 4;
}


$output["message"] = $result_code[$output["outcome"]];

echo json_encode($output);
