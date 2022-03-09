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
require_once "lib/date.php";

session_start();

if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
	header('Location: connexion');
	exit;
}


$db = db_open();

if (!isset($_SESSION["sess_refresh"]) || $_SESSION["sess_refresh"] != date_sql(new DateTime())) {
	$_SESSION["sess_refresh"] = date_sql(new DateTime());
	$compte = db_select_compte_par_nocompte($db, $_SESSION["no"])[0] ?? [];
	unset($compte["motdepasse"]);
	$_SESSION["compte"] = $compte;
}

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
		<script type="text/javascript" src="module/jquery.js?h=<?= hash_file("sha1", "./module/jquery.js") ?>"></script> 
		<script type="text/javascript" src="module/chart.js?h=<?= hash_file("sha1", "./module/chart.js") ?>"></script> 
		<script type="text/javascript">
			var tous_les_cycles = <?= json_encode($cycles); ?>;
			var sensations = <?= json_encode($sensations); ?>;
			const methode = <?= $_SESSION["compte"]["methode"] ?>;
		</script>
		<style>
			.pas_<?= $methode[$_SESSION["compte"]["methode"]] ?> {
				display: none !important;
			}
		</style>
		<script type="text/javascript" src="js/tableau.js?h=<?= hash_file("sha1", "./js/tableau.js") ?>"></script>
		<link rel="stylesheet" href="css/commun.css?h=<?= hash_file("sha1", "./css/commun.css") ?>">
		<link rel="stylesheet" href="css/tableau.css?h=<?= hash_file("sha1", "./css/tableau.css") ?>">
	</head>
	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<div id="nom"><?= $_SESSION["compte"]["nom"] ?? "Mon cahier" ?><?=  $_SESSION["compte"]["donateur"] ? " 🎖️" : "" ?></div>
			<button type="button" id="charger_cycle" class="nav_button">☝️ Cycle précedent</button> <a href="compte"><button type="button" class="nav_button">👨‍💻 Mon compte</button></a>
			<noscript><p class="rouge">Cette application a besoin de Javascript pour fonctionner.</p></noscript>
		</center>
		<div class="actu" id="actu" style="display:none">
			<span class="tag">actualités</span>
			<div id="actu_contennu"></div>
			<button type="button" id="fermer_actu">OK merci &#x1F44D;</button>
		</div>
		<div class="contennu" id="timeline"></div>
		<form id="jour_form" class="popup" style="display:none">
			<input type="hidden" id="form_date" name="date" value="" />
			<div id="jour_form_titre" class="bold uppercase"></div>
			<div>
				<button type="button" id="jour_form_submit">✏️ enregistrer</button> <button type="button" id="jour_form_close">❌ fermer</button><br />
				<br />
				<span class="categorie pas_temp">Gommettes:</span><br class="pas_temp" />
				<input class="pas_temp" type="radio" name="gommette" id="go_rouge" value="." /><label class="pas_temp" for="go_rouge">🟥 menstruation <span class='note'>.</span></label><br class="pas_temp" />
				<input class="pas_temp" type="radio" name="gommette" id="go_vert" value="I" /><label class="pas_temp" for="go_vert">🟩 pas de sécrétion <span class='note'>I</span></label><br class="pas_temp" />
				<input class="pas_temp" type="radio" name="gommette" id="go_jaune" value="=" /><label class="pas_temp" for="go_jaune">🟨 sécrétion inféconde <span class='note'>=</span></label><br class="pas_temp" />
				<input class="pas_temp" type="radio" name="gommette" id="go_bebe" value=":)" /><label class="pas_temp" for="go_bebe">👶 sécrétion féconde <span class='note'>:)</span></label><br class="pas_temp" />
				<br class="pas_temp pas_glaire" />
				<span class="categorie pas_glaire">Température:</span><br class="pas_glaire" />
				<input class="pas_glaire" type="number" step="0.1" min="0" max="99" name="temp" id="form_temp" style="width: 100px;" placeholder="entrer votre température corporelle"><span class="pas_glaire"> °C 🌡️</span><br />
				<br class="pas_glaire" />
				<span class="categorie pas_temp">Vos sensations et visuels:</span><br class="pas_temp" />
				<span class="pas_temp" id="vos_obs"></span>
				<input class="pas_temp" type="text" name="ob_extra" id="ob_extra" style="width: 95%" placeholder="autres sensations/visuels (séparées par une virgule)"/><br class="pas_temp" />
				<p class="pas_temp note">Séparez vos sensations/visuels par des virgules afin qu'ils vous soient proposés ultérieurement.</p>
				<br class="pas_temp" />
				<span class="categorie">Evénements:</span><br />
				<input type="checkbox" name="premier_jour" id="ev_premier_jour" value="1" /><label for="ev_premier_jour">📅 nouveau cycle à cette date</label><br />
				<input type="checkbox" name="union_sex" id="ev_union" value="1" /><label for="ev_union">❤️ union</label><br />
				<input type="checkbox" name="jour_sommet" id="ev_jour_sommet" value="1" /><label for="ev_jour_sommet">⛰️ jour sommet</label><br />
				<input type="checkbox" name="jenesaispas" id="ev_jesaispas" value="1" /><label for="ev_jesaispas">🤷‍♀️ jour non observé <span class='note'>?</span></label><br />
				<br />
				<span class="categorie">Commentaire:</span><br />
				<textarea style="width: 95%" name="commentaire" id="from_com" maxlength="255"></textarea><br />
				<br />
				<button id="jour_form_suppr" type="button" class="rouge">🗑️ Supprimer</button>
				<div id="form_err" class="err"></div>
			</div>
		</form>
		<div id="temp_graph" class="popup pas_glaire" style="display: none">
			<button type="button" id="temp_graph_close">❌ fermer</button>
			<div id="temp_graph_titre" class="bold uppercase"></div>
			<div id="graph_container" class="graph_container"></div>
		</div>
		<script type="text/javascript">
			window.onload = bill.letsgo;
		</script>
	</body>

</html>
