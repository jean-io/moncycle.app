<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "config.php";
require_once "lib/db.php";
require_once "lib/sec.php";
require_once "lib/mail.php";
require_once 'module/phpmailer/src/Exception.php';
require_once 'module/phpmailer/src/PHPMailer.php';
require_once 'module/phpmailer/src/SMTP.php';

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
			$methode = intval($_POST["methode"] ?? 0);
			if ($methode<1 || $methode>3) $methode=1;

			$pass_text = sec_motdepasse_aleatoire();
			$pass_hash = sec_hash($pass_text);

			db_insert_compte($db, $_POST["prenom"], $methode, $_POST["age"], $_POST["email1"],$pass_hash);

			$succes = "Votre compte a été créé. Vous allez recevoir votre mot de passe par mail. &#x1F525;";
			$mail_mdp = $pass_hash;
		}
		else {
			$output .= "Erreur dans la saisi du captcha.";
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
		$mail = mail_init();
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
		<?= file_get_contents("./vue/head.html") ?>
		<link rel="stylesheet" href="css/commun.css?h=<?= hash_file("sha1", "./css/commun.css") ?>" />
		<link rel="stylesheet" href="css/compte.css?h=<?= hash_file("sha1", "./css/compte.css") ?>" />

	</head>
	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<a href="/"><button type="button" class="nav_button">Se connecter</button></a>
			<span class="vert"><?= $succes? "<br /><br />" . $succes : "" ?></span>
			<span class="rouge"><?= $output? "<br /><br />" . $output : "" ?></span>
		</center>

		<div class="contennu" id="timeline">
			<h2>Créer votre compte</h2>
			<form action="?creation_compte" method="post"><br />
			<label for="i_prenom">Prénoms:</label><br />
			<input name="prenom" type="text" maxlength="255" id="i_prenom" required placeholder='ex: "Alice et Benoît" ou "Charlotte"' value="<?= $_POST['prenom'] ?? "" ?>" /><br />
			<br />
			J'ai besoin de suivre:<br />
			<input type="radio" name="methode" value="2" id="m_glaire" <?php if (($_POST["methode"] ?? 0) ==2): ?>checked<?php endif; ?> required /><label for="m_glaire">l'évolution de la glaire cervicale</label><br />	
			<input type="radio" name="methode" value="3" id="m_temp" <?php if (($_POST["methode"] ?? 0) ==3): ?>checked<?php endif; ?>/><label for="m_temp">les changements de température corporelle</label><br />	
			<input type="radio" name="methode" value="1" id="m_les2" <?php if (($_POST["methode"] ?? 0) ==1): ?>checked<?php endif; ?>/><label for="m_les2">les deux</label><br />	
			<span class="label_info">Vous pourrez modifier ce choix dans la rubrique "mom compte" sans perte de données une fois votre compte créé.</span><br />
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
			
			<label for="i_captcha">Captcha:</label><br />
			<input name="captcha" id="i_captcha" type="text" maxlength="6" required placeholder="Entrer les six lettres ou chiffres affichés ci-dessous." /><br />
			<img src="captcha.php" class="captcha" /><br />
			<br />
			<p><input type="checkbox" required id="jc_monito" name="monito" value="1" <?php if (boolval($_POST["monito"] ?? 0)): ?>checked<?php endif; ?>/> <label for="jc_monito">Je comprends que moncycle.app est seulement un support pour noter les différentes informations de mon cycle. En cas de difficulté dans la tenue de mon tableau, je me tournerai vers l'association qui propose la méthode que j'applique. &#x1F4DD;</label></p>
			<p><input type="checkbox" required id="jc_gratuit" name="gratuit" value="1" <?php if (boolval($_POST["gratuit"] ?? 0)): ?>checked<?php endif; ?>/> <label for="jc_gratuit">Je comprends que moncycle.app est gratuit et sans publicité/vente de donnnées! Je peux cependant contribuer au financement de l'application et aider le développer via la </label><a target="_blank" href="https://fr.tipeee.com/moncycleapp">page Tipeee de moncycle.app</a>. &#x1F4B6;</p>
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
			<center><a class="decouverte" href="https://www.moncycle.app">&#x1F60D; découvrir moncycle.app</a></center>
			<br /><br /><br />
		</div>


	</body>
</html>

