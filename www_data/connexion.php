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
		<title>MONCYCLE.APP - connexion</title>
		<meta name="mobile-web-app-capable" content="yes" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
		<meta name="apple-mobile-web-app-title" content="moncycle.app" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<link rel="apple-touch-icon" href="/img/moncycleapp512.jpg" />
		<meta name="theme-color" media="(prefers-color-scheme: light)" content="white" />
		<meta name="theme-color" media="(prefers-color-scheme: dark)" content="black" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: light)" content="light-content" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: dark)" content="dark-content" />
		<meta name="description" content="Application de suivi de cycle pour les méthodes naturelles de régulation des naissances." />
		<meta property="og:title" content="MONCYCLE.APP" />
		<meta property="og:type" content="siteweb" />
		<meta property="og:url" content="https://www.moncycle.app/" />
		<meta property="og:image" content="/img/moncycleapp_apercu.jpg" />
		<meta property="og:description" content="Application de suivi de cycle pour les méthodes naturelles de régulation des naissances." />
		<script type="text/javascript" src="vendor/components/jquery/jquery.min.js?v=<?= filemtime('vendor/components/jquery/jquery.min.js') ?>"></script> 
		<link rel="stylesheet" href="css/commun.css?v=<?= filemtime('css/commun.css') ?>" />
		<link rel="stylesheet" href="css/compte.css?v=<?= filemtime('css/compte.css') ?>" />
		<style>
			.contennu {
				max-width: 300px;
			}
		</style>
	</head>
	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<a href="inscription"><button type="button" class="nav_button">Créer un compte 🚀</button></a>
			<a href="inscription"><button type="button" class="nav_button">Mot de passe perdu 🤯</button></a>
		</center>
		<div class="contennu" id="timeline">
			<h2>Connexion à votre compte</h2>
			<span class="rouge" id="message_err"></span>
			<form method="post" action="api/authentification" id="connexion_form"><br />
			<label for="i_email1">E-mail:</label><br />
			<input name="email1" id="i_email1" type="email" required placeholder="adresse mail de connexion"  value="" /><br />
			<br />
			<label for="i_mdp">Mot de passe:</label><br />
			<input name="mdp" id="i_mdp" type="password" required placeholder="mot de passe moncycle.app"  value="" /><br />
			<br />
			<label for="i_code">Code:</label><br />
			<input name="code" id="i_code" type="number" placeholder="authentification multifacteur (si activée)" value="" /><br />
			<br />
			<input type="submit" value="Connexion &#x1F511;" id="connexion_but"/></form>
			<br /><br /><br />
			<center><a class="decouverte" id="but_demo_bill" href="/connexion">compte de démo <b>Billings</b> 🥸</a></center>
			<center><a class="decouverte" id="but_demo_fc" href="/connexion">compte de démo <b>FertilityCare</b> 🧐</a></center>
			<center><a class="decouverte" href="https://www.moncycle.app">découvrir MON<b>CYCLE</b>.APP 😍</a></center>
			<br /><br /><br />
		</div>

		<script>
			window.addEventListener("storage", function () {
				if (parseInt(localStorage.auth)>0) {
				window.location.replace('..');
			}
			}, false);
			if (parseInt(localStorage.auth)>0) {
				window.location.replace('..');
			}
			else {
				window.localStorage.clear();
				$(document).ready(function(){
					$("#connexion_form").on("submit", function(event) {
						event.preventDefault();
						$("#connexion_but").prop("disabled", true);
						var form_data = $("#connexion_form").serializeArray();
						$.post("api/authentification", $.param(form_data)).done(function(ret){
							console.log(ret);
							$("#connexion_but").prop("disabled", false);
							ret.message += "<br />"
							if (ret.auth == true) {
								localStorage.auth = ret.no_compte;
								window.location.replace('..');
							}
							else $("#message_err").html(ret.message);
						});
					});
					const email1 = (new URLSearchParams(window.location.search)).get("email1");
					$("#i_email1").val(email1);
					$("#but_demo_bill").on("click", function(event) {
						event.preventDefault();
						$("#i_email1").val("demo.bill@moncycle.app");
						$("#i_mdp").val("demo");
						$("#connexion_form").submit();
					});
					$("#but_demo_fc").on("click", function(event) {
						event.preventDefault();
						$("#i_email1").val("demo.fc@moncycle.app");
						$("#i_mdp").val("demo");
						$("#connexion_form").submit();
					});
				});
			}
		</script>
	</body>
</html>

