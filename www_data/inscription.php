<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "config.php";
require_once "lib/db.php";
require_once "lib/sec.php";
require_once 'phpmailer/src/Exception.php';
require_once 'phpmailer/src/PHPMailer.php';
require_once 'phpmailer/src/SMTP.php';

session_start();

if (isset($_SESSION["connected"]) && $_SESSION["connected"]) {
	header('Location: /');
	exit;
}

$output = "";
$succes = "";
$mail_mdp = false;

try {

	$db = db_open();

	$compte_existe = false;
	if (isset($_POST["email1"]) && filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) $compte_existe = boolval(db_select_compte_existe($db,$_POST["email1"])[0]["compte_existe"]);

	if (isset($_GET["creation_compte"]) && !$compte_existe && isset($_POST["prenom"]) && isset($_POST["email1"]) && isset($_POST["age"]) && filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) {

		if (isset($_SESSION["captcha"]) && isset($_POST["captcha"]) && strlen($_POST["captcha"])>=1 && $_POST["captcha"]==$_SESSION["captcha"]) {
			$pass_text = sec_motdepasse_aleatoire();
			$pass_hash = sec_hash($pass_text);

			db_insert_compte($db, $_POST["prenom"], $_POST["age"], $_POST["email1"],$pass_hash);

			$succes = "Votre compte a été créé. Vous allez recevoir votre mot de passe par mail. &#x1F525;";
			$mail_mdp = $pass_hash;
		}
		else {
			$output .= "Erreur de captcha.";
		}
	} 
	elseif (isset($_GET["nouveau_motdepasse_svp"]) && $compte_existe && isset($_POST["email1"]) && filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) {
		$pass_text = sec_motdepasse_aleatoire();
		$pass_hash = sec_hash($pass_text);

		db_update_motdepasse_par_mail($db, $pass_hash, $_POST["email1"]);

		$succes = "Un nouveau mot de passe va vous être envoyé par mail (si ce compte existe). &#x2709;";
		$mail_mdp = $pass_hash;	
	}
	elseif (isset($_GET["nouveau_motdepasse_svp"])) {
		sleep(1);
		$succes = "Un nouveau mot de passe va vous être envoyé par mail (si ce compte existe). &#x2709;";
	}
	elseif (isset($_GET["creation_compte"])) {
		$output .= "Erreur lors du traitement de votre demande. Merci de verifier votre addresse mail. Peut-être avez-vous déja un compte?";
	}



	if ($mail_mdp) {
		$mail = new PHPMailer(true);

		//$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
		$mail->isSMTP();                                            //Send using SMTP
		$mail->Host       = SMTP_HOST;                     //Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
		$mail->Username   = SMTP_MAIL;                     //SMTP username
		$mail->Password   = SMTP_PASSWORD;                               //SMTP password
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
		$mail->Port       = SMTP_PORT;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

		//Recipients
		$mail->setFrom(SMTP_MAIL, 'MONCYCLE.APP');
		$mail->addAddress($_POST["email1"], $_POST["email1"]);     //Add a recipient
		//$mail->addReplyTo('info@example.com', 'Information');

		//Content
		$mail->isHTML(false);                                  //Set email format to HTML
		$mail->Subject = 'Nouveau mot de passe';
		$mail->Body    = "Bonjour,\n\nMerci d'utiliser MONCYCLE.APP!\n\nVotre nouveau mot de passe: " . $pass_text . "\nCe mot de passe est à changer dans la rubrique \"Mon compte\".\n\nA bientôt.";
		//$mail->AltBody = 'Votre mot de passe: ' . $pass_text;

		$mail->send();
	}

}
catch (Exception $e){
	
	$output .= $e->getMessage();

}


?><!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
		<meta name="mobile-web-app-capable" content="yes" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
		<link rel="apple-touch-icon" href="/img/bill512.jpg" />
		<meta name="theme-color" media="(prefers-color-scheme: light)" content="white" />
		<meta name="theme-color" media="(prefers-color-scheme: dark)" content="black" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: light)" content="light-content" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: dark)" content="dark-content" />
		<title>MONCYCLE.APP</title>
		<link rel="stylesheet" href="css/commun.css" />
		<link rel="stylesheet" href="css/compte.css" />
	</head>

	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<a href="/"><button type="button" class="nav_button">Se connecter</button></a>
			<a href="https://www.moncycle.app"><button type="button" class="nav_button">Présentation de l'APP</button></a>
			<span class="vert"><?= $succes? "<br /><br />" . $succes : "" ?></span>
			<span class="rouge"><?= $output? "<br /><br />" . $output : "" ?></span>
		</center>

		<div class="contennu" id="timeline">
			<h2>Créer votre compte</h2>
			<form action="?creation_compte" method="post"><br />
			<label for="i_prenom">Prénoms:</label><br />
			<input name="prenom" type="text" maxlength="255" id="i_prenom" required placeholder='ex: "Alice et Benoît" ou "Charlotte"' value="<?= $_POST['prenom'] ?? "" ?>" /><br />
			<br />
			<label for="i_email1">E-mail:</label><br />
			<input name="email1" id="i_email1" type="email" maxlength="255" required placeholder="Entrer votre adresse mail."  value="<?= $_POST['email1'] ?? "" ?>" /><br />
			<br />
			<label for="i_anaissance">Année de naissance:</label><br />
			<select name="age" id="i_anaissance" required placeholder="">
			<option disabled selected class="placeholder">Selectionner votre année de naissance</option>
			<?php for ($i = date('Y')-(date('Y')%5)-75; $i < date('Y')-5; $i += 5) { ?>
				<option <?= $i==($_POST["age"]?? -1) ? "selected" : "" ?>  value="<?= $i ?>">entre <?= $i ?> et <?= $i+4 ?></option>	
			<?php } ?>
			</select><br /><br />
			<label for="i_email1">Captcha:</label><br />
			<input name="captcha" id="i_captcha" type="text" maxlength="6" required placeholder="Entrer les six lettres ou chiffres affichés ci-dessous." /><br />
			<img src="captcha.php" class="captcha" /><br />
			<br />

			<p>&#x1F1EB;&#x1F1F7; Ici on ne vend pas vos données. Elles sont hébergées en France et elles soumisent à la règlementation européenne.</p>
			<br />
			<input type="submit" value="Créer mon compte &#x1F942;&#x1F37E;" /></form>
			<br /><br /><br />
			<h2>Mot de passe perdu?</h2>
			<form action="?nouveau_motdepasse_svp" method="post"><br />
			<label for="i_email1">E-mail:</label><br />
			<input name="email1" id="i_email1" type="email" required placeholder="Votre adresse mail"  value="<?= $_POST['email1'] ?? "" ?>" /><br />
			<br />
			<input type="submit" value="Recevoir un nouveau mot de passe &#x2709;" /></form>
			<br /><br /><br />
		</div>


	</body>
</html>

