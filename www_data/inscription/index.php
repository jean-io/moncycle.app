<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

use PHPMailer\PHPMailer\Exception;

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

$db = db_open();

$compte = sec_auth_jetton($db);
if (!is_null($compte) && $compte["no_compte"]!=2 && $compte["no_compte"]!=3) {
	header('Location: /');
	echo "Déja connecté, redirection.";
	exit;
}

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
else {
	$jetton = sec_motdepasse_aleatoire(64);
	db_insert_jetton($db, NULL, "CAPTCHA | " .  $_SERVER['HTTP_USER_AGENT'], "FR", $jetton, 3);
	$arr_cookie_options = array (
		'expires' => strtotime('+2 days'), 
		'path' => '/',
		'secure' => true,
		'httponly' => true,
	);
	setcookie("MONCYCLEAPP_JETTON", $jetton, $arr_cookie_options);
}


$output = "";
$succes = "";

try {

	$db = db_open();

	$compte_existe = false;
	if (isset($_POST["email1"]) && filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) $compte_existe = boolval(db_select_compte_existe($db,$_POST["email1"])[0]["compte_existe"]);

	if (isset($_GET["creation_compte"]) && !$compte_existe) {

		if (!CREATION_COMPTE) {
			$output .= "La création de compte est temporairement désactivée. Veuillez nous excuser pour le désagrément.";
		}
		elseif (!isset($_POST["prenom"]) || !isset($_POST["email1"]) || !isset($_POST["age"]) || !filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) {
			$output .= "Toutes les données n'ont pas été saisies ou sont erronées.";
		}
		elseif (isset($_POST["captcha"]) && strlen(trim($_POST["captcha"]))>=1 && trim($_POST["captcha"])==$captcha) {
			$methode = intval($_POST["methode"] ?? 0);
			if ($methode<METHODE_BILLINGS || $methode>METHODE_FERTILITYCARE) $methode=METHODE_BILLINGS;
			if (intval($_POST["temp"] ?? 0)) {
				if ($methode == METHODE_BILLINGS)      $methode = METHODE_BILLINGS_TEMP;
				if ($methode == METHODE_FERTILITYCARE) $methode = METHODE_FERTILITYCARE_TEMP;
			}

			$pass_text = sec_motdepasse_aleatoire();
			$pass_hash = sec_hash($pass_text);

			db_insert_compte($db, $_POST["prenom"], $methode, $_POST["age"], $_POST["email1"],$pass_hash, $_POST["decouvert"] ?? null, $_POST["recherche"] ?? 0);

			$succes = "Félicitation <b>{$_POST["prenom"]}</b>: votre compte a été créé! &#x1F525;<br />Votre mot de passe vous a été envoyé par e-mail.";

			$mail = mail_init();
			$mail->addAddress($_POST["email1"], $_POST["email1"]);

			$mail->isHTML(false);
			$mail->Subject = 'Bienvenue et mot de passe';
			$mail->Body = mail_body_creation_compte($_POST["prenom"], $pass_text, $_POST["email1"]);
			$mail->AltBody = 'Bienvenue sur moncycle.app! Votre mot de passe: ' . $pass_text;

			$mail->send();

		}
		else {
			$output .= "Erreur dans la saisie du captcha.";
		}
	} 


	elseif (isset($_GET["nouveau_motdepasse_svp"]) && $compte_existe && isset($_POST["email1"]) && filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) {
		sleep(rand(1,5));

		$pass_text = sec_motdepasse_aleatoire();
		$pass_hash = sec_hash($pass_text);

		db_update_motdepasse_par_mail($db, $pass_hash, $_POST["email1"]);

		$succes = "Un nouveau mot de passe vous a été envoyé par mail (si ce compte existe). &#x2709;";

		$mail = mail_init();
		$mail->addAddress($_POST["email1"], $_POST["email1"]);

		$mail->isHTML(false);
		$mail->Subject = 'Nouveau mot de passe';
		$mail->Body = mail_body_nouveau_mdp($pass_text, $_POST["email1"]);
		$mail->AltBody = 'Nouveau mot de passe temporaire: ' . $pass_text;

		$mail->send();

	}
	elseif (isset($_GET["nouveau_motdepasse_svp"])) {
		sleep(rand(1,5));
		$succes = "Un nouveau mot de passe vous a été envoyé par mail (si ce compte existe). &#x2709;";
	}
	elseif (isset($_GET["creation_compte"])) {
		$output .= "Un compte existe déjà pour cette adresse mail.";
	}


}
catch (Exception $e){

	$output .= $e->getMessage();

}


?><!doctype html>
<!--
** moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
-->
<html lang="fr">
	<head>
		<meta charset="utf-8" />
		<meta name="mobile-web-app-capable" content="yes" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
		<meta name="apple-mobile-web-app-title" content="moncycle.app" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<link rel="apple-touch-icon" href="/img/moncycleapp512.jpg" />
		<meta name="theme-color" media="(prefers-color-scheme: light)" content="white" />
		<meta name="theme-color" media="(prefers-color-scheme: dark)" content="black" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: light)" content="light-content" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: dark)" content="dark-content" />
		<title>moncycle.app</title>
		<meta name="description" content="Application de suivi de cycle pour les méthodes naturelles de régulation des naissances." />
		<meta property="og:title" content="MONCYCLE.APP" />
		<meta property="og:type" content="siteweb" />
		<meta property="og:url" content="https://www.moncycle.app/" />
		<meta property="og:image" content="/img/moncycleapp_apercu.jpg" />
		<meta property="og:description" content="Application de suivi de cycle pour les méthodes naturelles de régulation des naissances." />
		<link rel="stylesheet" href="../css/commun.css?v=<?= filemtime('../css/commun.css') ?>" />
		<link rel="stylesheet" href="../css/compte.css?v=<?= filemtime('../css/compte.css') ?>" />
	</head>
	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<a class="decouverte" href="https://www.moncycle.app"><button type="button" class="nav_button">découvrir moncycle.app 😍</button></a>
			<a href="/connexion?email1=<?= $_POST['email1'] ?? "" ?>"><button type="button" class="nav_button">Se connecter 🔑</button></a>
			<span class="vert"><?= $succes? "<br /><br />" . $succes : "" ?></span>
			<span class="rouge"><?= $output? "<br /><br />" . $output : "" ?></span>
		</center>

		<div class="contennu" id="timeline" <?php if(!empty($succes)): ?>style="display:none;"<?php endif; ?>>
			<h2>Créer votre compte</h2>
			<form action="?creation_compte" method="post"><br />
			<label for="i_prenom">Prénoms:</label><br />
			<input name="prenom" type="text" maxlength="255" id="i_prenom" required placeholder='ex: "Alice et Benoît" ou "Charlotte"' value="<?= $_POST['prenom'] ?? "" ?>" /><br />
			<br />
			Méthode à suivre:<br />
			<input type="radio" name="methode" value="2" id="m_glaire" <?php if (($_POST["methode"] ?? 0) ==2): ?>checked<?php endif; ?> required /><label for="m_glaire"><b>Billings</b>: l'évolution de la glaire cervicale seule</label><br />
			<input type="radio" name="methode" value="3" id="m_fc" <?php if (($_POST["methode"] ?? 0) ==3): ?>checked<?php endif; ?>/><label for="m_fc"><b>FertilityCare</b>: l'évolution de la glaire cervicale + notation</label><br />
			<br />
			<input type="checkbox" name="temp" value="1" id="m_temp" <?php if (($_POST["temp"] ?? 0) ==1): ?>checked<?php endif; ?>/><label for="m_temp"><b>Température</b>: suivre dans l'application les évolutions de la température corporelle en plus de Billings ou de FertilityCare.</label><br />
			<br />
			<span class="label_info">Si vous suivez une méthode symptothermique (Cyclamen, SensiPlan, ...), vous pouvez cocher "Billings" avec "température". Pour ces méthodes symptothermiques, l'application est incomplète car elle ne propose pas une interprétation des courbes de températures efficace. Vous pourrez modifier ce choix dans la rubrique "mon compte" sans perte de données une fois votre compte créé.</span><br />
			<br />
			<label for="i_email1">E-mail:</label><br />
			<input name="email1" id="i_email1" type="email" maxlength="255" required placeholder="Entrer votre adresse mail."  value="<?= $_POST['email1'] ?? "" ?>" /><br />
			<br />
			<label for="i_anaissance">Année de naissance:</label><br />
			<select name="age" id="i_anaissance" required placeholder="">
			<option disabled selected class="placeholder">Sélectionner votre année de naissance</option>
			<?php for ($i = date('Y')-(date('Y')%5)-75; $i < date('Y')-5; $i += 5) { ?>
				<option <?= $i==($_POST["age"]?? -1) ? "selected" : "" ?>  value="<?= $i ?>">entre <?= $i ?> et <?= $i+4 ?></option>
			<?php } ?>
			</select><br />
			<br />
			<label for="i_captcha">Captcha:</label><br />
			<input name="captcha" id="i_captcha" type="text" maxlength="6" required placeholder="Entrer les six lettres ou chiffres affichés ci-dessous." /><br />
			<img src="captcha.php" class="captcha" /><br />
			<br />
			<label for="i_comment">Comment avez-vous découvert moncycle.app? Un commentaire?</label><br />
			<textarea required id="i_comment" name="decouvert" maxlength="255" placeholder="Dites nous tout!"><?= $_POST['decouvert'] ?? "" ?></textarea>
			<br />
			<p><input type="checkbox" required id="jc_monito" name="monito" value="1" <?php if (boolval($_POST["monito"] ?? 0)): ?>checked<?php endif; ?>/> <label for="jc_monito">📝 L'application nécessite d'être formé aux méthodes naturelles pour être utilisé. Je comprends que moncycle.app est seulement un support pour noter les différentes informations de mon cycle. En cas de difficultés dans la tenue de mon tableau, je me tournerai vers l'association qui propose la méthode que j'utilise en contactant une monitrice/instructrice.</label></p>
			<p><input type="checkbox" required id="jc_gratuit" name="gratuit" value="1" <?php if (boolval($_POST["gratuit"] ?? 0)): ?>checked<?php endif; ?>/> <label for="jc_gratuit">💶 Je comprends que moncycle.app est gratuit et sans publicité/vente de données! Je suis d'accord avec <a target="_blank" href="https://www.moncycle.app/#rgpd">la politique de gestion des données</a> conformément à la RGPD. Je peux cependant contribuer au financement de l'application et aider le développeur via la </label><a target="_blank" href="https://fr.tipeee.com/moncycleapp">page Tipeee de moncycle.app</a>.</p>
			<p><input type="checkbox" id="jc_recherche" name="recherche" value="1" <?php if (boolval($_POST["recherche"] ?? 0)): ?>checked<?php endif; ?>/> <label for="jc_recherche">👩‍🔬 J'autorise des exports de la base de données avec mes cycles anonymisés pour contribuer à des programmes de recherches sur les méthodes naturelles ou le cycle féminin. (vous pourrez modifier ce choix dans la rubrique "mon compte")</p>
			<br />
			<input type="submit" value="Créer mon compte &#x1F942;&#x1F37E;" /></form>
			<br /><br /><br />
			<h2>Mot de passe perdu?</h2>
			<form action="?nouveau_motdepasse_svp" method="post"><br />
			<label for="i_email1">E-mail:</label><br />
			<input name="email1" id="i_email1" type="email" required placeholder="Votre adresse mail"  value="<?= $_REQUEST['email1'] ?? "" ?>" /><br />
			<br />
			<input type="submit" value="Recevoir un nouveau mot de passe 📩" /></form>
			<br /><br /><br />
			<br /><br /><br />
		</div>


	</body>
</html>

