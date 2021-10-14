<?php

session_start();
$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);

if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
	header('Location: session.php');
	exit;
}

?><!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
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
		<script src="jquery.min.js"></script> 
		<style type="text/css">
			html, body, h2, #jour_form_titre {
				margin: 0;
				padding: 0;
				font-family: Sans-serif;
				background-color: white;
				color: black;
			}
			#jour_form {
				background-color: white;
				border: 1px solid black;
			}
			@media (prefers-color-scheme: dark) {
				html, body, h2, #jour_form_titre {
					background-color: black;
					color: white;
				}
				#jour_form {
					background-color: black;
					border-color: white;
				}
			}
			.day {
				padding: 1px;
				cursor: pointer;
			}
			
			#timeline {
				padding-bottom: 60px;
				max-width: 550px;
				margin: 0 auto;
			}

			.day span {
				display: inline-block;
				vertical-align: sub;
				margin-right: 3px;
			}

			.day .d {
				font-family: monospace;
				height: 26px;
				line-height: 26px;
				text-align: center;
				font-size: .7em;
				float: right;
				text-transform: uppercase;
			}

			.day .j {
				color: gray;
				font-family: monospace;
				text-align: center;
				width: 20px;
				height: 26px;
				line-height: 26px;
			}

			.day .g {
				width: 25px;
				height: 25px;
				line-height: 25px;
				padding-top: 0px;
				text-align: center;
				border: 1px solid black;
			}
			@media (prefers-color-scheme: dark) {
				.day .g {
					border-color: white;
				}
			}
			.day .rouge {
				background-color: #ac2433;
				border-color: #ac2433;
				color: #ac2433;
			}

			.day .vert {
				background-color: #345c3c;
				border-color: #345c3c;
				color: #345c3c;
			}

			.day .jaune {
				background-color: #fbca0b;
				border-color: #fbca0b;
				color: #fbca0b;
			}

			.day .bebe {
				background-color: white;
				border-color: black;
			}

			.day .jesaispas {
				border-style: dashed;
			}

			.day .aremplir {
				border: none;
				background: linear-gradient(45deg, white 25%, black 25%, black 50%, white 50%, white 75%, black 75%);
			}

			.day .u {

			}
			.day .s {
				font-family: monospace;
			}	
			.day .o {

			}	
			.day .c {
				font-size: .7em;
				font-style: italic;
			}

			.petit {
				font-size: .8em;
			}
			.bold {
				font-weight: bold;
			}

			.uppercase {
				text-transform: uppercase;
			}

			#jour_form {
				display: block;
				position: fixed;
				top: 50px;
				bottom: 50px;
				left: 25px;
				right: 25px;
				overflow-y: scroll;
				padding: 10px;
				padding-top: 0;
			}

			#jour_form_titre {
				position: sticky;
				padding: 10px 0 5px 0;
				top: 0;
			}

			.categorie {
				font-size: 1.1em;
			}

			.err {
				text-color: red;
			}

			h2 {
				position: sticky;
				top: 0px;
				margin: 0;
				margin-top: 30px;
				padding: 5px 10px;
				font-size: 1.1em;
				font-weight: bold;
			}

			h2 a {
				float: right;
				display: block;
				margin-top: 5px;
				background-color: black;
				color: white;
				font-weight: normal;
				font-size: .5em;
				padding: 2px;
				border-radius: 2px;
				text-decoration: none;
			}

			@media (prefers-color-scheme: dark) {
				h2 a {
					background-color: white;
					color: black;
				}
			}

			#charger_cycle {
				margin-top: 20px;
			}

		</style>
	</head>

	<body>
		<center><button type="button" id="charger_cycle">charger le cycle précedent</button></center>
		<div id="timeline"></div>
		<form id="jour_form" style="display:none">
			<input type="hidden" id="form_date" name="date" value="" />
			<div id="jour_form_titre" class="bold uppercase"></div>
			<div>
				<br />
				<span class="categorie">Gommettes:</span><br />
				<input type="radio" name="gommette" id="go_rouge" value="." /><label for="go_rouge">🟥 menstruation (•)</label><br />
				<input type="radio" name="gommette" id="go_vert" value="I" /><label for="go_vert">🟩 jour infécond (I)</label><br />
				<input type="radio" name="gommette" id="go_jaune" value="=" /><label for="go_jaune">🟨 sécrétion qui ne change pas (=)</label><br />
				<input type="radio" name="gommette" id="go_bebe" value=":)" /><label for="go_bebe">👶 jour fécond</label><br />
				<input type="radio" name="gommette" id="go_jesaispas" value="?" /><label for="go_jesaispas">❔ jour non observé</label><br />
				<br />
				<span class="categorie">Observasions:</span><br />
				<input type="checkbox" name="ob_1" id="ob_sec" value="sec" /><label for="ob_sec">sec</label><br />
				<input type="checkbox" name="ob_2" id="ob_humide" value="humide" /><label for="ob_humide">humide</label><br />
				<input type="checkbox" name="ob_3" id="ob_elastique" value="elastique" /><label for="ob_elastique">elastique</label><br />
				<input type="checkbox" name="ob_4" id="ob_filant" value="filant" /><label for="ob_filant">filant</label><br />
				<input type="checkbox" name="ob_5" id="ob_collant" value="collant" /><label for="ob_collant">collant</label><br />
				<input type="text" name="ob_extra" id="ob_extra" style="width: 95%" placeholder="autres observations (séparées par une virgule)"/><br />
				<br />
				<span class="categorie">Evénements:</span><br />
				<input type="checkbox" name="premier_jour" id="ev_premier_jour" value="1" /><label for="ev_premier_jour">1er jour du cycle</label><br />
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
		<script>
			bill = {
				gommette : {
					"."  : ["•", "rouge"],
					"I"  : ["I", "vert"],
					"="  : ["=", "jaune"],
					"?"  : ["?", "jesaispas"],
					":)" : ["👶", "bebe"],
					""   : ["👀", "aremplir"]
				},
				text : {
					a_renseigner : "à renseigner",
					a_aujourdhui : "à auj.",
					union : "❤️",
					sommet : "⛰",
					mois : ["janv.", "févr.", "mars", "avr.", "mai", "juin", "juil.", "aout", "sept.", "oct.", "nov.", "déc."],
					mois_long : ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
					semaine : ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
				},
				letsgo : function() {
					console.log("Bill - cahier à gommettes pour la méthode Billings.");
					bill.timeline();
					$("#jour_form_close").click(function () {
						$("#jour_form").hide();
					});
					$("#jour_form_submit").click(bill.submit_menu);	
					$("#charger_cycle").click(bill.timeline);
				},
				cycle : [],
				sommets : [],
				first_load : true,
				timeline_cursor : new Date(),
				timeline() {
					bill.load(bill.timeline_cursor);
					bill.timeline_cursor.setDate(bill.timeline_cursor.getDate()-1);
				},
				build_cycle : function (date_cycle) {
					let nb_jours = (bill.timeline_cursor - date_cycle)/(1000*60*60*24);
					for (let pas = 0; pas < nb_jours; pas++) {
						bill.load(bill.timeline_cursor);
						bill.timeline_cursor.setDate(bill.timeline_cursor.getDate()-1);
					}
				},
				load : function(d) {
					$.get("observation.php", { date: d.toISOString() } ).done(function(data) {
						console.log(data);
						$("#o-" + data.date).remove();
						let o_date = new Date(data.date);
						let c_date = new Date(data.cycle);
						let nb_jours = parseInt((o_date-c_date)/(1000*60*60*24)+1);
						if (!$("#c-" + data.cycle).length) $("#timeline").prepend(bill.cycle2html(data.cycle));
						$(`#c-${data.cycle} .contenu`).append(bill.observation2html(data, nb_jours));
						if (parseInt($(`#c-${data.cycle} .nb_jours`).html()) < nb_jours) {
							$(`#c-${data.cycle} .nb_jours`).html(nb_jours);
							let a_date = new Date();
							if (o_date.getDate() == a_date.getDate() && o_date.getMonth() == a_date.getMonth() && o_date.getFullYear() == a_date.getFullYear()) {
								$(`#c-${data.cycle} .cycle_fin`).html(bill.text.a_aujourdhui + " ");
							}
							else $(`#c-${data.cycle} .cycle_fin`).html(`au ${o_date.getDate()} ${bill.text.mois[o_date.getMonth()]} `);
						}
						if (data.jour_sommet && !bill.sommets.includes(data.date)) bill.sommets.push(data.date);
						else if (!data.jour_sommet && bill.sommets.includes(data.date)) bill.sommets.splice(bill.sommets.indexOf(data.date),1);
						bill.trois_jours();
						bill.tri(data.cycle);
						if (!bill.cycle.includes(data.cycle)) {
							bill.cycle.push(data.cycle);
							bill.build_cycle(c_date);			
						}
					});
				},
				tri : function(c) {
					let a_trier = $(`#c-${c} .contenu`).children();
					a_trier.sort(function (a, b){
						return (new Date(a.id.substring(2)) > new Date(b.id.substring(2)));
					});
					$(`#c-${c} .contenu`).html(a_trier);
					$(`#c-${c} .contenu .day`).click(bill.open_menu);
				},
				trois_jours : function() {
					$(".day .s").empty();				
					$(".day .s").removeClass("petit");				
					bill.sommets.forEach(s => {
						$(`#o-${s} .s`).html(bill.text.sommet);
						let s_date = new Date(s);
						for (let n=1; n<=3; n+=1) {
							s_date.setDate(s_date.getDate()+1);		
							let s_id = s_date.getFullYear() + "-";
							s_id += (s_date.getMonth()+1).toLocaleString('fr-FR', {minimumIntegerDigits: 2, useGrouping:false}) + "-";
							s_id += s_date.getDate().toLocaleString('fr-FR', {minimumIntegerDigits: 2, useGrouping:false});
							$(`#o-${s_id} .s`).html(`${bill.text.sommet}+${n}`);
							$(`#o-${s_id} .s`).addClass("petit");
						}
					});
				},
				cycle2html : function (c) {
					let c_id = "c-" + c;
					let cycle = $("<div>", {id: c_id, class: "cycle"});
					let c_date = new Date(c);
					cycle.append(`<h2 class='titre'><a href='export.php?cycle=${c_date.toISOString()}&type=csv'>CSV</a>Cycle de <span class='nb_jours'>1</span>j du ${c_date.getDate()} ${bill.text.mois[c_date.getMonth()]} <span class='cycle_fin'></span></h2>`);
					cycle.append("<div class='contenu'></div>");
					return cycle;
				},
				observation2html : function(j, nb_j) {
					let o_id = "o-" + j.date;
					let observation = $("<div>", {id: o_id, class: "day"});
					let o_date = new Date(j.date);
					observation.append(`<span class='d'>${o_date.getDate()} ${bill.text.mois[o_date.getMonth()]} </span>`);	
					observation.append(`<span class='j'>${nb_j}</span>`);
					let gommette = j.gommette ?? "";
					observation.append(`<span class='g ${bill.gommette[gommette][1]}'>${bill.gommette[gommette][0]}</span>`);
					observation.append(`<span class='s'>${j.jour_sommet ? bill.text.sommet : ""}</span>`);
					observation.append(`<span class='u'>${j.union_sex ? bill.text.union : ""}</span>`);
					if (gommette == "") j.sensation = bill.text.a_renseigner;
					observation.append(`<span class='o'>${j.sensation ?? ""}</span>`);
					observation.append(`<span class='c'>${j.commentaire ?? ""}</span>`);
					observation.append(`<span class='data' style='display:none'>${JSON.stringify(j)}</span>`);
					observation.click(bill.open_menu);
					return observation;
				},
				open_menu : function(e) {
					let j = JSON.parse($("#" + $(this).attr('id') + " .data").text());
					let o_date = new Date(j.date);
					let gommette = j.gommette? j.gommette : "";
					$("#jour_form_titre").text([bill.text.semaine[o_date.getDay()], o_date.getDate(), bill.text.mois_long[o_date.getMonth()], o_date.getFullYear()].join(" "));
					$("#jour_form")[0].reset();
					$("#form_date").val(j.date);
					$("#go_" + bill.gommette[gommette][1]).prop('checked', true);
					let extra = []
					if (j.sensation) j.sensation.split(',').forEach(ob => {
						if (ob == bill.text.a_renseigner) return;
						let obj = $("#ob_" + ob.replace(/\s/g, ''));
						if(obj.length) obj.prop('checked', true);
						else extra.push(ob);
					});
					if (extra.length) $("#ob_extra").val(extra.join(", "));
					if (j.premier_jour) $("#ev_premier_jour").prop('checked', true);
					if (j.union_sex) $("#ev_union").prop('checked', true);
					if (j.jour_sommet) $("#ev_jour_sommet").prop('checked', true);
					$("#from_com").val(j.commentaire);
					$("#jour_form").show();
				},
				submit_menu : function () {
					let d = $("#jour_form").serializeArray();
					$.post("observation.php", $.param(d)).done(function(data){
						console.log(data);
						if (data.err){
							$("#form_err").val(data.err);
							console.error(data.err);
						}
						if (data.outcome == "ok") {
							bill.load(new Date(data.date));
							$("#jour_form").hide();
						}		
					}).fail(function (ret) {
						console.error(ret.responseText); 
						$("#form_err").val(ret.responseText);
					});
				}
			}
			window.onload = bill.letsgo;
		</script>
	</body>

</html>
