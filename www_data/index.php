<!doctype html>
<!--
** moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
-->
<html>
	<head>
		<title>moncycle.app</title>
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
		<meta name="description" content="Application de suivi de cycle pour les méthodes naturelles de régulation des naissances." />
		<meta property="og:title" content="MONCYCLE.APP" />
		<meta property="og:type" content="siteweb" />
		<meta property="og:url" content="https://www.moncycle.app/" />
		<meta property="og:image" content="/img/moncycleapp_apercu.jpg" />
		<meta property="og:description" content="Application de suivi de cycle pour les méthodes naturelles de régulation des naissances." />
		<script type="text/javascript" src="vendor/components/jquery/jquery.min.js?v=<?= filemtime('vendor/components/jquery/jquery.min.js') ?>"></script> 
		<script type="text/javascript" src="vendor/chartjs/chart.js?v=<?= filemtime('vendor/chartjs/chart.js') ?>"></script> 
		<script type="text/javascript" src="js/tableau.js?v=<?= filemtime('js/tableau.js') ?>"></script>
		<link rel="stylesheet" href="css/commun.css?v=<?= filemtime('css/commun.css') ?>" />
		<link rel="stylesheet" href="css/tableau.css?v=<?= filemtime('css/tableau.css') ?>" />
	</head>
	<body>
		<center>
			<h1>mon<span class="gradiant_logo">cycle</span>.app</h1>
			<div id="nom">chargement<br /><br />&#x23F3;</div>
			<button type="button" id="charger_cycle" class="nav_button main_button">☝️ Cycle précedent</button>
			<button id="but_macro" class="nav_button main_button">&#x1F50D; Vue mini</button><button id="but_micro" style="display:none" class="nav_button">&#x1F50D; Vue maxi</button>
			<a href="compte"><button type="button" class="nav_button main_button">👨‍💻 Mon compte</button></a>
			<noscript><p class="rouge">Cette application a besoin de Javascript pour fonctionner.</p></noscript>
		</center>
		<div class="actu" id="actu" style="display:none">
			<span class="tag">actualités</span>
			<div id="actu_contenu"></div>
			<button type="button" id="fermer_actu">OK merci &#x1F44D;</button>
		</div>
		<div class="contennu macro" id="recap" style="display:none"></div>
		<div class="contennu micro" id="timeline"></div>
		<form id="jour_form" class="popup" style="display:none">
			<input type="hidden" id="form_date" name="date" value="" />
			<div id="jour_form_header">
				<button type="button" id="jour_form_prev" class="but_j_nav">J -1</button>
				<button type="button" id="jour_form_next" class="but_j_nav">J +1</button>
				<button type="button" id="jour_form_close">← retour</button>
				<button type="button" id="jour_form_submit">✏️</button>
				<span class="popup_enregistratement_status" style="display: none;" id="jour_form_saving">&nbsp;⏳&nbsp;enregistrement...</span>
				<span class="popup_enregistratement_status vert" style="display: none;" id="jour_form_saved">&nbsp;✅&nbsp;enregistré</span>
				<div id="jour_form_titre" class="bold uppercase"></div>
			</div>
			<div>
				<br />
				<div>
					<span class="categorie">Gommettes:</span><br />
					<input type="radio" name="gommette" id="go_vide" value="" checked /><label for="go_vide">pas de couleur (blanc)</label><br />
					<input type="radio" name="gommette" id="go_rouge" value="." /><label for="go_rouge">🟥 rouge <span class='note'>.</span></label><br />
					<input type="radio" name="gommette" id="go_vert" value="I" /><label for="go_vert">🟩 vert <span class='note'>I</span></label><br />
					<input type="radio" name="gommette" id="go_jaune" value="=" /><label for="go_jaune">🟨 jaune <span class='note'>=</span></label><br />
					<input type="checkbox" name="bebe" id="go_bebe" value=":)" /><label for="go_bebe">👶 bébé <span class='note'>:)</span></label><br />
					<br />
				</div>
				<div class="pas_bill pas_bill_temp">
					<span class="categorie pas_temp">Note FertilityCare:</span> <span id="fc_msg"></span><br />
					<input type="text" autocapitalize="characters" name="note_fc" id="form_fc" style="width: 95%" placeholder="note FC" /><br />	
					<br />
					<span class="categorie pas_temp">&#x1FA78; Saignement:</span><br />
					<input class="fc_form_note" type="radio" name="fc_regles" id="fc_h" value="H" /><label for="fc_h"><b>H</b> flux abondant</label><br />
					<input class="fc_form_note" type="radio" name="fc_regles" id="fc_m" value="M" /><label for="fc_m"><b>M</b> flux modéré</label><br />
					<input class="fc_form_note" type="radio" name="fc_regles" id="fc_rl" value="L" /><label for="fc_rl"><b>L</b> flux léger</label><br />
					<input class="fc_form_note" type="radio" name="fc_regles" id="fc_vl" value="VL" /><label for="fc_vl"><b>VL</b> flux vraiment léger</label><br />
					<input class="fc_form_note" type="radio" name="fc_regles" id="fc_vh" value="VH" /><label for="fc_vh"><b>VH</b> flux vraiment abondant</label><br />
					<input class="fc_form_note" type="radio" name="fc_regles" id="fc_nr" value="" checked /><label for="fc_nr">pas de flux</label><br />
					<input class="fc_form_note" type="checkbox" name="fc_regles_b" id="fc_b" value="B" /><label for="fc_b"><b>B</b> saignement brun (ou noir)</label><br />
					<br />
					<span class="categorie pas_temp">&#x1F441;&#xFE0F; Sensation & observation:</span><br />
					<input class="fc_form_note" type="radio" name="fc_sens" id="fc_0" value="0" /><label for="fc_0"><b>0</b> sec</label><br />
					<input class="fc_form_note" type="radio" name="fc_sens" id="fc_2" value="2" /><label for="fc_2"><b>2</b> humide sans lubrification</label><br />
					<input class="fc_form_note" type="radio" name="fc_sens" id="fc_2w" value="2W" /><label for="fc_2w"><b>2W</b> mouillé sans lubrification</label><br />
					<input class="fc_form_note" type="radio" name="fc_sens" id="fc_4" value="4" /><label for="fc_4"><b>4</b> brillant sans lubrification</label><br />
					<input class="fc_form_note" type="radio" name="fc_sens" id="fc_6" value="6" /><label for="fc_6"><b>6</b> peu élastique (0,5cm)</label><br />
					<input class="fc_form_note" type="radio" name="fc_sens" id="fc_8" value="8" /><label for="fc_8"><b>8</b> moyennement élastique (1-2cm)</label><br />
					<input class="fc_form_note" type="radio" name="fc_sens" id="fc_10" value="10" /><label for="fc_10"><b>10</b> très élastique (2,5cm ou +)</label><br />
					<input class="fc_form_note" type="radio" name="fc_sens" id="fc_10dl" value="10DL" /><label for="fc_10dl"><b>10DL</b> humide avec lubrification</label><br />
					<input class="fc_form_note" type="radio" name="fc_sens" id="fc_10sl" value="10SL" /><label for="fc_10sl"><b>10SL</b> brillant avec lubrification</label><br />
					<input class="fc_form_note" type="radio" name="fc_sens" id="fc_10wl" value="10WL" /><label for="fc_10wl"><b>10WL</b> mouillé avec lubrification</label><br />
					<input class="fc_form_note" type="radio" name="fc_sens" id="fc_sr" value="" checked /><label for="fc_sr">pas de sensation</label><br />
					<br />
					<span class="categorie pas_temp">&#x1F90F; Test au doigt:</span><br />
					<input class="fc_obs fc_form_note" type="checkbox" name="fc_c" id="fc_c" value="C" /><label for="fc_c"><b>C</b> opaque (blanc)</label><br />
					<input class="fc_obs fc_form_note" type="checkbox" name="fc_g" id="fc_g" value="G" /><label for="fc_g"><b>G</b> gommeux (collant)</label><br />
					<input class="fc_obs fc_form_note" type="checkbox" name="fc_k" id="fc_k" value="K" /><label for="fc_k"><b>K</b> transparent</label><br />
					<input class="fc_obs fc_form_note" type="checkbox" name="fc_l" id="fc_l" value="L" /><label for="fc_l"><b>L</b> lubrifiant</label><br />
					<input class="fc_obs fc_form_note" type="checkbox" name="fc_p" id="fc_p" value="P" /><label for="fc_p"><b>P</b> pâteux (crémeux)</label><br />
					<input class="fc_obs fc_form_note" type="checkbox" name="fc_y" id="fc_y" value="Y" /><label for="fc_y"><b>Y</b> jaune (même jaune pâle)</label><br />
					<input class="fc_obs fc_form_note" type="checkbox" name="fc_r" id="fc_r" value="R" /><label for="fc_r"><b>R</b> rouge</label><br />
					<br />
					<span class="categorie pas_temp">&#x1F522; Récurrence de l'observation:</span><br />
					<input class="fc_form_note" type="radio" name="fc_rec" id="fc_x1" value="X1" /><label for="fc_x1"><b>X1</b> vu seulement une fois ce jour</label><br />
					<input class="fc_form_note" type="radio" name="fc_rec" id="fc_x2" value="X2" /><label for="fc_x2"><b>X2</b> vu deux fois ce jour</label><br />
					<input class="fc_form_note" type="radio" name="fc_rec" id="fc_x3" value="X3" /><label for="fc_x3"><b>X3</b> vu trois fois ce jour</label><br />
					<input class="fc_form_note" type="radio" name="fc_rec" id="fc_ad" value="AD" /><label for="fc_ad"><b>AD</b> vu toute la journée</label><br />
					<input class="fc_form_note" type="radio" name="fc_rec" id="fc_rr" value="" checked /><label for="fc_rr">pas de récurrence</label><br />
					<br />
					<span class="categorie pas_temp">&#x1F974; Douleur:</span><br />
					<input class="fc_form_note" type="radio" name="fc_dou" id="fc_ap" value="AP" /><label for="fc_ap"><b>AP</b> douleur abdominal</label><br />
					<input class="fc_form_note" type="radio" name="fc_dou" id="fc_rap" value="RAP" /><label for="fc_rap"><b>RAP</b> douleur abdominal à droite</label><br />
					<input class="fc_form_note" type="radio" name="fc_dou" id="fc_lap" value="LAP" /><label for="fc_lap"><b>LAP</b> douleur abdominal à gauche</label><br />
					<input class="fc_form_note" type="radio" name="fc_dou" id="fc_rp" value="" checked /><label for="fc_rp">pas de douleur</label><br />
					<br />
					<span class="categorie">Flèche:</span><br />
					<input class="fc_form_note" type="radio" name="fc_fle" id="fc_fh" value="↑" /><label for="fc_fh">&#x2B06;&#xFE0F;</label>
					<input class="fc_form_note" type="radio" name="fc_fle" id="fc_fb" value="↓" /><label for="fc_fb">&#x2B07;&#xFE0F;</label>
					<input class="fc_form_note" type="radio" name="fc_fle" id="fc_fd" value="→" /><label for="fc_fd">&#x27A1;&#xFE0F;</label>
					<input class="fc_form_note" type="radio" name="fc_fle" id="fc_fr" value="" checked /><label class="" for="fc_fr">aucune</label><br />
					<br />
				</div>
				<div class="pas_bill pas_fc">
					<span class="categorie">Température:</span><br />
					<input type="number" step="0.1" min="0" max="99" name="temp" id="form_temp" style="width: 100px;" placeholder="ex: 36,5" /><span> °C 🌡️</span><br/>
					<span>température prise à </span><input type="time" name="heure_temp" id="form_heure_temp" /><br/>
					<br />
				</div>
				<div class="pas_fc pas_fc_temp">
					<span class="categorie">Vos sensations et visuels:</span><br />
					<span id="vos_obs"></span>
					<input type="text" name="ob_extra" id="ob_extra" autocapitalize="off" style="width: 95%" placeholder="autres sensations/visuels (séparées par une virgule)"/><br />
					<p class="pas_fc note">Séparez vos sensations/visuels par des virgules afin qu'ils vous soient proposés ultérieurement.</p>
					<br />
				</div>
				<div>
					<span class="categorie">Evénements:</span><br />
					<input type="checkbox" name="premier_jour" id="ev_premier_jour" class="ev_reload" value="1" /><label for="ev_premier_jour">📆 nouveau cycle à cette date</label><br />
					<input type="checkbox" name="union_sex" id="ev_union" value="1" /><label for="ev_union">❤️ union</label><br />
					<input type="checkbox" name="jour_sommet" id="ev_jour_sommet" value="1" /><label for="ev_jour_sommet">🏔️ <span class="pas_fc pas_fc_temp">jour sommet</span><span class="pas_bill pas_bill_temp">pic</span></label><br />
					<input type="checkbox" id="ev_compteur_actif" value="1" class="pas_fc pas_fc_temp"/><label for="ev_compteur_actif" class="pas_fc pas_fc_temp">⏰ compteur de </label><input id="ev_compteur_nb" type="number" value="3" class="pas_fc pas_fc_temp" /><label for="compteur_actif" class="pas_fc pas_fc_temp"> jours</label><br class="pas_fc pas_fc_temp" />
					<input type="hidden" id="ev_hidden_compteur" name="compteur" value="0" />
					<input type="checkbox" name="jenesaispas" id="ev_jesaispas" value="1" /><label for="ev_jesaispas">🤷‍♀️ jour non observé <span class='note'>?</span></label><br />
					<input type="checkbox" name="grossesse" id="ev_grossesse" class="ev_reload" value="1" /><label for="ev_grossesse">&#x1F930; grossesse</label><br />
					<br />
					<span class="categorie">Commentaire:</span><br />
					<textarea style="width: 95%" name="commentaire" id="from_com" autocapitalize="off" maxlength="255"></textarea><br />
					<br />
				</div>
				<span id="jour_form_suppr_holder" class="rouge">
					<button id="jour_form_suppr" type="button" class="rouge">🗑️ Supprimer l'observation</button>
				</span>
				<div id="form_err" class="err"></div>
				<br />
				<br />
			</div>
		</form>
		<script type="text/javascript">
			window.onload = moncycle_app.letsgo;
		</script>
	</body>

</html>

