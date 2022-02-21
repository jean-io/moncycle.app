<?php

require_once "config.php";

session_start();

if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
	header('Location: connexion.php');
	exit;
}

$db = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_ID, DB_PASSWORD);

$sql = "SELECT date_obs AS cycles FROM observation WHERE no_compte = :no_compte AND premier_jour = 1 ORDER BY cycles DESC";

$statement = $db->prepare($sql);
$statement->bindValue(":no_compte", $_SESSION["no"], PDO::PARAM_INT);
$statement->execute();

$cycles = $statement->fetchAll(PDO::FETCH_COLUMN);
		
?><!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
		<meta name="mobile-web-app-capable" content="yes" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
		<meta name="apple-mobile-web-app-title" content="Mon Cycle" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<link rel="apple-touch-icon" href="/img/bill512.jpg" />
		<meta name="theme-color" media="(prefers-color-scheme: light)" content="white" />
		<meta name="theme-color" media="(prefers-color-scheme: dark)" content="black" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: light)" content="light-content" />
		<meta name="apple-mobile-web-app-status-bar-style" media="(prefers-color-scheme: dark)" content="dark-content" />
		<title>MONCYCLE.APP</title>
		<script type="text/javascript" src="js/jquery.min.js"></script> 
		<script type="text/javascript">
			var tous_les_cycles = <?= json_encode($cycles); ?>;
		</script>
		<script type="text/javascript" src="js/tableau.js"></script>
		<link rel="stylesheet" href="css/commun.css">
		<link rel="stylesheet" href="css/cahier.css">
	</head>

	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<div id="nom"><?= $_SESSION["compte"]["nom"] ?? "Mon cahier" ?></div>
			<button type="button" id="charger_cycle" class="nav_button">Cycle précedent</button> <a href="compte.php"><button type="button" class="nav_button">Mon compte</button></a>
		</center>
		<div class="contennu" id="timeline"></div>
		<form id="jour_form" style="display:none">
			<input type="hidden" id="form_date" name="date" value="" />
			<div id="jour_form_titre" class="bold uppercase"></div>
			<div>
				<br />
				<span class="categorie">Gommettes:</span><br />
				<input type="radio" name="gommette" id="go_rouge" value="." /><label for="go_rouge">🟥 menstruation <span class='note'>.</span><label><br />
				<input type="radio" name="gommette" id="go_vert" value="I" /><label for="go_vert">🟩 absence de sécrétion <span class='note'>I</span></label><br />
				<input type="radio" name="gommette" id="go_jaune" value="=" /><label for="go_jaune">🟨 sécrétion inféconde <span class='note'>=</span></label><br />
				<input type="radio" name="gommette" id="go_bebe" value=":)" /><label for="go_bebe">👶 sécrétion féconde <span class='note'>:)</span></label><br />
				<input type="radio" name="gommette" id="go_jesaispas" value="?" /><label for="go_jesaispas">❔ jour non observé <span class='note'>?</span></span></label><br />
				<br />
				<span class="categorie">Sensations et visuels:</span><br />
				<input type="checkbox" name="ob_1" id="ob_sec" value="sec" /><label for="ob_sec">sec</label><br />
				<input type="checkbox" name="ob_2" id="ob_humide" value="humide" /><label for="ob_humide">humide</label><br />
				<input type="checkbox" name="ob_3" id="ob_elastique" value="elastique" /><label for="ob_elastique">elastique</label><br />
				<input type="checkbox" name="ob_4" id="ob_filant" value="filant" /><label for="ob_filant">filant</label><br />
				<input type="checkbox" name="ob_5" id="ob_collant" value="collant" /><label for="ob_collant">collant</label><br />
				<input type="text" name="ob_extra" id="ob_extra" style="width: 95%" placeholder="autres sensations/visuels (séparées par une virgule)"/><br />
				<br />
				<span class="categorie">Evénements:</span><br />
				<input type="checkbox" name="premier_jour" id="ev_premier_jour" value="1" /><label for="ev_premier_jour">📅 nouveau cycle à cette date</label><br />
				<input type="checkbox" name="union_sex" id="ev_union" value="1" /><label for="ev_union">❤️ union</label><br />
				<input type="checkbox" name="jour_sommet" id="ev_jour_sommet" value="1" /><label for="ev_jour_sommet">⛰️ jour sommet</label><br />
				<br />
				<span class="categorie">Commentaire:</span><br />
				<textarea style="width: 95%" name="commentaire" id="from_com"></textarea><br />
				<br />
				<button type="button" id="jour_form_submit">✔️</button> <button type="button" id="jour_form_close">❌</button> 
				<div id="form_err" class="err"></div>
			</div>
		</form>
		<script type="text/javascript">
			window.onload = bill.letsgo;
		</script>
	</body>

</html>
