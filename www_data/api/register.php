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

define('METHODE_BILLINGS_TEMP',      1);
define('METHODE_BILLINGS',           2);
define('METHODE_FERTILITYCARE',      3);
define('METHODE_FERTILITYCARE_TEMP', 4);

$result_code = [
	0 => "",
	1 => "Account already logged in.",
	2 => "Account creation has been disabled.",
	3 => "Missing data or user input.",
	4 => "Provided email address is not valid.",
	5 => "Error in captcha input.",
	6 => "Account successfuly created, but password sending has failed.",
	7 => "Account already exist.",
	8 => "Birth year is not reallistic."
	100 => "Account successfuly created"
];

$output = [];
$output["auth"] = 0;
$output["output"] = 0;
$output["message"] = "";

$db = db_open();

// IF ACCOUNT IS LOGGED IN
$compte = sec_auth_jetton($db);
if (!is_null($compte)) {
	$output["auth"] = $compte["no_compte"];
	$output["output"] = 1;
}

// IF ACCOUNT CREATION IS DISABLED
elseif (!CREATION_COMPTE) {
	$output["output"] = 2;
}

// ANALYSING USER INPUT FOR ACCOUNT CREATION
else {

	// GETTING CAPTCHAT VALUE
	$jetton = "";
	$captcha = null;
	if (isset($_COOKIE["MONCYCLEAPP_JETTON"]) && strlen($_COOKIE["MONCYCLEAPP_JETTON"])>0) {
		$jetton = $_COOKIE["MONCYCLEAPP_JETTON"];
		$db_ret = db_select_jetton_captcha($db, $jetton);
		if (isset($db_ret[0]["no_jetton"])) {
			db_update_jetton_use($db, $db_ret[0]["no_jetton"]);
			$captcha = $db_ret[0]["captcha"]; 
		}
	}

	// CHECKING USER INOUT
	if (!isset($_POST["firstname"]) || !isset($_POST["email1"]) || !isset($_POST["birth_year"]) || !isset($_POST["birth_year"])) {
		$output["output"] = 3;
	}
	elseif (!filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) {
		$output["output"] = 4;
	}
	elseif (!isset($_POST["captcha"]) || strlen(trim($_POST["captcha"]))<=0 || trim($_POST["captcha"])!=$captcha) {
		$output["output"] = 5;
	}
	elseif (boolval(db_select_compte_existe($db,$_POST["email1"])[0]["compte_existe"])) {
		$output["output"] = 7;
	}
	elseif (intval($_POST["birth_year"]) < (intval(date("Y"))-100) || intval($_POST["birth_year"]) > intval(date("Y"))) {
		$output["output"] = 8;
	}
	else {

		//CREATING USER ACCOUNT
		$methode = intval($_POST["method"] ?? 0);
		if ($methode<METHODE_BILLINGS || $methode>METHODE_FERTILITYCARE) $methode=METHODE_BILLINGS;
		if (intval($_POST["temp"] ?? 0)) {
			if ($methode == METHODE_BILLINGS)      $methode = METHODE_BILLINGS_TEMP;
			if ($methode == METHODE_FERTILITYCARE) $methode = METHODE_FERTILITYCARE_TEMP;
		}

		$pass_text = sec_motdepasse_aleatoire();
		$pass_hash = sec_hash($pass_text);

		$output["new_account_no"] = db_insert_compte($db, $_POST["firstname"], $methode, $_POST["birth_year"], $_POST["email1"],$pass_hash, $_POST["discovered_comment"] ?? null, $_POST["ok_for_research"] ?? 0);

		$output["succes"] = "Félicitations <b>{$_POST["firstname"]}</b>: votre compte a été créé! &#x1F525;<br />Votre mot de passe vous a été envoyé par e-mail à l'addresse <b>{$_POST["email1"]}</b>";
		$output["email1"] = $_POST["email1"];
		$output["name"] = $_POST["firstname"];

		$output["output"] = 6;

		// MAIL SENDING TO USER
		$mail = mail_init();
		$mail->addAddress($_POST["email1"], $_POST["email1"]);

		$mail->isHTML(false);
		$mail->Subject = 'Bienvenue et mot de passe';
		$mail->Body = mail_body_creation_compte($_POST["firstname"], $pass_text, $_POST["email1"]);
		$mail->AltBody = 'Bienvenue sur MONCYCLE.APP! Votre mot de passe: ' . $pass_text;

		$mail->send();

		$output["output"] = 100;
	}

}


$output["message"] = $result_code[$output["output"]];

echo json_encode($output);
