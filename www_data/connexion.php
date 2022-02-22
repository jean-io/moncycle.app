<?php

require_once "config.php";

session_start();

$output = "";

try {

	if (isset($_REQUEST["deconnexion_svp"])) {
		$_SESSION["connected"] = false;
		session_unset();
		session_destroy();
		session_write_close();
		setcookie(session_name(),'',0,'/');
		session_regenerate_id(true);
		header('Location: /');
		exit;
	}

	if (isset($_SESSION["connected"]) && $_SESSION["connected"]) {
		header('Location: /');
		exit;
	}


	if (isset($_POST["email1"]) && isset($_POST["mdp"]) && filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) {

		$db = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_ID, DB_PASSWORD);

		$sql = "select * from compte where email1 like :email1";
		$statement = $db->prepare($sql);
		$statement->bindValue(":email1", $_POST["email1"], PDO::PARAM_STR);
		$statement->execute();

		$compte = $statement->fetchAll(PDO::FETCH_ASSOC)[0] ?? [];
		
		if (isset($compte["nb_co_echoue"]) && intval($compte["nb_co_echoue"])>=5) sleep(5);
		elseif (!isset($compte["nb_co_echoue"]) && rand(0,5)==0) sleep(5);

		if (empty($_POST["email1"]) || empty($_POST["mdp"])) {
			$output .= "E-mail et mot de passe manquant.";
		}
		elseif (isset($compte["actif"]) && !boolval($compte["actif"])) {
			$output .= "Compte désactivé. Contactez l'administrateur.";
		}		
		elseif (isset($compte["motdepasse"]) && password_verify($_POST["mdp"], $compte["motdepasse"])) {
			$output .= "Connecté!";
		
			unset($compte["motdepasse"]);
			$_SESSION["connected"] = true;
			$_SESSION["compte"] = $compte;
			$_SESSION["no"] = intval($compte["no_compte"] ?? -1);

			$sql ="update compte set derniere_co_date = now(), nb_co_echoue = 0 where no_compte = :no_compte";
			
			$statement = $db->prepare($sql);
			$statement->bindValue(":no_compte", $_SESSION["no"], PDO::PARAM_INT);
			$statement->execute();

			header('Location: /');
			exit;

		}
		else {
			$sql ="update compte set nb_co_echoue = nb_co_echoue + 1 where email1 like :email1";
			
			$statement = $db->prepare($sql);
			$statement->bindValue(":email1", $_POST["email1"], PDO::PARAM_STR);
			$statement->execute();

			$output .= "Mauvais mot de passe ou compte inexistant.";
		}
	
	}

}
catch (Exception $e){
	
	echo $e->getMessage();

}


?><!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
		<meta name="mobile-web-app-capable" content="yes" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
		<meta name="theme-color" media="(prefers-color-scheme: light)" content="white" />
		<meta name="theme-color" media="(prefers-color-scheme: dark)" content="black" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: light)" content="light-content" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: dark)" content="dark-content" />
		<title>MONCYCLE.APP</title>
		<link rel="stylesheet" href="css/commun.css" />
		<link rel="stylesheet" href="css/compte.css" />
		<style>
			.contennu {
				max-width: 300px;
			}
		</style>
	</head>

	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<a href="inscription.php"><button type="button" class="nav_button">Créer un compte</button></a>
			<a href="inscription.php"><button type="button" class="nav_button">Mot de passe perdu</button></a>
			<a href="https://www.moncycle.app"><button type="button" class="nav_button">Présentation de l'APP</button></a>
		</center>

		<div class="contennu" id="timeline">
			<h2>Connexion à votre compte</h2>
			<span class="rouge"><?= $output? $output . "<br />" : "" ?></span>
			<form method="post"><br />
			<label for="i_email1">E-mail:</label><br />
			<input name="email1" id="i_email1" type="email" required placeholder="Votre adresse mail"  value="<?= $_REQUEST['email1'] ?? "" ?>" /><br />
			<br />
			<label for="i_mdp">Mot de passe:</label><br />
			<input name="mdp" id="i_mdp" type="password" required placeholder="Votre mot de passe"  value="<?= $_REQUEST['mdp'] ?? "" ?>" /><br />
			<br />
			<input type="submit" value="Connexion &#x1F511;" /></form>
			<br /><br /><br />
			
		</div>


	</body>
</html>

