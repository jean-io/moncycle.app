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
	<a href='https://www.moncycle.app' style='color: unset; text-decoration:none'>mon<span style='color: #1e824c;font-weight:bold'>cycle</span>.app</a><br />
	<br />
	<p style="color:gray;font-size:.85em;font-style: italic;">Merci d'utiliser moncycle.app! Ce mail a été envoyé automatiquement, merci de ne pas y répondre. Vous le recevez car vous avez un compte sur moncycle.app. Ce mail fait partie intégrante de l'application. Si vous ne souhaitez plus recevoir de mails de notre part, merci de ne plus utiliser moncycle.app.</p><br />
	</div>
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
	<a href='https://www.moncycle.app' style='color: unset; text-decoration:none'>mon<span style='color: #1e824c;font-weight:bold'>cycle</span>.app</a><br />
	<br />
	<p style="color:gray;font-size:.85em;font-style: italic;">Merci d'utiliser moncycle.app! Ce mail a été envoyé automatiquement, merci de ne pas y répondre. Vous le recevez car vous avez un compte sur moncycle.app. Ce mail fait partie intégrante de l'application. Si vous ne souhaitez plus recevoir de mails de notre part, merci de ne plus utiliser moncycle.app.</p><br />
	</div>
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
	<a href='https://www.moncycle.app' style='color: unset; text-decoration:none'>mon<span style='color: #1e824c;font-weight:bold'>cycle</span>.app</a><br />
	<br />
	<p style="color:gray;font-size:.85em;font-style: italic;">Merci d'utiliser moncycle.app! Ce mail a été envoyé automatiquement, merci de ne pas y répondre. Vous le recevez car vous avez un compte sur moncycle.app. Ce mail fait partie intégrante de l'application. Si vous ne souhaitez plus recevoir de mails de notre part, merci de ne plus utiliser moncycle.app.</p><br />
	</div>
	HTML;
}

function mail_body_relance ($nom, $mail1) {
	return <<<HTML
	<div style='font-family: sans-serif;'>Bonjour {$nom},<br />
	<br />
	Cela fait un moment qu'il n'y a pas eu d'activité sur votre tableau.<br />
	<br />					
	Tout va bien? Comment pouvons-nous aider?<br />
	<ol type='a'>
		<li>Vous avez perdu votre mot de passe?<br /><a style='color: #1e824c' href='https://tableau.moncycle.app/inscription?email1={$mail1}'>Réinitiliisez votre mot de passe</a></li>
		<li style="margin-top: 10px">L'application ne vous plait pas?<br /><a style='color: #1e824c' href='https://forms.gle/aA3GrFHAAx8SFdd47'>Dites nous tout</a></li>
		<li style="margin-top: 10px">Vous souhaitez simplement vous connecter?<br /><a style='color: #1e824c' href='https://tableau.moncycle.app/connexion?email1={$mail1}'>C'est par içi</a></li>
		<li style="margin-top: 10px">Un problème? Besoin d'aide?<br />Envoyez-nous un mail à <a style='color: #1e824c' href='mailto:moncycle.app@thjn.fr'>moncycle.app@thjn.fr</a></li>
		<li style="margin-top: 10px">Vous ne souhaitez plus utiliser moncycle.app?<br />Ingonrez ce mail, vous n'en recevrez pas d'autre.</li>
	</ol>
	A bientôt,<br />
	<br />
	<a href='https://www.moncycle.app' style='color: unset; text-decoration:none'>mon<span style='color: #1e824c;font-weight:bold'>cycle</span>.app</a><br />
	<br />
	<p style="color:gray;font-size:.85em;font-style: italic;">Merci d'utiliser moncycle.app! Ce mail a été envoyé automatiquement, merci de ne pas y répondre. Vous le recevez car vous avez un compte sur moncycle.app. Ce mail fait partie intégrante de l'application. Si vous ne souhaitez plus recevoir de mails de notre part, merci de ne plus utiliser moncycle.app.</p><br />
	</div>
	HTML;
}


