<?php

session_start();
$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);

if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
	header('Location: session.php');
	exit;
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
			<div id="nom">Thérèse et Jean</div>
			<a href="/"><button type="button" class="nav_button">Revenir aux cycles</button></a> <a href="session.php?deconnexion"><button type="button" id="mon_compte" class="nav_button">Déconnexion</button></a>
		</center>

		<div class="contennu" id="timeline">
		<h2>Vos informations</h2>
		<form><br />
		<label>Prénom(s):</label><br />
		<br />
		<br />
		<label>Email:</label> <span class="label_info">identifiant de connexion et envoie des cycles.</span><br />
		<br />
		<br />
		<label>2ème Email:</label> <span class="label_info">permet de recevoir les cycles sur une deuxième addresse.</span><br />
		<br />
		<br />
		<label>Année de naissance:</label><br />
		<br /></form>
		<a href="#"><button>Exporter toutes vos données</button></a><br />
		<br />
		<br />
		<h2>Changer de mot de passe</h2>
		<span class="label_info">Le mot de passe doit contenir au moins 8 caractères dont un chiffre et une majuscule.</span>
		<form><label for="mdp1">Nouveau mot de passe:</label><br />
		<input type="password" name="mdp1" pattern="^(?=.*?[a-z])(?=.*?[0-9]).{7,}$" /><br />
		<br />
		<label for="mdp2">Confirmer mot de passe:</label><br />
		<input type="password" name="mdp2" /><br />
		<br />
		<input type="submit" value="valider" /></form><br />
		<br />
		<h2>Zone de danger</h2>
		<span class="label_info">En supprimant définitivement votre compte, totues vos données seront supprimer. Cette action est ireversible.</span><br />
		<br />
		<button>Supprimer definitivement le compte moncycle.app de Thérèse et Jean</button>

</div>


	</body>
</html>
