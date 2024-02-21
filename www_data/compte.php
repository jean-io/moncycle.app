<!doctype html>
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
		<link rel="stylesheet" href="css/commun.css?v=<?= filemtime('css/commun.css') ?>" />
		<link rel="stylesheet" href="css/compte.css?v=<?= filemtime('css/compte.css') ?>" />
		<script type="text/javascript" src="vendor/components/jquery/jquery.min.js?v=<?= filemtime('vendor/components/jquery/jquery.min.js') ?>"></script> 
		<script type="text/javascript" src="js/compte.js?v=<?= filemtime('js/compte.js') ?>"></script>
	</head>
	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<div id="nom">Mon compte</div>
			<a href="/"><button type="button" class="nav_button">👈 Revenir aux cycles</button></a> <a href="api/deconnexion" onclick='window.localStorage.clear()'><button type="button" id="mon_compte" class="nav_button rouge">🔑 Déconnexion</button></a>
			<p id="merci_don" style="display:none">🎖️ Merci pour votre don sur <a href="https://fr.tipeee.com/moncycleapp" target="_blank">Tipeee</a>.</p>
			<p id="warning_demo" style="font-weight:bold; display:none">&#x1F6A8; Vous visualisez actuellement le compte de démonstration.<br /><br /><a style="color:#fbca0b" href='/inscription'><button type='button'>&#x1F680; créer votre compte</button></a></p>
		</center>

		<div class="contennu" id="timeline">



			<h2>Exporter mes données</h2>
			<p>Vous avez la possibilité d’exporter une partie de vos observations au format PDF. Cela permet d'avoir plusieurs cycles sur un même document. Comme demandé par la RGPD, vous avez aussi la possibilité d’exporter l’ensemble des données liées à votre compte. Cet export sera au format CSV.</p>
			<br >
			<form method="get" action="api/export" id="f_export_complexe">
			Exporter du <input id="i_start_date" name="start_date" type="date" /> au <input id="i_end_date" name="end_date" type="date" />. <input type="submit" value="📄 Créer PDF" />
			<span class="rouge" id="export_err"></span>
			</form>
			<br /><br />
			<a href="api/mes_donnees_svp"><input type="button" value="📦 Exporter brut de tout mon compte" /></a>
			<br /><br /><br />
	


			<h2>Modifier mes informations/préférences<span class="net_stat" id="net_stat"></span></h2>
			<form action="../api/param" method="post" id="f_info_pref"><br />
			<label for="i_prenom">Prénom(s):</label><br />
			<input class="auto_save" type="text" id="i_prenom" required name="nom" value="" /><br />
			<br />
			<input class="auto_save" value="1" type="checkbox" id="i_timeline_asc" name="timeline_asc"/> <label for="i_timeline_asc">Afficher les date en ordre croissant (aujourd'hui en premier et les dates les plus anciènne en bas).</label><br />
			<br />
			J'ai besoin de suivre:<br />
			<span class="label_info">Modifier ce choix ne génère aucune perte de données.</span><br />
			<input type="radio" name="methode" value="2" class="auto_save" id="m_2" required /><label for="m_2"><b>Billings</b>: l'évolution de la glaire cervicale seule.</label><br />
			<input type="radio" name="methode" value="1" class="auto_save" id="m_1"  /><label for="m_1"><b>Billings + température</b>: l'évolution de la glaire cervicale avec suivi des évolutions de la température corporelle.</label><br />
			<input type="radio" name="methode" value="3" class="auto_save" id="m_3" /><label for="m_3"><b>FertilityCare</b>: l'évolution de la glaire cervicale avec notation FertilityCare</label><br />
			<input type="radio" name="methode" value="4" class="auto_save" id="m_4" /><label for="m_4"><b>FertilityCare + température</b>: l'évolution de la glaire cervicale avec notation FertilityCare et suivi des évolutions de la température corporelle.</label><br />
			<br />
			<input class="auto_save" value="1" type="checkbox" id="i_recherche" name="recherche"/> <label for="i_recherche">Autoriser des exports de la base de données avec des cycles anonymisés pour la recherche sur les méthodes naturelles.</label><br />
			<br />
			<label for="i_email1">E-mail:</label> <br /><span class="label_info">Identifiant de connexion et envoi des cycles (non modifiable).</span><br />
			<input id="i_email1" type="email" readonly name="email1" value="" /><br />
			<br />
			<label for="i_email2">2ème e-mail:</label> <br /><span class="label_info">Permet de recevoir les cycles sur une deuxième addresse.</span><br />
			<input id="i_email2" class="auto_save" type="email" name="email2" value="" /><br />
			<br />
			<label for="i_anaissance">Année de naissance:</label><br />
			<select id="i_anaissance" name="age" class="auto_save" required></select><br />
			<!-- <br />
			<input type="submit" value="💾 enregistrer" /> -->
			</form>
			<br /><br /><br />



			<h2>Authentification multifacteur <span class="net_stat" id=""></span></h2>
			<div id="totp_explications" style="display: none;">
				<p>
					La double authentification est une méthode de sécurité qui demande à l'internaute de fournir deux types d'informations différentes pour prouver son identité.
					Ainsi, pour accéder à votre tableau il vous sera demandé de saisir votre mot de passe et un code temporaire disponnible dans une applications sur votre téléphone.
					Ce système permet de renforcer la sécurité de vos données personnelles en cas de fuite ou de vol de votre mot de passe ou d'un mot de passe trop faible (entre autres).
				</p>
				<input type="button" id="i_activate_otp" value="🔐 activer l'authentification multifacteur" />
			</div>
			<div id="totp_miseenpalce" style="display: none;">
				<form id="f_totp_validation" action="../api/totp?activation" method="post">
					<p>Configurez votre application d'authentification multifacteur. Vous n'avez pas encore d'application de ce type? téléchargez <a target="_blank" href="https://freeotp.github.io/">FreeOTP</a>.</p>
					<p><span class="totp_activation_option">option 1: </span>si vous êtes sur votre téléphone, cliquez sur le boutton ci-dessous pour une configuration automatique.</p>
					<a id="totp_auto_conf"><button type="button">🤖 CONFIGURER</button></a>
					<p><span class="totp_activation_option">option 2: </span>si vous êtes sur votre ordinateur, scannez le QR code ci-dessous depuis l'application depuis votre téléphone.</p>
					<div id="totp_qrcode"></div>
					<p><span class="totp_activation_option">option 3: </span>copiez-collez manuellement le secret directement dans l'application.</p>
					<button id="totp_copy_secret" type="button">📎 COPIER</button> <span class="vert" id="totp_copy_secret_ok"></span>
					<p>Après avoir réalisé l'une de ces trois options, entrez le code généré par l'application:</p>
					<input type="number" name="tmp_code" class="input_compte_totp" placeholder="code TOTP" />
					<input type="submit" id="i_submit_otp" value="🔐 activer" />
				</form>
			</div>
			<div id="totp_actif" style="display: none;">
				<p class="vert">✅ 🔐 Authentification multifacteur active.</p>
				<form id="f_totp_desac" action="../api/totp?desactivation" method="post">
					<p>Pour désactiver l'authentification multifacteur, entrez le code temporaire dans votre application:</p>
					<input type="number" name="tmp_code" placeholder="code TOTP" class="input_compte_totp" />
					<input type="submit" value="Désactiver" />
				</form>
			</div>
			<p class="rouge" id="totp_err_msg"></p>
			<br /><br /><br />



			<h2>Changer mon mot de passe <span class="net_stat" id="mdp_change_ok"></span></h2>
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
			<input id="but_mdp_change" type="submit" value="💾 enregistrer" /><br />
			<span id="mdp_ret_msg" class="rouge"></span><br /></form>
			<br /><br /><br />
			<h2>À propos et contact</h2>
			<p>Cette application est gratuite et sans publicité/vente de données! Vous pouvez cependant contribuer au financement de l'application et aider le développeur via </label><a target="_blank" href="https://fr.tipeee.com/moncycleapp">tipeee.com/moncycleapp</a>.</p>
			<p>Cette application est Open Source: le code est disponible sur <a href="https://github.com/jean-io/moncycle.app" target="_blank">github.com/jean-io/moncycle.app</a>.</p>
			<p>Retrouvez toutes les informations de cette application sur <a href="https://www.moncycle.app" target="_blank">www.moncycle.app</a>.</p>
			<p>Un bug? Besoin d'aide? Une question? Une suggestion? Une demande liée aux données personnelles utilisées? Envoyez-nous un mail à <a href="mailto:moncycle.app@thjn.fr">moncycle.app@thjn.fr</a>.</p>
			<br /><br /><br />



			<h2 class="rouge">Zone de danger</h2>
			<p class="rouge">En supprimant définitivement votre compte, toutes vos données seront effacées et irrécupérables. Cette action est irréversible mais vous avez la possibilité de télécharger toutes vos données avant la suppression.</p>
			<br />
			<form method="delete" action="api/suppr_compte" id="f_suppr_compte">
				<label class="rouge" for="mdp_pour_supprimer">Entrez votre mot de passe pour supprimer votre compte:</label><br />
				<input class="rouge" name="mdp_pour_supprimer" name="mdp_pour_supprimer" type="password" placeholder="mot de passe" /><br />
				<br />
				<input type="submit" class="rouge" value="⚠️ Supprimer définitivement mon compte" />
			</form>
			<br /><br /><br /><br />
			<center>
				<p class="tech_info">
					Numéro de compte : <span id="tech_info_no"></span><br />
					Identifiant : <span id="tech_info_id">jean.mercadier@thjn.fr</span><br />
					Date d'inscription : <span id="tech_info_insc">15 fév. 2024</span><br />
					Version du serveur/API : <span id="tech_info_ver">v8</span>
				</p>
			</center>
			</div>
	</body>
</html>
