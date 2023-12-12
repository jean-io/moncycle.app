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

$db = db_open();

$compte = sec_auth_jetton($db);
sec_redirect_non_connecte($compte);

$erreur = "";
$succes = "";

if (isset($_REQUEST["suppr_compte"]) && isset($_POST["boutton_suppr"])) {
	db_delete_compte($db, $compte["no_compte"]);
	header('Location: /');
	exit;
}

if (isset($_REQUEST["mes_donnees_svp"])) {
	$export_compte = db_select_compte_par_nocompte($db, $compte["no_compte"]);
	$export_obs = db_select_all_observation($db, $compte["no_compte"]);

	header("content-type:application/csv;charset=UTF-8");
	header('Content-Disposition: attachment; filename="export_moncycle_app.csv"');

	$out = fopen('php://output', 'w');
	fputs($out, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

	fputs($out,"Export des données MONCYCLE.APP de " . $compte["nom_compte"] . PHP_EOL);
	fputs($out, PHP_EOL);

	foreach ($export_compte[0] as $key => $value) {
		fputs($out, $key . CSV_SEP . " ");
	}
	fputs($out, PHP_EOL);
	fputcsv($out, $export_compte[0], CSV_SEP);
	fputs($out, PHP_EOL);

	if (!isset($export_obs[0])) exit;

	foreach ($export_obs[0] as $key => $value) {
		fputs($out, $key . CSV_SEP . " ");
	}
	fputs($out, PHP_EOL);

	foreach ($export_obs as $key => $value) {
		fputcsv($out, $value, CSV_SEP);
	}
	fputs($out, PHP_EOL);

	fclose($out);
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
		<script type="text/javascript" src="vendor/components/jquery/jquery.min.js"></script> 
	</head>
	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<div id="nom"><?= $compte["nom_compte"] ?? "Mon compte" ?></div>
			<a href="/"><button type="button" class="nav_button">👈 Revenir aux cycles</button></a> <a href="api/deconnexion" onclick='window.localStorage.clear()'><button type="button" id="mon_compte" class="nav_button rouge">🔑 Déconnexion</button></a>
			<span class="vert"><?= $succes? "<br /><br />" . $succes : "" ?></span>
			<span class="rouge"><?= $erreur? "<br /><br />" . $erreur : "" ?></span>
			<p id="merci_don" style="display:none">🎖️ Merci pour votre don sur <a href="https://fr.tipeee.com/moncycleapp" target="_blank">Tipeee</a>.</p>
			<p id="warning_demo" style="font-weight:bold; display:none">&#x1F6A8; Vous visualisez actuellement le compte de démonstration.<br /><br /><a style="color:#fbca0b" href='/inscription'><button type='button'>&#x1F680; créer votre compte</button></a></p>
		</center>

		<div class="contennu" id="timeline">
		<h2>Modifier mes informations</h2>
		<form action="../api/param" method="post"><br />
		<label for="i_prenom">Prénom(s):</label><br />
		<input class="auto_save" type="text" id="i_prenom" required name="nom" value="<?= $compte["nom_compte"] ?? '' ?>" /><br />
		<br />
		J'ai besoin de suivre:<br />
		<span class="label_info">Modifier ce choix ne génère aucune perte de données.</span><br />
		<input type="radio" name="methode" value="2" class="auto_save" id="m_glaire" <?php if ($compte["methode"]==2): ?>checked<?php endif; ?>  required /><label for="m_glaire"><b>Billings</b>: l'évolution de la glaire cervicale seule</label><br />	
		<input type="radio" name="methode" value="3" class="auto_save" id="m_fc"  <?php if ($compte["methode"]==3): ?>checked<?php endif; ?>/><label for="m_fc"><b>FertilityCare</b>: l'évolution de la glaire cervicale + notation</label><br />	
		<input type="radio" name="methode" value="1" class="auto_save" id="m_temp"  <?php if ($compte["methode"]==1): ?>checked<?php endif; ?>/><label for="m_temp"><b>Symptothermie</b>: l'évolution de la glaire cervicale + les changements de température corporelle</label><br />	
		<br />
		<label for="i_email1">E-mail:</label> <br /><span class="label_info">Identifiant de connexion et envoi des cycles (non modifiable).</span><br />
		<input id="i_email1" type="email" readonly name="email1" value="<?= $compte['email1'] ?? '' ?>" /><br />
		<br />
		<label for="i_email2">2ème e-mail:</label> <br /><span class="label_info">Permet de recevoir les cycles sur une deuxième addresse.</span><br />
		<input id="i_email2" class="auto_save" type="email" name="email2" value="<?= $compte['email2'] ?? '' ?>" /><br />
		<br />
		<label for="i_anaissance">Année de naissance:</label><br />
		<select id="i_anaissance" name="age" class="auto_save" required>
		<?php for ($i = date('Y')-(date('Y')%5)-75; $i < date('Y')-5; $i += 5) { ?>
			<option <?= $i==($compte["age"]?? -1) ? "selected" : "" ?> value="<?= $i ?>">entre <?= $i ?> et <?= $i+4 ?></option>	
		<?php } ?>
		</select><br />
		<!-- <br />
		<input type="submit" value="&#x1F4BE; enregistrer" /> -->
		</form>
		<br />
		<br />
		<br />
		<h2>Changer mon mot de passe</h2>
		<form id="form_mdp_change" action="?change_motdepasse" method="post">
		<br />
		<label for="mdp_old">Ancien mot de passe:</label><br />
		<input type="password" name="mdp_old" required /><br />  
		<br />
		<label for="mdp1">Nouveau mot de passe:</label><br />
		<input type="password" name="mdp1" id="i_mdp1" required pattern="^(?=.*?[a-z])(?=.*?[0-9]).{7,}$" /><br />
		<span class="label_info">Le mot de passe doit contenir au moins 10 caractères dont un chiffre et une majuscule.</span><br/>
		<br />
		<label for="mdp2">Confirmer votre nouveau mot de passe:</label><br />
		<input type="password" name="mdp2" id="i_mdp2" required /><br />
		<br />
		<input id="but_mdp_change" type="submit" value="&#x1F4BE; enregistrer" /><br />
		<span id="mdp_ret_msg" class=""></span><br /></form>
		<br />
		<h2>À propos et contact</h2>
		<p>Cette application est gratuite et sans publicité/vente de données! Vous pouvez cependant contribuer au financement de l'application et aider le développeur via </label><a target="_blank" href="https://fr.tipeee.com/moncycleapp">tipeee.com/moncycleapp</a>.</p>
		<p>Cette application est Open Source: le code est disponible sur <a href="https://github.com/jean-io/moncycle.app" target="_blank">github.com/jean-io/moncycle.app</a>.</p>
		<p>Retrouvez toutes les informations de cette application sur <a href="https://www.moncycle.app" target="_blank">www.moncycle.app</a>.</p>
		<p>Un bug? Besoin d'aide? Une question? Une suggestion? Une demande liée aux données personnelles utilisées? Envoyez-nous un mail à <a href="mailto:moncycle.app@thjn.fr">moncycle.app@thjn.fr</a>.</p>
		<br />
		<h2 class="rouge">Zone de danger</h2>
		<span class="rouge">En supprimant définitivement votre compte, toutes vos données seront effacées et irrécupérables. Cette action est irréversible mais vous avez la possibilité de télécharger toutes vos données en amont de la suppression.</span><br />
		<br />
		<a href="?mes_donnees_svp"><input type="button" value="📦 Exporter mes données" /></a> <form method="post" action="?suppr_compte" onsubmit="return confirm('Êtes-vous sur de vouloir supprimer votre compte ainsi que toutes vos données? Cette action est irréversible.')"><input name="boutton_suppr" type="submit" class="rouge" value="⚠️ Supprimer mon compte" /></form>
		<br /><br /><br /><br /><br /><br />
		</div>
		<script>

			var moncycle_app_usr = {};

			$(document).ready(function(){

				// TELECHARGEMENT DES DONNES DES UTILISATEUR
				$.get("api/constante.php", {}).done(function(data) {
					moncycle_app_usr = data;
					if(moncycle_app_usr.donateur) $("#merci_don").show();
					if(moncycle_app_usr.id_utilisateur == 2) $("#warning_demo").show();
					console.log(data);
				});

				// MISE A JOURS DES PARAMETTRE DU COMPTE
				$(".auto_save").on("keyup change", function() {
					$.post("../api/param", `${$(this).attr('name')}=${this.value}`).fail(function(data){
						console.error(data);
					}).done(function(data){
						console.log(data);
						if(data.hasOwnProperty("nom")) {
							console.log(moncycle_app_usr);
							$("#nom").text(data.nom);
						}
					});
				});

				// CHAGEMENT DU MOT DE PASSE
				$("#form_mdp_change").on("submit", function(event) {
					event.preventDefault();
					if ($("#i_mdp1").val() != $("#i_mdp2").val()) {
						$("#mdp_ret_msg").removeClass("vert");
						$("#mdp_ret_msg").addClass("rouge");
						$("#mdp_ret_msg").text("Erreur: le nouveau mot de passe et sa confirmation ne sont pas identiques.");
						return;
					}
					$("#but_mdp_change").prop("disabled", true);
					var form_data = $("#form_mdp_change").serializeArray();
					$.post("../api/mdp_change", $.param(form_data)).done(function(ret){
						console.log(ret);
						$("#but_mdp_change").prop("disabled", false);
						if (ret.change_ok) {
							$("#mdp_ret_msg").removeClass("rouge");
							$("#mdp_ret_msg").addClass("vert");
							$("#mdp_ret_msg").text("Mot de passe changé avec succès.");
						}
						else {
							$("#mdp_ret_msg").removeClass("vert");
							$("#mdp_ret_msg").addClass("rouge");
							$("#mdp_ret_msg").text(`Erreur: ${ret.msg}.`);
						}
					}).fail(function(ret){
						console.error(ret);
						$("#but_mdp_change").prop("disabled", false);
					});
				});

			});
		</script>
	</body>
</html>
