<?php

require_once "config.php";
require_once "lib/db.php";

session_start();

if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
	header('Location: connexion');
	exit;
}


$db = db_open();

$cycles = db_select_cycles($db, $_SESSION["no"]);
$sensations_brut = db_select_sensations($db, $_SESSION["no"]);

$sensations = [];
foreach ($sensations_brut as $obj) {
	$i = explode(',', $obj["sensation"]);
	foreach ($i as $sens) {
		$sens = strtolower(trim($sens));
		if (!isset($sensations[$sens])) $sensations[$sens] = 0;
		$sensations[$sens] += $obj["nb"];
	}
} 

$methode = [1 => "les2", 2 => "glaire", 3 => "temp"];

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
		<script type="text/javascript" src="js/chart.min.js"></script> 
		<script type="text/javascript">
			var tous_les_cycles = <?= json_encode($cycles); ?>;
			var sensations = <?= json_encode($sensations); ?>;
		</script>
		<style>
			.pas_<?= $methode[$_SESSION["compte"]["methode"]] ?> {
				display: none;
			}
		</style>
		<script type="text/javascript" src="js/tableau.js"></script>
		<link rel="stylesheet" href="css/commun.css">
		<link rel="stylesheet" href="css/tableau.css">
	</head>

	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<div id="nom"><?= $_SESSION["compte"]["nom"] ?? "Mon cahier" ?></div>
			<button type="button" id="charger_cycle" class="nav_button">☝️ Cycle précedent</button> <a href="compte"><button type="button" class="nav_button">&#x1F9CD; Mon compte</button></a>
		</center>
		<div class="contennu" id="timeline"></div>
		<form id="jour_form" class="popup" style="display:none">
			<input type="hidden" id="form_date" name="date" value="" />
			<div id="jour_form_titre" class="bold uppercase"></div>
			<div>
				<button type="button" id="jour_form_submit">✏️ enregistrer</button> <button type="button" id="jour_form_close">❌ fermer</button><br />
				<br />
				<span class="categorie">Gommettes:</span><br />
				<input type="radio" name="gommette" id="go_rouge" value="." /><label for="go_rouge">🟥 menstruation <span class='note'>.</span></label><br />
				<input type="radio" name="gommette" id="go_vert" value="I" /><label for="go_vert">🟩 pas de sécrétion <span class='note'>I</span></label><br />
				<input type="radio" name="gommette" id="go_jaune" value="=" /><label for="go_jaune">🟨 sécrétion inféconde <span class='note'>=</span></label><br />
				<input type="radio" name="gommette" id="go_bebe" value=":)" /><label for="go_bebe">👶 sécrétion féconde <span class='note'>:)</span></label><br />
				<br />
				<span class="categorie">Température:</span><br />
				<input type="number" step="0.1" min="0" max="99" name="temp" id="form_temp" style="width: 100px;" placeholder="entrer votre temperature corporelle"> °C 🌡️</br>
				<br />
				<span class="categorie">Vos sensations et visuels:</span><br />
				<span id="vos_obs"></span>
				<input type="text" name="ob_extra" id="ob_extra" style="width: 95%" placeholder="autres sensations/visuels (séparées par une virgule)"/><br />
				<br />
				<span class="categorie">Evénements:</span><br />
				<input type="checkbox" name="premier_jour" id="ev_premier_jour" value="1" /><label for="ev_premier_jour">📅 nouveau cycle à cette date</label><br />
				<input type="checkbox" name="union_sex" id="ev_union" value="1" /><label for="ev_union">❤️ union</label><br />
				<input type="checkbox" name="jour_sommet" id="ev_jour_sommet" value="1" /><label for="ev_jour_sommet">⛰️ jour sommet</label><br />
				<input type="radio" name="gommette" id="go_jesaispas" value="?" /><label for="go_jesaispas">❔ jour non observé <span class='note'>?</span></span></label><br />
				<br />
				<span class="categorie">Commentaire:</span><br />
				<textarea style="width: 95%" name="commentaire" id="from_com" maxlength="255"></textarea><br />
				<br />
				<button id="jour_form_suppr" type="button" class="rouge">🗑️ Supprimer</button>
				<div id="form_err" class="err"></div>
			</div>
		</form>
		<div id="temp_graph" class="popup" style="display: none">
			<button type="button" id="temp_graph_close">❌ fermer</button>
			<div id="temp_graph_titre" class="bold uppercase"></div>
			<div id="graph_container" class="graph_container"></div>
		</div>
		<script type="text/javascript">
			window.onload = bill.letsgo;
		</script>
	</body>

</html>
