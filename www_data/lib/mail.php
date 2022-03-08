<?php


use PHPMailer\PHPMailer\PHPMailer;

function mail_init(){
	$mail = new PHPMailer();

	$mail->isSMTP();
	$mail->Host       = SMTP_HOST;
	$mail->SMTPAuth   = true;
	$mail->Username   = SMTP_MAIL;
	$mail->Password   = SMTP_PASSWORD;
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
	$mail->Port       = SMTP_PORT;
	$mail->CharSet    = 'UTF-8';

	$mail->setFrom(SMTP_MAIL, 'moncycle.app');
	return $mail;
}


