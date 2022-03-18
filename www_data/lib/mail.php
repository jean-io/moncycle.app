<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/


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

function mail_body_creation_compte ($nom, $mdp, $mail1) {
	return <<<HTML
	<div style='font-family: sans-serif;'>Bonjour {$nom},<br />
	<br />
	Bienvenue sur moncycle.app!<br />
	<br />					
	Voici votre mot de passe temporaire: <b style='font-family: monospace;'>{$mdp}</b><br />
	Ce mot de passe est à changer dans la page "mon compte".<br />
	<br />					
	<a style='color: #1e824c' href='https://tableau.moncycle.app/connexion?email1={$mail1}'>connectez-vous</a><br />
	<br />
	A bientôt,<br />
	<br />
	<a href='https://www.moncycle.app' style='color: unset; text-decoration:none'>mon<span style='color: #1e824c;font-weight:bold'>cycle</span>.app</a><br /></div><?php;
	HTML;
}

function mail_body_nouveau_mdp($mdp, $mail1) {
	return <<<HTML
	<div style='font-family: sans-serif;'>Bonjour,<br />
	<br />
	Voici un nouveau mot de passe temporaire: <b style='font-family: monospace;'>{$mdp}</b><br />
	Ce mot de passe est à changer dans la page "mon compte".<br />
	<br />
	<a style='color: #1e824c' href='https://tableau.moncycle.app/connexion?email1={$mail1}'>connectez-vous</a><br />
	<br />
	A bientôt,<br />
	<br />
	<a href='https://www.moncycle.app' style='color: unset; text-decoration:none'>mon<span style='color: #1e824c;font-weight:bold'>cycle</span>.app</a><br /></div>
	HTML;
}

function mail_body_cycle($nom, $dh, $fh, $nb_j) {
	return <<<HTML
	<div style='font-family: sans-serif;'>Bonjour {$nom},<br />
	<br />
	Vous trouverez en PJ un export au format PDF et CSV de votre cycle du $dh au $fh d'une durée de $nb_j jours.<br />
	<br />
	A bientôt,<br />
	<br />
	<a href='https://www.moncycle.app' style='color: unset; text-decoration:none'>mon<span style='color: #1e824c;font-weight:bold'>cycle</span>.app</a><br /></div>
	HTML;
}


