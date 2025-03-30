<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

require_once "../config.php";
require_once "../lib/db.php";
require_once "../lib/sec.php";
require_once "../lib/mail.php";
require_once '../vendor/phpmailer/phpmailer/src/Exception.php';
require_once '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '../vendor/phpmailer/phpmailer/src/SMTP.php';

define('NFP_METHOD_BILLINGS_TEMP',      1);
define('NFP_METHOD_BILLINGS',           2);
define('NFP_METHOD_FERTILITYCARE',      3);
define('NFP_METHOD_FERTILITYCARE_TEMP', 4);

$result_code = [
	0 => "",
	1 => "Account already logged in.",
	2 => "Account creation has been disabled.",
	3 => "Missing data or user input.",
	4 => "Provided email address is not valid.",
	5 => "Error in captcha input.",
	6 => "Account successfuly created, but password sending has failed.",
	7 => "Account already exist.",
	8 => "Birth year is not reallistic.",
	100 => "Account successfuly created"
];

$output = [];
$output["auth"] = 0;
$output["outcome"] = 0;
$output["message"] = "";

$db = db_open();

// IF ACCOUNT IS LOGGED IN
$user_account = sec_auth_token($db);
if (!is_null($user_account)) {
	$output["auth"] = $user_account["no_user_account"];
	$output["outcome"] = 1;
}

// IF ACCOUNT CREATION IS DISABLED
elseif (!CREATION_COMPTE) {
	$output["outcome"] = 2;
}

// ANALYSING USER INPUT FOR ACCOUNT CREATION
else {

	// GETTING CAPTCHAT VALUE
	$auth_token = "";
	$captcha = null;
	if (isset($_COOKIE["MONCYCLEAPP_TOKEN"]) && strlen($_COOKIE["MONCYCLEAPP_TOKEN"])>0) {
		$auth_token = $_COOKIE["MONCYCLEAPP_TOKEN"];
		$db_ret = db_select_auth_token_captcha($db, $auth_token);
		if (isset($db_ret[0]["no_auth_token"])) {
			db_update_auth_token_use($db, $db_ret[0]["no_auth_token"]);
			$captcha = $db_ret[0]["captcha"];

			// SECURITY : THIS PREVENT CAPTCAH RE-USE
			db_update_auth_token_captcha($db, $auth_token, null);
		}
	}

	// CHECKING USER INPUT
	if (!isset($_POST["firstname"]) || !isset($_POST["email1"]) || !isset($_POST["birth_year"]) || !isset($_POST["birth_year"])) {
		$output["outcome"] = 3;
	}
	elseif (!filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) {
		$output["outcome"] = 4;
	}
	elseif (!isset($_POST["captcha"]) || strlen(trim($_POST["captcha"]))<=0 || is_null($captcha) || trim($_POST["captcha"])!=$captcha) {
		$output["outcome"] = 5;
	}
	elseif (boolval(db_select_user_account_existe($db,$_POST["email1"])[0]["user_account_existe"])) {
		$output["outcome"] = 7;
	}
	elseif (intval($_POST["birth_year"]) < (intval(date("Y"))-100) || intval($_POST["birth_year"]) > intval(date("Y"))) {
		$output["outcome"] = 8;
	}
	else {

		//CREATING USER ACCOUNT
		$nfp_method = intval($_POST["method"] ?? 0);
		if ($nfp_method<NFP_METHOD_BILLINGS || $nfp_method>NFP_METHOD_FERTILITYCARE) $nfp_method=NFP_METHOD_BILLINGS;
		if (intval($_POST["temp"] ?? 0)) {
			if ($nfp_method == NFP_METHOD_BILLINGS)      $nfp_method = NFP_METHOD_BILLINGS_TEMP;
			if ($nfp_method == NFP_METHOD_FERTILITYCARE) $nfp_method = NFP_METHOD_FERTILITYCARE_TEMP;
		}

		$pass_text = sec_password_aleatoire();
		$pass_hash = sec_hash($pass_text);

		$output["new_account_no"] = db_insert_user_account($db, $_POST["firstname"], $nfp_method, $_POST["birth_year"], $_POST["email1"],$pass_hash, $_POST["discovered_comment"] ?? null, $_POST["ok_for_research"] ?? 0);

		$output["email1"] = $_POST["email1"];
		$output["name"] = $_POST["firstname"];

		$output["outcome"] = 6;

		// MAIL SENDING TO USER
		$mail = mail_init();
		$mail->addAddress($_POST["email1"], $_POST["email1"]);

		$mail->isHTML(false);
		$mail->Subject = 'Bienvenue et mot de passe';
		$mail->Body = mail_body_creation_user_account($_POST["firstname"], $pass_text, $_POST["email1"]);
		$mail->AltBody = 'Bienvenue sur MONCYCLE.APP! Votre mot de passe: ' . $pass_text;

		$mail->send();

		$output["outcome"] = 100;
	}

}


$output["message"] = $result_code[$output["outcome"]];

echo json_encode($output);
