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
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

header('Content-Type: application/json');

$db = db_open();

$compte = sec_auth_jetton($db);
sec_exit_si_non_connecte($compte);

$ret = [];
$ret["totp_actif"] = $compte["totp_etat"];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if ($compte["totp_etat"] == TOTP_STATE_ACTIVE) $ret["msg"] = "la double authentification est déjà active";
	else {
		$totp = TOTP::generate();
		$totp->setLabel($compte["email1"]);
		$totp->setIssuer('MONCYCLE.APP');
		$totp->setParameter('image', APP_URL . "img/moncycleapp512.jpg");
		db_update_compte_totp_secret($db, $totp->getSecret(), $compte["no_compte"]);
		db_update_compte_totp_etat($db, TOTP_STATE_INIT, $compte["no_compte"]);
		$renderer = new ImageRenderer(new RendererStyle(150), new SvgImageBackEnd());
		$writer = new Writer($renderer);
		$ret["init_secret"] = $totp->getSecret();
		$ret["otpauth"] = $totp->getProvisioningUri();
		$ret["qrcode"] = $writer->writeString($totp->getProvisioningUri());
		$ret["totp_actif"] = TOTP_STATE_INIT;
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ($compte["totp_etat"] == TOTP_STATE_ACTIVE) $ret["msg"] = "la double authentification est déjà active";
	elseif (isset($_POST["tmp_code"]) && !empty($_POST["tmp_code"]) && intval($_POST["tmp_code"])>0) {
		$otp_obj = TOTP::createFromSecret($compte["totp_secret"]);
		if ($otp_obj->verify(intval($_POST["tmp_code"]))) {
			db_update_compte_totp_etat($db, TOTP_STATE_ACTIVE, $compte["no_compte"]);
			$ret["msg"] = "authentification multi-facteur activée";
			$ret["totp_actif"] = TOTP_STATE_ACTIVE;
		}
		else {
			$ret["msg"] = "le code renseigné n'est pas correct";
		}
	}
	else {
		$ret["msg"] = "code temporaire non renseigné";
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
	parse_str(file_get_contents('php://input'), $_DELETE);
	if ($compte["totp_etat"] != TOTP_STATE_ACTIVE) $ret["msg"] = "la double authentification n'est pas active";
	elseif (isset($_DELETE["tmp_code"]) && !empty($_DELETE["tmp_code"]) && intval($_DELETE["tmp_code"])>0) {
		$otp_obj = TOTP::createFromSecret($compte["totp_secret"]);
		if ($otp_obj->verify(intval($_DELETE["tmp_code"]))) {
			db_update_compte_totp_etat($db, TOTP_STATE_DISABLED, $compte["no_compte"]);
			db_update_compte_totp_secret($db, null, $compte["no_compte"]);
			$ret["msg"] = "authentification multi-facteur désactivée";
			$ret["totp_actif"] = TOTP_STATE_DISABLED;
		}
		else {
			$ret["msg"] = "le code renseigné n'est pas correct";
		}
	}
	else {
		$ret["msg"] = "code temporaire non renseigné";
	}
}

echo json_encode($ret);
