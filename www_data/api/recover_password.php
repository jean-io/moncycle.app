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

$output = [];
$output["outcome"] = 0;
$output["message"] = "";

$result_code = [
	0 => "",
    1 => "Email address was not provided.",
	2 => "Email address provided is not valid.",
    3 => "Password sending via email has failed.",
	100 => "New password sucessfully sent to provided email address (if account exist)."
];

$db = db_open();

$reset_email = $_POST["email1"] ?? null;
$reset_email = is_null($reset_email) ? null : trim($reset_email);

$output["email1"] = $reset_email;

// CHECK IS ARG ARE PROVIDED
if (is_null($reset_email)) {
    $output["outcome"] = 1;
}

// CHECK IF EMAIL ADDRESS IS VALID
elseif (!filter_var($reset_email, FILTER_VALIDATE_EMAIL)) {
    $output["outcome"] = 2;
}

// CHECK IF ACCOUNT IS EXISTING, IF YES SEND A NEW PASSWORD
elseif (boolval(db_select_compte_existe($db,$reset_email)[0]["compte_existe"])) {
    $pass_text = sec_password_aleatoire();
    $pass_hash = sec_hash($pass_text);

    db_update_password_par_mail($db, $pass_hash, $_POST["email1"]);

    $output["outcome"] = 3;

    $mail = mail_init();
    $mail->addAddress($_POST["email1"], $_POST["email1"]);

    $mail->isHTML(false);
    $mail->Subject = 'Nouveau mot de passe';
    $mail->Body = mail_body_nouveau_mdp($pass_text, $_POST["email1"]);
    $mail->AltBody = 'Nouveau mot de passe temporaire: ' . $pass_text;

    $mail->send();

    sleep(rand(1,4));
    $output["outcome"] = 100;
}

// RETURN SUCCESS TO AVOID DETECTION OF EXISTING ACCOUNT
else {
    sleep(rand(1,5));
    $output["outcome"] = 100;
}



$output["message"] = $result_code[$output["outcome"]];

echo json_encode($output);
