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
$grossesses = db_select_grossesses($db, $_SESSION["no"]);
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

$methode = [1 => "temp", 2 => "glaire", 3 => "fc"];

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
			var toutes_les_grossesses = <?= json_encode($grossesses); ?>;
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
				<br />
				<div class="pas_glaire pas_temp">
					<span class="categorie pas_temp">Note FertilityCare:</span> <span id="fc_msg"></span><br class="pas_temp" />
					<input class="pas_glaire" type="text" autocapitalize="characters" name="note_fc" id="form_fc" style="width: 95%" placeholder="note FC" /><br class="pas_temp" />	
					<br />
					<span class="categorie pas_temp">&#x1FA78; Saignement:</span><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_regles" id="fc_h" value="H" /><label class="pas_temp" for="fc_h"><b>H</b> flux abondant</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_regles" id="fc_m" value="M" /><label class="pas_temp" for="fc_m"><b>M</b> flux modéré</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_regles" id="fc_rl" value="L" /><label class="pas_temp" for="fc_rl"><b>L</b> flux léger</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_regles" id="fc_vl" value="VL" /><label class="pas_temp" for="fc_vl"><b>VL</b> flux vraiment léger</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_regles" id="fc_b" value="B" /><label class="pas_temp" for="fc_b"><b>B</b> saignement brun (ou noir)</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_regles" id="fc_nr" value="" checked /><label class="pas_temp" for="fc_nr">pas de règle</label><br class="pas_temp" />
					<br />
					<span class="categorie pas_temp">&#x1F441;&#xFE0F; Sensation & observation:</span><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_sens" id="fc_0" value="0" /><label class="pas_temp" for="fc_0"><b>0</b> sec</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_sens" id="fc_2" value="2" /><label class="pas_temp" for="fc_2"><b>2</b> humide sans lubrification</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_sens" id="fc_2w" value="2W" /><label class="pas_temp" for="fc_2w"><b>2W</b> mouillé sans lubrification</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_sens" id="fc_4" value="4" /><label class="pas_temp" for="fc_4"><b>4</b> brillant sans lubrification</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_sens" id="fc_6" value="6" /><label class="pas_temp" for="fc_6"><b>6</b> peu élastique (0,5cm)</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_sens" id="fc_8" value="8" /><label class="pas_temp" for="fc_8"><b>8</b> moyennement élastique (1-2cm)</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_sens" id="fc_10" value="10" /><label class="pas_temp" for="fc_10"><b>10</b> très élastique (2,5cm ou +)</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_sens" id="fc_10dl" value="10DL" /><label class="pas_temp" for="fc_10dl"><b>10DL</b> humide avec lubrification</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_sens" id="fc_10sl" value="10SL" /><label class="pas_temp" for="fc_10sl"><b>10SL</b> brillant avec lubrification</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_sens" id="fc_10wl" value="10WL" /><label class="pas_temp" for="fc_10wl"><b>10WL</b> mouillé avec lubrification</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_sens" id="fc_sr" value="" checked /><label class="pas_temp" for="fc_sr">pas de sensation</label><br class="pas_temp" />
					<br />
					<span class="categorie pas_temp">&#x1F90F; Test au doigt:</span><br class="pas_temp" />
					<input class="fc_obs pas_temp fc_form_note" type="checkbox" name="fc_c" id="fc_c" value="C" /><label class="pas_temp" for="fc_c"><b>C</b> opaque (blanc)</label><br class="pas_temp" />
					<input class="fc_obs pas_temp fc_form_note" type="checkbox" name="fc_g" id="fc_g" value="G" /><label class="pas_temp" for="fc_g"><b>G</b> gommeux (collant)</label><br class="pas_temp" />
					<input class="fc_obs pas_temp fc_form_note" type="checkbox" name="fc_k" id="fc_k" value="K" /><label class="pas_temp" for="fc_k"><b>K</b> transparent</label><br class="pas_temp" />
					<input class="fc_obs pas_temp fc_form_note" type="checkbox" name="fc_l" id="fc_l" value="L" /><label class="pas_temp" for="fc_l"><b>L</b> lubrifiant</label><br class="pas_temp" />
					<input class="fc_obs pas_temp fc_form_note" type="checkbox" name="fc_p" id="fc_p" value="P" /><label class="pas_temp" for="fc_p"><b>P</b> pâteux (crémeux)</label><br class="pas_temp" />
					<input class="fc_obs pas_temp fc_form_note" type="checkbox" name="fc_y" id="fc_y" value="Y" /><label class="pas_temp" for="fc_y"><b>Y</b> jaune (même jaune pâle)</label><br class="pas_temp" />
					<input class="fc_obs pas_temp fc_form_note" type="checkbox" name="fc_r" id="fc_r" value="R" /><label class="pas_temp" for="fc_r"><b>R</b> rouge</label><br class="pas_temp" />
					<br />
					<span class="categorie pas_temp">&#x1F522; Récurrence de l'observation:</span><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_rec" id="fc_x1" value="X1" /><label class="pas_temp" for="fc_x1"><b>X1</b> vu seulement une fois ce jour</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_rec" id="fc_x2" value="X2" /><label class="pas_temp" for="fc_x2"><b>X2</b> vu deux fois ce jour</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_rec" id="fc_x3" value="X3" /><label class="pas_temp" for="fc_x3"><b>X3</b> vu trois fois ce jour</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_rec" id="fc_ad" value="AD" /><label class="pas_temp" for="fc_ad"><b>AD</b> vu toute la journée</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_rec" id="fc_rr" value="" checked /><label class="pas_temp" for="fc_rr">pas de récurrence</label><br class="pas_temp" />
					<br />
					<span class="categorie pas_temp">&#x1F974; Douleur:</span><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_dou" id="fc_ap" value="AP" /><label class="pas_temp" for="fc_ap"><b>AP</b> douleur abdominal</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_dou" id="fc_rap" value="RAP" /><label class="pas_temp" for="fc_rap"><b>RAP</b> douleur abdominal à droite</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_dou" id="fc_lap" value="LAP" /><label class="pas_temp" for="fc_lap"><b>LAP</b> douleur abdominal à gauche</label><br class="pas_temp" />
					<input class="pas_temp fc_form_note" type="radio" name="fc_dou" id="fc_rp" value="" checked /><label class="pas_temp" for="fc_rp">pas de douleur</label><br class="pas_temp" />
					<br />
					<span class="categorie">Flèche:</span><br class="pas_temp" />
					<input class="fc_form_note" type="radio" name="fc_fle" id="fc_fh" value="↑" /><label for="fc_fh">&#x2B06;&#xFE0F;</label>
					<input class="fc_form_note" type="radio" name="fc_fle" id="fc_fb" value="↓" /><label for="fc_fb">&#x2B07;&#xFE0F;</label>
					<input class="fc_form_note" type="radio" name="fc_fle" id="fc_fd" value="→" /><label for="fc_fd">&#x27A1;&#xFE0F;</label>
					<input class="fc_form_note" type="radio" name="fc_fle" id="fc_fr" value="" checked /><label class="" for="fc_fr">aucune</label><br />
					<br />
				</div>
				<span class="categorie">Gommettes:</span><br />
				<input type="radio" name="gommette" id="go_vide" value="" checked /><label for="go_vide">pas de couleur (blanc)</label><br />
				<input type="radio" name="gommette" id="go_rouge" value="." /><label for="go_rouge">🟥 rouge <span class='note'>.</span></label><br />
				<input type="radio" name="gommette" id="go_vert" value="I" /><label for="go_vert">🟩 vert <span class='note'>I</span></label><br />
				<input type="radio" name="gommette" id="go_jaune" value="=" /><label for="go_jaune">🟨 jaune <span class='note'>=</span></label><br />
				<input type="checkbox" name="bebe" id="go_bebe" value=":)" /><label for="go_bebe">👶 bébé <span class='note'>:)</span></label><br />
				<br class="pas_glaire pas_fc" />
				<span class="categorie pas_fc pas_glaire">Température:</span><br class="pas_glaire" />
				<input class="pas_fc pas_glaire" type="number" step="0.1" min="0" max="99" name="temp" id="form_temp" style="width: 100px;" placeholder="ex: 36,5" /><span class="pas_glaire pas_fc"> °C 🌡️</span><br class="pas_glaire pas_fc"/>
				<span class="pas_glaire pas_fc">température prise à </span><input class="pas_fc pas_glaire" type="time" name="h_temp" id="form_h_temp" /><br class="pas_glaire pas_fc"/>
				<br class="pas_fc " />
				<span class="categorie pas_fc">Vos sensations et visuels:</span><br class="pas_fc" />
				<span class="pas_fc" id="vos_obs"></span>
				<input class="pas_fc" type="text" name="ob_extra" id="ob_extra" autocapitalize="off" style="width: 95%" placeholder="autres sensations/visuels (séparées par une virgule)"/><br class="pas_fc" />
				<p class="pas_fc note">Séparez vos sensations/visuels par des virgules afin qu'ils vous soient proposés ultérieurement.</p>
				<br class="pas_fc" />
				<span class="categorie">Evénements:</span><br />
				<input type="checkbox" name="premier_jour" id="ev_premier_jour" class="ev_reload" value="1" /><label for="ev_premier_jour">📅 nouveau cycle à cette date</label><br />
				<input type="checkbox" name="union_sex" id="ev_union" value="1" /><label for="ev_union">❤️ union</label><br />
				<input type="checkbox" name="jour_sommet" id="ev_jour_sommet" value="1" /><label for="ev_jour_sommet">⛰️ <span class="pas_fc">jour sommet</span><span class="pas_glaire pas_temp">pic</span><span class="note pas_glaire pas_fc"> point de température le plus bas</span></label><br />
				<input type="checkbox" name="jenesaispas" id="ev_jesaispas" value="1" /><label for="ev_jesaispas">🤷‍♀️ jour non observé <span class='note'>?</span></label><br />
				<input type="checkbox" name="grossesse" id="ev_grossesse" class="ev_reload" value="1" /><label for="ev_grossesse">&#x1F930; grossesse</label><br />
				<br />
				<span class="categorie">Commentaire:</span><br />
				<textarea style="width: 95%" name="commentaire" id="from_com" autocapitalize="off" maxlength="255"></textarea><br />
				<br />
				<span id="jour_form_suppr_holder" class="rouge">
					<button id="jour_form_suppr" type="button" class="rouge">🗑️ Tout supprimer</button>
				</span>
				<div id="form_err" class="err"></div>
				<br />
				<br />
			</div>
			<div id="jour_form_but">
				<button type="button" id="jour_form_submit">✏️ enregistrer</button> <button type="button" id="jour_form_close">❌ fermer</button><br />
			</div>
		</form>
		<script type="text/javascript">
			window.onload = bill.letsgo;
		</script>
	</body>

</html>

