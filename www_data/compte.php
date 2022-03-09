<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "config.php";
require_once "lib/db.php";
require_once "lib/sec.php";

session_start();

if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
	header('Location: connexion.php');
	exit;
}

$db = db_open();

$erreur = "";
$succes = "";

if (isset($_REQUEST["change_motdepasse"])) {
	if (!empty($_POST["mdp1"])) {
		db_udpate_motdepasse_par_nocompte($db, sec_hash($_POST["mdp1"]), $_SESSION["no"]);
		$succes .= "Votre mot de passe a été changé. &#x270C;";
	}
	else {
		$erreur .= "Merci de renseigner un nouveau mot de passe.";
	}
}

if (isset($_REQUEST["modif_compte"]) && (empty($_POST["email2"]) || (!empty($_POST["email2"]) && filter_var($_POST["email2"], FILTER_VALIDATE_EMAIL)) )) {
	$methode = intval($_POST["methode"]);
	if (!$methode || $methode<1 || $methode>3) {
		$erreur .= "Erreur dans l'enregistrement de la méthode.";
	}
	else {
		db_update_compte($db, $_POST["nom"], $_POST["email2"], $_POST["age"], $methode, $_SESSION["no"]);

		$compte = db_select_compte_par_nocompte($db, $_SESSION["no"])[0] ?? [];
		unset($compte["motdepasse"]);
		$_SESSION["compte"] = $compte;

		$succes .= "Vos informations ont été mises à jour. &#x1F44F;";
	}
}

if (isset($_REQUEST["suppr_compte"]) && isset($_POST["boutton_suppr"])) {
	db_delete_compte($db, $_SESSION["no"]);
	$_SESSION["connected"] = false;

	header('Location: connexion?deconnexion_svp');
	exit;
}

if (isset($_REQUEST["mes_donnees_svp"])) {
	$export_compte = db_select_compte_par_nocompte($db, $_SESSION["no"]);
	$export_obs = db_select_all_observation($db, $_SESSION["no"]);

	header("content-type:application/csv;charset=UTF-8");
	header('Content-Disposition: attachment; filename="export_moncycle_app.csv"');

	print(mb_convert_encoding("Export des données MONCYCLE.APP de " . $_SESSION["compte"]["nom"], 'UTF-16LE', 'UTF-8'));	
	print(PHP_EOL);
	print(PHP_EOL);

	foreach ($export_compte[0] as $key => $value) {
		print(mb_convert_encoding($key . CSV_SEP . " ", 'UTF-16LE', 'UTF-8'));
	}
	print(PHP_EOL);
	print(mb_convert_encoding(implode(CSV_SEP, $export_compte[0]), 'UTF-16LE', 'UTF-8'));
	print(PHP_EOL);
	print(PHP_EOL);

	if (!isset($export_obs[0])) exit;

	foreach ($export_obs[0] as $key => $value) {
		print(mb_convert_encoding($key . CSV_SEP . " ", 'UTF-16LE', 'UTF-8'));
	}
	print(PHP_EOL);
	foreach ($export_obs as $key => $value) {
		print(mb_convert_encoding(implode(CSV_SEP, $value), 'UTF-16LE', 'UTF-8'));
		print(PHP_EOL);
	}
	print(PHP_EOL);

	exit;
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
		<?= file_get_contents("./vue/head.html") ?>
		<link rel="stylesheet" href="css/commun.css?h=<?= hash_file("sha1", "./css/commun.css") ?>" />
		<link rel="stylesheet" href="css/compte.css?h=<?= hash_file("sha1", "./css/compte.css") ?>" />
	</head>
	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<div id="nom"><?= $_SESSION["compte"]["nom"] ?? "Mon compte" ?></div>
			<a href="/"><button type="button" class="nav_button">👈 Revenir aux cycles</button></a> <a href="connexion?deconnexion_svp"><button type="button" id="mon_compte" class="nav_button rouge">🔑 Déconnexion</button></a>
			<span class="vert"><?= $succes? "<br /><br />" . $succes : "" ?></span>
			<span class="rouge"><?= $erreur? "<br /><br />" . $erreur : "" ?></span>
			<?php if(boolval($_SESSION["compte"]["donateur"])): ?><p>🎖️ Merci pour votre don sur <a href="https://fr.tipeee.com/moncycleapp" target="_blank">Tipeee</a>.</p><?php endif; ?>
		</center>

		<div class="contennu" id="timeline">
		<h2>Modifier mes informations</h2>
		<form action="?modif_compte" method="post"><br />
		<label for="i_prenom">Prénom(s):</label><br />
		<input type="text" id="i_prenom" required name="nom" value="<?= $_SESSION['compte']['nom'] ?? '' ?>" /><br />
		<br />
		J'ai besoin de suivre:<br />
		<span class="label_info">Modifier ce choix ne génère aucune perte de données.</span><br />
		<input type="radio" name="methode" value="2" id="m_glaire" <?php if ($_SESSION["compte"]["methode"]==2): ?>checked<?php endif; ?>  required /><label for="m_glaire">l'évolution de la glaire cervicale</label><br />	
		<input type="radio" name="methode" value="3" id="m_temp"  <?php if ($_SESSION["compte"]["methode"]==3): ?>checked<?php endif; ?>/><label for="m_temp">les changements de température corporelle</label><br />	
		<input type="radio" name="methode" value="1" id="m_les2"  <?php if ($_SESSION["compte"]["methode"]==1): ?>checked<?php endif; ?>/><label for="m_les2">les deux</label><br />	
		<br />
		<label for="i_email1">E-mail:</label> <br /><span class="label_info">Identifiant de connexion et envoie des cycles (non modifiable).</span><br />
		<input id="i_email1" type="email" readonly name="email1" value="<?= $_SESSION['compte']['email1'] ?? '' ?>" /><br />
		<br />
		<label for="i_email2">2ème e-mail:</label> <br /><span class="label_info">Permet de recevoir les cycles sur une deuxième addresse.</span><br />
		<input id="i_email2" type="email" name="email2" value="<?= $_SESSION['compte']['email2'] ?? '' ?>" /><br />
		<br />
		<label for="i_anaissance">Année de naissance:</label><br />
		<select id="i_anaissance" name="age" required>
		<?php for ($i = date('Y')-(date('Y')%5)-75; $i < date('Y')-5; $i += 5) { ?>
			<option <?= $i==($_SESSION["compte"]["age"]?? -1) ? "selected" : "" ?> value="<?= $i ?>">entre <?= $i ?> et <?= $i+4 ?></option>	
		<?php } ?>
		</select><br />
		<br />
		<input type="submit" value="&#x1F4BE; enregistrer" /></form>
		<br />
		<br />
		<br />
		<h2>Changer mon mot de passe</h2>
		<form action="?change_motdepasse" method="post">
		<span class="label_info">Le mot de passe doit contenir au moins 8 caractères dont un chiffre et une majuscule.</span><br/>
		<br />
		<label for="mdp1">Nouveau mot de passe:</label><br />
		<input type="password" name="mdp1" required pattern="^(?=.*?[a-z])(?=.*?[0-9]).{7,}$" /><br />  
		<br />
		<!--<label for="mdp2">Confirmer mot de passe:</label><br />
		<input type="password" name="mdp2" required /><br />
		<br />-->
		<input type="submit" value="&#x1F4BE; enregistrer" /><br />
		</form><br />
		<br />
		<h2>A propos et contact</h2>
		<p>Cette application est gratuite et sans publicité/vente de donnnées! Vous pouvez cependant contribuer au financement de l'application et aider le développer via </label><a target="_blank" href="https://fr.tipeee.com/moncycleapp">tipeee.com/moncycleapp</a>.</p>
		<p>Cette application est Open Source: le code est disponnible sur <a href="https://github.com/jean-io/moncycle.app" target="_blank">github.com/jean-io/moncycle.app</a>.</p>
		<p>Retrouvez toutes les informations de cette application sur <a href="https://www.moncycle.app" target="_blank">www.moncycle.app</a>.</p>
		<p>Un bug? Besoin d'aide? Une question? Une suggestion? Une demande? Envoyez-nous un mail à <a href="mailto:moncycle.app@thjn.fr">moncycle.app@thjn.fr</a>.</p>
		<br />
		<h2 class="rouge">Zone de danger</h2>
		<span class="rouge">En supprimant définitivement votre compte, toutes vos données seront effacées et irrécupérables. Cette action est ireversible mais vous avez la possibilité de télècharger toutes vos donné en amont de la suppression.</span><br />
		<br />
		<a href="?mes_donnees_svp"><input type="button" value="📦 Exporter mes données" /></a> <form method="post" action="?suppr_compte" onsubmit="return confirm('Êtes-vous sur de vouloir supprimer votre compte ainsi que toutes vos données? Cette action est irreversible.')"><input name="boutton_suppr" type="submit" class="rouge" value="⚠️ Supprimer mon compte" /></form>
<br /><br /><br /><br /><br /><br />
</div>


	</body>
</html>
