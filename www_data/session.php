<?php

require_once "password.php";

session_start();
$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);


$login = false;

if (isset($_GET["deconnexion"])) {
	$_SESSION["connected"] = false;
	session_destroy();
	header('Location: /');
	exit;
}
elseif (isset($_SESSION["connected"]) && $_SESSION["connected"]) {
	$login = true;
}
elseif (isset($_POST["pass"]) && hash("sha256", $_POST["pass"]) == LOGIN_PASSWORD) {
	$login = true;
	$_SESSION["connected"] = true;
}
elseif (isset($_POST["pass"])) {
	sleep(3);
	$err = "Mot de passe erroné.";
}


if ($login) {

	header('Location: /');
	exit;

}

?><!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="mobile-web-app-capable" content="yes" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
	<meta name="apple-mobile-web-app-title" content="Bill" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<link rel="apple-touch-icon" href="/img/bill512.jpg" />
	<meta name="theme-color" media="(prefers-color-scheme: light)" content="white" />
	<meta name="theme-color" media="(prefers-color-scheme: dark)" content="black" />
	<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: light)" content="light-content" />
	<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: dark)" content="dark-content" />
	<title>Bill</title>
	<style type="text/css">
		html, body {
			margin: 0;
			padding: 0;
			font-family: Sans-serif;
			background-color: white;
			color: black;
		}
		@media (prefers-color-scheme: dark) {
			html, body {
				background-color: black;
				color: white;
			}
		}
		body {
			padding-top: 100px;
		}
		img {
			border-radius: 3px;
		}
		.err {
			color: red;
			color: #ac2433;
			font-size: .8em;
		}
	</style>
</head>
<body>
	<form action="" method="post">
		<center>
			<img src="/img/bill512.jpg" height="64px" /><br />
			<b>Bill</b> - cahier à gommettes pour la méthode Billings.<br /><br />
			<input type="password" name="pass" placeholder="mot de passe" /><br />
			<button>Connexion</button><br /><br />
			<span class='err'><?= $err ?? "" ?></span>
		</center>
	</form>
</body>
</html>
