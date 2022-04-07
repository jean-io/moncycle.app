/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

bill = {
	gommette : {
		"."  : [".", "rouge"],
		"I"  : ["I", "vert"],
		"="  : ["=", "jaune"],
		":)" : ["👶", "bebe"],
	},
	text : {
		je_sais_pas: "❔ jour non observé",
		a_renseigner : "👋 à renseigner",
		chargement : "⏳ chargement",
		a_aujourdhui : "à auj.",
		union : "❤️",
		sommet : "⛰️",
		mois : ["jan", "fév", "mars", "avr", "mai", "juin", "juil", "août", "sep", "oct", "nov", "déc"],
		mois_long : ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
		semaine : ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
	},
	sommets : [],
	page_a_recharger: false,
	graph_data : {},
	cycle_curseur : 0,
	a_le_focus: true,
	date_chargement: null,
	letsgo : function() {
		bill.date_chargement = bill.date.str(bill.date.now());
		console.log("moncycle.app - app de suivi de cycle pour les méthodes naturelles");
		bill.charger_cycle();
		$("#charger_cycle").click(bill.charger_cycle);
		$("#jour_form_close").click(bill.close_menu);
		$("#temp_graph_close").click(function () {
			$("#temp_graph").hide();
		});
		$("#jour_form_submit").click(bill.submit_menu);	
		$("#jour_form_suppr").click(bill.suppr_observation);	
		$("#form_fc").keyup(bill.fc_test_note).change(bill.fc_test_note);
		$('input.fc_form_note').change(bill.fc_form2note);
		bill.charger_actu();
		$(window).focus(function() {
			if (bill.date.str(bill.date.now()) != bill.date_chargement) location.reload(false); 
		})
	},
	charger_actu : function() {
		$.get("https://www.moncycle.app/actu.html", function(data) {
			let html = $.parseHTML(data);
			$("#actu_contennu").html(html);
			let titre = $("#actu_contennu").find("h4").text();
			if (titre && localStorage.actu_lu != titre) $("#actu").show();
			$("#fermer_actu").click(function () {
				localStorage.actu_lu = $("#actu_contennu").find("h4").text();
				$("#actu").hide();
			});
		});	
	},
	charger_cycle : function() {
		if (bill.cycle_curseur >= tous_les_cycles.length) {
			bill.form_nouveau_cycle();
			return;
		}
		let c = bill.cycle_curseur;
		bill.cycle_curseur += 1;
		let date_cycle_str = tous_les_cycles[c];
		let date_fin = bill.date.now();
		if (c>0) {
			date_fin = new Date(bill.date.parse(tous_les_cycles[c-1]) - (1000*60*60*24));
			date_fin.setHours(9);
		}
		let date_cycle = bill.date.parse(date_cycle_str);
		date_cycle.setHours(9);
		let nb_jours = parseInt(Math.round((date_fin-date_cycle)/(1000*60*60*24)+1));
		$("#timeline").prepend(bill.cycle2html(date_cycle_str, nb_jours, date_fin));
		if (JSON.parse(localStorage.cycle_cache || "[]").includes("contenu-c-" + date_cycle_str)) bill.cycle_aff_switch("contenu-c-" + date_cycle_str);
		$(`#c-${date_cycle_str} .aff_masquer_cycle`).click(function (e) {
			bill.cycle_aff_switch($(this).attr("for"));
		});
		$(`#c-${date_cycle_str} .aff_temp_graph`).click(bill.open_temp_graph);
		for (let pas = 0; pas < nb_jours; pas++) {
			let date_obs = new Date(date_cycle);
			date_obs.setDate(date_obs.getDate()+pas);
			date_obs_str = bill.date.str(date_obs);
			let data = {date: date_obs_str, pos: pas+1, chargement: true, temperature: NaN, cycle: date_cycle_str};
			bill.graph_preparation_data(data);
			$(`#c-${date_cycle_str} .contenu`).append(bill.observation2html(data));
			bill.charger_observation(date_obs_str);
		}
	},
	charger_observation : function(o_date) {
		$.get("observation.php", { date: o_date }).done(function(data) {
			$(`#o-${data.date}`).replaceWith(bill.observation2html(data));
			if (data.jour_sommet && !bill.sommets.includes(data.date)) bill.sommets.push(data.date);
			else if (!data.jour_sommet && bill.sommets.includes(data.date)) bill.sommets.splice(bill.sommets.indexOf(data.date),1);
			bill.trois_jours();
			bill.graph_preparation_data(data);
		});
	},
	form_nouveau_cycle: function () {
		let max_date = bill.date.str(bill.date.now());
		if (bill.cycle_curseur>0) {
			max_date = tous_les_cycles[bill.cycle_curseur-1];
			max_date = bill.date.str(new Date(bill.date.parse(max_date) - (1000*60*60*24)));
		}
		let html = `<div class="cycle" id="nouveau_cycle"><h2 class="titre">Créer un nouveau cycle</h2><div class="nouveau_cycle_form">Entrer la date du premier jour du cycle à créer.<br><input id="nouveau_cycle_date" type="date" value="${max_date}" max="${max_date}" /> <input type="button" id="but_creer_cycle" value="✔️" /></div></div>`;
		$("#charger_cycle").prop("disabled", true);
		$("#timeline").prepend(html);
		$("#but_creer_cycle").click(function () {
			let nouveau_cycle_date = $("#nouveau_cycle_date").val();
			if (bill.date.parse(nouveau_cycle_date) > bill.date.parse($("#nouveau_cycle_date").attr("max"))) {
				alert("Erreur: la date du premier jour du cycle à créer doit être antérieur aux cycles déja existant et antérieur à auhjourd'hui.");
				return;
			}
			$.post("observation.php", `date=${nouveau_cycle_date}&premier_jour=1`).done(function(data){
				if (data.err){
					console.error(data.err);
				}
				if (data.outcome == "ok") {
					tous_les_cycles.push(nouveau_cycle_date);
					$("#charger_cycle").prop("disabled", false);
					$("#nouveau_cycle").remove();
					bill.charger_cycle();
				}		
			}).fail(function (ret) {
				console.error(ret.responseText); 
			});
		});
	},
	trois_jours : function() {
		$(".day .s").empty();
		$(".day .s").removeClass("petit");				
		bill.sommets.forEach(s => {
			$(`#o-${s} .s`).html(bill.text.sommet);
			let nb_j_sommet = [1, 2, 3, $(`#o-${s}`).parent()[0].children.length - $(`#o-${s}`).index() - 1];
			nb_j_sommet.forEach(n => {
				let s_date = bill.date.parse(s);
				s_date.setDate(s_date.getDate()+n);		
				let s_id = s_date.getFullYear() + "-";
				s_id += (s_date.getMonth()+1).toLocaleString('fr-FR', {minimumIntegerDigits: 2, useGrouping:false}) + "-";
				s_id += s_date.getDate().toLocaleString('fr-FR', {minimumIntegerDigits: 2, useGrouping:false});
				$(`#o-${s_id} .s`).html(`${bill.text.sommet}+${n}`);
				$(`#o-${s_id} .s`).addClass("petit");
			});
		});
	},
	cycle_aff_switch: function (id) {
		let cache = JSON.parse(localStorage.cycle_cache || "[]");
		if ($("#" + id).is(":hidden")) {
			$("#" + id).show();
			$("#but-" + id).html("&#x1F440; Masquer");
			if (cache.includes(id)) cache.splice(cache.indexOf(id) , 1);
		}
		else {
			$("#" + id).hide();
			$("#but-" + id).html("&#x1F440; Afficher");
			if (!cache.includes(id)) cache.push(id);
		}
		localStorage.cycle_cache = JSON.stringify(cache);
	},
	cycle2html : function (c, nb, fin) {
		let c_id = "c-" + c;
		let cycle = $("<div>", {id: c_id, class: "cycle"});
		let c_date = bill.date.parse(c);
		let c_fin = new Date(fin);
		let c_fin_text = `au ${c_fin.getDate()} ${bill.text.mois[c_fin.getMonth()]} `;
		cycle.append(`<h2 class='titre'>Cycle du ${c_date.getDate()} ${bill.text.mois[c_date.getMonth()]} <span class='cycle_fin'>${c_fin_text}</span> de <span class='nb_jours'>${nb}</span>j</h2>`);
		cycle.append(`<div class='options'><button class='aff_masquer_cycle' for='contenu-${c_id}' id='but-contenu-${c_id}'>&#x1F440; Masquer</button> <button class='aff_temp_graph pas_glaire pas_fc' for='${c}'>&#x1F4C8; Courbe de température</button> <a href='export?cycle=${bill.date.str(c_date)}&type=pdf'><button>&#x1F4C4; export PDF</button></a> <a href='export?cycle=${bill.date.str(c_date)}&type=csv'><button>&#x1F522; export CSV</button></a></div>`);
		cycle.append(`<div class='contenu' id='contenu-${c_id}'></div>`);
		return cycle;
	},
	ooobservation2html : function(j) {
		let o_date = bill.date.parse(j.date);
		let o_id = "o-" + bill.date.str(o_date);
		let observation = $("<div>", {id: o_id, class: "day"});
		observation.click(bill.open_menu);
		observation.append(`<span class='data' style='display:none'>${JSON.stringify(j)}</span>`);
		observation.append(`<span class='d'>${o_date.getDate()} ${bill.text.mois[o_date.getMonth()]} </span>`);	
		observation.append(`<span class='j'>${j.pos}</span>`);
		observation.append(`<span class='p'>${j.jenesaispas ?  bill.text.je_sais_pas : ""}</span>`);
		if (j.chargement) observation.append(`<span class='l'>${bill.text.chargement}</span>`);
		else if (!j.jenesaispas) {
			if (j.gommette) {
				let contenu = "o";
				let color = j.gommette;
				if (j.gommette.includes(':)') && j.gommette.length>2){
					contenu = bill.gommette[":)"][0];
					color = j.gommette.replace(":)", "");
				}
				else {
					contenu = bill.gommette[j.gommette][0];
				}
				observation.append(`<span class='g ${bill.gommette[color][1]}'>${contenu}</span>`);
			}
			if (j.temperature) {
				let temp = parseFloat(j.temperature);
				let color = "#4169e1";
				if (temp > 37.5) color = "#b469e1";
				else if (temp <= 37.5 && temp >= 36.5) {
					let r = parseInt((1-(37.5-temp))*115)+65;
					color = `rgb(${r}, 105, 225)`;
				}
				observation.append(`<span class='t pas_glaire pas_fc' style='background-color: ${color}'>${temp}</span>`);
			}
			else observation.append(`<span class='t'></span>`);
			observation.append(`<span class='s'>${j.jour_sommet ? bill.text.sommet : ""}</span>`);
			observation.append(`<span class='u'>${j.union_sex ? bill.text.union : ""}</span>`);
			observation.append(`<span class='o pas_temp'>${j.sensation || ""}</span>`);
		}
		else {
			observation.append(`<span class='s'>${j.jour_sommet ? bill.text.sommet : ""}</span>`);
			observation.append(`<span class='u'>${j.union_sex ? bill.text.union : ""}</span>`);
		}
		observation.append(`<span class='c'>${j.commentaire || ""}</span>`);
		return observation;
	},
	observation2html : function(j) {
		let o_date = bill.date.parse(j.date);
		let o_id = "o-" + bill.date.str(o_date);
		let observation = $("<div>", {id: o_id, class: "day"});
		observation.click(bill.open_menu);
		observation.append(`<span class='data' style='display:none'>${JSON.stringify(j)}</span>`);
		observation.append(`<span class='d'>${o_date.getDate()} ${bill.text.mois[o_date.getMonth()]} </span>`);	
		observation.append(`<span class='j'>${j.pos}</span>`);
		if (j.chargement) {
			observation.append(`<span class='l'>${bill.text.chargement}</span>`);
			return observation;
		}
		let tbd = true;
		if (j.jenesaispas) {
			observation.append(`<span class='p'>${j.jenesaispas ?  bill.text.je_sais_pas : ""}</span>`);
			tbd = false;
		}
		else {
			if (j.gommette) {
				let contenu = "o";
				let color = j.gommette;
				if (j.gommette.includes(':)') && j.gommette.length>2){
					contenu = bill.gommette[":)"][0];
					color = j.gommette.replace(":)", "");
				}
				else {
					contenu = bill.gommette[j.gommette][0];
				}
				observation.append(`<span class='g ${bill.gommette[color][1]}'>${contenu}</span>`);
				tbd = false;
			}
			if (methode==1 && j.temperature) {
				let temp = parseFloat(j.temperature);
				let color = "#4169e1";
				if (temp > 37.5) color = "#b469e1";
				else if (temp <= 37.5 && temp >= 36.5) {
					let r = parseInt((1-(37.5-temp))*115)+65;
					color = `rgb(${r}, 105, 225)`;
				}
				observation.append(`<span class='t pas_glaire pas_fc' style='background-color: ${color}'>${temp}</span>`);
				tbd = false;
			}
			if (methode==3 && j.note_fc) {
				tbd = false;
			}
		}
		if (tbd) {
			observation.append(`<span class='r'>${bill.text.a_renseigner}</span>`);
			observation.append(`<span class='s'></span>`);
			return observation;
		}
		observation.append(`<span class='s'>${j.jour_sommet ? bill.text.sommet : ""}</span>`);
		observation.append(`<span class='u'>${j.union_sex ? bill.text.union : ""}</span>`);
		if (!j.jenesaispas) {
			observation.append(`<span class='o pas_fc'>${j.sensation || ""}</span>`);
			observation.append(`<span class='fc pas_temp pas_glaire'>${j.note_fc || ""}</span>`);
		}
		observation.append(`<span class='c'>${j.commentaire || ""}</span>`);
		return observation;
	},
	open_menu : function(e) {
		$("#temp_graph").hide();
		let j = JSON.parse($("#" + $(this).attr('id') + " .data").text());
		let o_date = bill.date.parse(j.date);
		let gommette = j.gommette? j.gommette : "";
		let titre = [bill.text.semaine[o_date.getDay()], o_date.getDate(), bill.text.mois_long[o_date.getMonth()], o_date.getFullYear()];
		titre.push(`<span>J${j.pos}</span>`);
		$("#jour_form_titre").html(titre.join(" "));
		$("#jour_form")[0].reset();
		$("#fc_msg").empty();
		$("#form_date").val(j.date);
		if (j.note_fc && methode==3) {
			$("#form_fc").val(j.note_fc);
			bill.fc_test_note();
		}
		if (gommette.includes(":)") && gommette.length>2) {
			$("#go_" + bill.gommette[":)"][1]).prop('checked', true);
			gommette = gommette.replace(":)", "");
		}
		if (bill.gommette[gommette]) $("#go_" + bill.gommette[gommette][1]).prop('checked', true);
		$("#form_temp").val(j.temperature);
		$("#vos_obs").empty();
		let n = 0;
		Object.entries(sensations).sort((a,b) => b[1] - a[1]).forEach(function (o){
			if (n<10) {
				let ob_id = btoa(unescape(encodeURIComponent(o[0]))).replace(/[^A-Za-z0-9 -]/g, "");
				let html = `<input type="checkbox" name="ob_${n}" id="ob_${ob_id}" value="${o[0]}" /><label for="ob_${ob_id}">${o[0]}</label><br />`;
				$("#vos_obs").append(html);
			}
			n += 1;	
		});
		let extra = []
		if (j.sensation) j.sensation.split(',').forEach(ob => {
			if (ob == bill.text.a_renseigner) return;
			ob = ob.toLowerCase().trim();
			let ob_id = btoa(unescape(encodeURIComponent(ob))).replace(/[^A-Za-z0-9 -]/g, "");
			let obj = $(`#ob_${ob_id}`);
			if(obj.length) obj.prop('checked', true);
			else extra.push(ob);
		});
		if (extra.length) $("#ob_extra").val(extra.join(", "));
		if (j.premier_jour) {
			$("#ev_premier_jour").prop('checked', true);
			$("#ev_premier_jour").attr('initial', true);
		}
		else $("#ev_premier_jour").attr('initial', false);
		if (j.union_sex) $("#ev_union").prop('checked', true);
		if (j.jour_sommet) $("#ev_jour_sommet").prop('checked', true);
		if (j.jenesaispas) $("#ev_jesaispas").prop('checked', true);
		$("#ev_premier_jour").change(function () {
			if (JSON.parse($("#ev_premier_jour").attr('initial')) && $("#ev_premier_jour").is(':checked')) {
				bill.page_a_recharger = false;
				return;
			}
			if (!JSON.parse($("#ev_premier_jour").attr('initial')) && !$("#ev_premier_jour").is(':checked')) {
				bill.page_a_recharger = false;
				return;
			}
			bill.page_a_recharger = true;
		});
		$("#from_com").val(j.commentaire);
		$("html, body").css({
			"overflow": "hidden",
			"touch-action": "none"
		});
		$("#jour_form").show();
	},
	close_menu : function (e) {
		$("html, body").css({
			"overflow": "visible",
			"touch-action": "auto"
		});
		$("#jour_form").hide();
	},
	submit_menu : function () {
		$("#ob_extra").val().split(',').forEach(function(o) {
			o = o.trim().toLowerCase();
			if (!o) return;
			if (!(o in sensations)) sensations[o] = 0;
			sensations[o] += 1;
		});
		let d = $("#jour_form").serializeArray();
		$.post("observation.php", $.param(d)).done(function(data){
			if (data.err){
				$("#form_err").val(data.err);
				console.error(data.err);
			}
			if (data.outcome == "ok") {
				if (bill.page_a_recharger) location.reload();
				bill.charger_observation(data.date);
				bill.close_menu();
			}		
		}).fail(function (ret) {
			console.error(ret.responseText); 
			$("#form_err").val(ret.responseText);
		});
	},
	suppr_observation : function () {
		let date = bill.date.parse($("#form_date").val());
		date.setHours(9);
		let jour = [bill.text.semaine[date.getDay()], date.getDate(), bill.text.mois_long[date.getMonth()], date.getFullYear()].join(" ");
		if (confirm(`Voulez-vous vraiment supprimer definitivement les données de la journée du ${jour}?`)) {
			$.post("observation.php", `suppr=${bill.date.str(date)}`).done(function(data){
				if (data.err){
					$("#form_err").val(data.err);
					console.error(data.err);
				}
				if (data.outcome == "ok") {
					bill.charger_observation(data.date);
					bill.close_menu();
				}		
			}).fail(function (ret) {
				console.error(ret.responseText); 
				$("#form_err").val(ret.responseText);
			});
		}
	},
	graph_preparation_data : function (data) {
		if (bill.graph_data[data.cycle] == undefined) bill.graph_data[data.cycle] = {};
		let date = bill.date.parse(data.date);
		let label = `${date.getDate()} ${bill.text.mois[date.getMonth()]}`;
		bill.graph_data[data.cycle][label] = parseFloat(data.temperature);
	},
	open_temp_graph : function() {
		bill.close_menu();
		let cycle = $(this).attr("for");
		let cycle_date = bill.date.parse(cycle);
		$("#temp_graph_titre").text(`Cycle du ${cycle_date.getDate()} ${bill.text.mois_long[cycle_date.getMonth()]}`);
		$('#canvas_temp').remove();
		$('#graph_container').append("<canvas id='canvas_temp'></canvas>");
		$("#temp_graph").show();
		const temp_chart = new Chart($("#canvas_temp"), {
			type: 'line',
			data: {
				datasets: [{
					data: bill.graph_data[cycle],
					fill: false,
					borderColor: '#1e824c',
					tension: 0.1
				}]
			},
			options: {
				responsive:true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					}
				}
			}
		});
	},
	fc_note_regex : /^((h|m|l|vl|b|H|M|L|VL|B)\s*)?(2W|10KL|10SL|10DL|10WL|2w|10kl|10sl|10dl|10wl|[024]|(([68]|10)\s*[BCGKLPYbcgklpy]{1,7}))?\s*([xX][123]|AD|ad)?(\s*[RrLl]?(ap|AP))?$/,
	fc_test_note : function() {
		if (!$("#form_fc").val()) {	
			$("#fc_msg").empty();
		}
		else if (bill.fc_note_regex.test($("#form_fc").val().toUpperCase())) {
			$("#fc_msg").html("(syntaxe valide)");
			$("#fc_msg").addClass("vert");
			$("#fc_msg").removeClass("rouge");
		}
		else {
			$("#fc_msg").html("(syntaxe invalide)");
			$("#fc_msg").addClass("rouge");
			$("#fc_msg").removeClass("vert");
		}
		bill.fc_note2form();
	},
	fc_form2note : function() {
		let note = $('input[name="fc_regles"]:checked').val();
		if (note.length && !note.endsWith(' ')) note += " " ;
		note += $('input[name="fc_sens"]:checked').val();
		note += $(".fc_obs:checked").map(function(){ return this.value }).get().join("");
		if (note.length && !note.endsWith(' ')) note += " ";
		note += $('input[name="fc_rec"]:checked').val();
		if (note.length && !note.endsWith(' ')) note += " ";
		note += $('input[name="fc_dou"]:checked').val();
		$("#form_fc").val(note.trim());
		bill.fc_test_note();
	},
	fc_note2form : function() {
		let note = $("#form_fc").val().trim().toUpperCase();
		if (note.startsWith('L') && !note.startsWith('LAP')) {
			$("#fc_rl").prop("checked", true);
			note = note.slice(1);
		}
		else {
			$("#fc_rl").prop("checked", false);
		}
		['10DL', '10SL', '10WL', 'RAP', 'LAP', 'X1', 'X2', 'X3', 'AD', 'AP', 'VL', '2W', '10', 'H', 'M', 'L', 'B', '0', '2', '4', '6', '8', 'C', 'G', 'K', 'P', 'Y'].forEach(c => {
			if (note.includes(c)) {
				$("#fc_" + c.toLowerCase()).prop("checked", true);
				note = note.replace(c,'');
			}
			else $("#fc_" + c.toLowerCase()).prop("checked", false);
		});
		note = $("#form_fc").val().trim().toUpperCase();
		let no_regle = true;
		let no_sens = true;
		let no_dou = true;
		let no_rec = true;
		['10DL', '10SL', '10WL', '10', '2W'].forEach(c => {
			if (note.includes(c)) no_sens=false;
			note = note.replace(c,'');
		});
		['RAP', 'LAP', 'AP'].forEach(c => {
			if (note.includes(c)) no_dou=false;
			note = note.replace(c,'');
		});
		['X1', 'X2', 'X3', 'AD'].forEach(c => {
			if (note.includes(c)) no_rec=false;
			note = note.replace(c,'');
		});
		['VL', 'H', 'M', 'L', 'B'].forEach(c => {
			if (note.includes(c)) no_regle=false;
			note = note.replace(c,'');
		});
		['0', '2', '4', '6', '8'].forEach(c => {
			if (note.includes(c)) no_sens=false;
			note = note.replace(c,'');
		});
		if (no_regle) $("#fc_r").prop("checked", true);
		if (no_sens) $("#fc_sr").prop("checked", true);
		if (no_rec) $("#fc_rr").prop("checked", true);
		if (no_dou) $("#fc_rp").prop("checked", true);
	},
	date : {
		now : function () {
			let d =  new Date();
			d.setHours(9,0,0,0);
			return d;
		},
		parse : function (str) {
			let d = bill.date.now();
			let b = str.split(/\D/);
			d.setFullYear(b[0], b[1]-1, b[2]);
			return d;
		},
		str : function (d) {
			let m = d.getMonth()+1;
			let j = d.getDate();
			return [d.getFullYear(), m<10 ? "0"+m : m, j<10 ? "0"+j : j].join("-");
		}
	}
}

