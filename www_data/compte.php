<?php

require_once "password.php";

session_start();
$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);

if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
	header('Location: connexion.php');
	exit;
}

$db = new PDO('mysql:host=nas_ovpn;dbname=dev_moncyle_app_nas', 'jean_dev', DB_PASSWORD);

$erreur = "";
$succes = "";

if (isset($_REQUEST["change_motdepasse"])) {
	if (!empty($_POST["mdp1"])) {
		$pass_hash = password_hash($_POST["mdp1"], PASSWORD_BCRYPT);

		$sql = "UPDATE compte SET motdepasse = :motdepasse, mdp_change_date = now() WHERE no_compte = :no_compte";

		$statement = $db->prepare($sql);
		$statement->bindValue(":no_compte", $_SESSION["no"], PDO::PARAM_INT);
		$statement->bindValue(":motdepasse", $pass_hash, PDO::PARAM_STR);
		$statement->execute();

		//return $statement->fetchAll(PDO::FETCH_ASSOC);
		$succes .= "Votre mot de passe a été changé. &#x270C;";
	}
	else {
		$erreur .= "Merci de renseigner un nouveau mot de passe.";
	}
}


?><!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
		<meta name="mobile-web-app-capable" content="yes" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
		<meta name="apple-mobile-web-app-title" content="Bill" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<link rel="apple-touch-icon" href="/img/bill512.jpg" />
		<meta name="theme-color" media="(prefers-color-scheme: light)" content="white" />
		<meta name="theme-color" media="(prefers-color-scheme: dark)" content="black" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: light)" content="light-content" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: dark)" content="dark-content" />
		<title>moncycle.app</title>
		<script src="jquery.min.js"></script> 
		<link rel="stylesheet" href="css/commun.css" />
		<link rel="stylesheet" href="css/compte.css" />
	</head>

	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<div id="nom"><?= $_SESSION["compte"]["nom"] ?? "Mon compte" ?></div>
			<a href="/"><button type="button" class="nav_button">Revenir aux cycles</button></a> <a href="connexion.php?deconnexion_svp"><button type="button" id="mon_compte" class="nav_button">Déconnexion</button></a>
			<span class="vert"><?= $succes? "<br /><br />" . $succes : "" ?></span>
			<span class="rouge"><?= $erreur? "<br /><br />" . $erreur : "" ?></span>
		</center>

		<div class="contennu" id="timeline">
		<h2>Mofifier mes informations</h2>
		<form><br />
		<label for="i_prenom">Prénom(s):</label><br />
		<input type="text" id="i_prenom" required value="<?= $_SESSION['compte']['nom'] ?? '' ?>" /><br />
		<br />
		<label for="i_email1">E-mail:</label> <span class="label_info">identifiant de connexion et envoie des cycles.</span><br />
		<input id="i_email1" type="email" readonly value="<?= $_SESSION['compte']['email1'] ?? '' ?>" /><br />
		<br />
		<label for="i_email2">2ème e-mail:</label> <span class="label_info">permet de recevoir les cycles sur une deuxième addresse.</span><br />
		<input id="i_email2" type="email" value="<?= $_SESSION['compte']['email2'] ?? '' ?>" /><br />
		<br />
		<label for="i_anaissance">Année de naissance:</label><br />
		<select id="i_anaissance" required>
		<?php for ($i = date('Y')-(date('Y')%5)-75; $i < date('Y')-5; $i += 5) { ?>
			<option <?= $i==($_SESSION["compte"]["age"]?? -1) ? "selected" : "" ?> value="<?= $i ?>">entre <?= $i ?> et <?= $i+4 ?></option>	
		<?php } ?>
		</select><br />
		<br />
		<input type="submit" value="&#x1F4BE; enregistrer" /></form>
		<br />
		<br />
		<h2>Changer mon mot de passe</h2>
		<form action="?change_motdepasse" method="post">
		<span class="label_info">Le mot de passe doit contenir au moins 8 caractères dont un chiffre et une majuscule.</span><br/>
		<br />
		<label for="mdp1">Nouveau mot de passe:</label><br />
		<input type="password" name="mdp1" required/><br /><!--  pattern="^(?=.*?[a-z])(?=.*?[0-9]).{7,}$" -->
		<br />
		<label for="mdp2">Confirmer mot de passe:</label><br />
		<input type="password" name="mdp2" required /><br />
		<br />
		<input type="submit" value="&#x1F4BE; enregistrer" /><br />
		</form><br />
		<h2 class="rouge">Zone de danger</h2>
		<span class="rouge">En supprimant définitivement votre compte, toutes vos données seront effacées et irrécupérables. Cette action est ireversible mais vous avez la possibilité de télècharger toutes vos donné en amont de la suppression.</span><br />
		<br />
		<a href="#"><button>Exporter toutes mes données</button></a> <button>&#x26A0; Supprimer definitivement mon compte</button>
<br /><br /><br /><br /><br /><br />
</div>


	</body>
</html>
