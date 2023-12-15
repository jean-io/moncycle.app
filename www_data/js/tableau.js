/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

moncycle_app = {
	gommette : {
		"."  : [".", "rouge"],
		"I"  : ["I", "vert"],
		"="  : ["=", "jaune"],
		":)" : ["👶", "bebe"],
	},
	fleche : {
		"↓" : ["b", "⬇️"],
		"↑" : ["h", "⬆️"],
		"→" : ["d", "➡️"],
		"←" : ["g", "⬅️"],
		""  : ["", ""]
	},
	text : {
		je_sais_pas: "❔ jour non observé",
		grossesse: "🤰 grossesse",
		a_renseigner : "👋 à renseigner",
		chargement : "⏳ chargement",
		a_aujourdhui : "à auj.",
		union : "❤️",
		sommet : "⛰️",
		mois : ["jan", "fév", "mars", "avr", "mai", "juin", "juil", "août", "sep", "oct", "nov", "déc"],
		mois_long : ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
		semaine : ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
	},
	sommets : {},
	page_a_recharger: false,
	graph_data : {},
	graphs : {},
	cycle_curseur : 0,
	a_le_focus: true,
	date_chargement: null,
	utilisateurs_beta : [5],
	constante : {},
	sensation : {},
	letsgo : function() {
		console.log("moncycle.app - app de suivi de cycle pour les méthodes naturelles");
		if (!localStorage.authok) window.location.replace('/connexion');
		moncycle_app.date_chargement = moncycle_app.date.str(moncycle_app.date.now());
		$.get("api/sensation.php", {}).done(function(data) {
			moncycle_app.sensation = data;
		}).fail(moncycle_app.redirection_connexion);
		$.get("api/constante.php", {}).done(function(data) {
			moncycle_app.constante = data;
			moncycle_app.charger_cycle();
			$("#nom").html(moncycle_app.constante.nom);
			if (moncycle_app.constante.donateur) $("#nom").append(" &#x1F396;&#xFE0F;");
			$(".main_button").css("display","inline-block");
		}).fail(moncycle_app.redirection_connexion);
		$("#charger_cycle").click(moncycle_app.charger_cycle);
		$("#jour_form_close").click(moncycle_app.close_menu);
		$("#jour_form_submit").click(moncycle_app.submit_menu);
		$("#jour_form input, #jour_form textarea").on("keyup change", moncycle_app.submit_menu);
		$("#jour_form_suppr").click(moncycle_app.suppr_observation);
		$("#form_fc").keyup(moncycle_app.fc_test_note).change(moncycle_app.fc_test_note);
		$("input.fc_form_note").change(moncycle_app.fc_form2note);
		$("#but_macro").click(function () {
			$("#but_macro").hide();
			$("#timeline").hide();
			$("#but_micro").show();
			$("#recap").show();
			while (moncycle_app.cycle_curseur < Math.min(5, moncycle_app.constante.tous_les_cycles.length)) moncycle_app.charger_cycle();
		});
		$("#but_micro").click(function () {
			$("#but_macro").show();
			$("#timeline").show();
			$("#but_micro").hide();
			$("#recap").hide();
		});
		$("#form_h_temp").focus(function () {
			if($("#form_h_temp").val().trim().length==0) {
				let d  = new Date();
				let h = d.getHours();
				let m = d.getMinutes();
				$("#form_h_temp").val((h<10 ? "0"+h : h) + ":" + (m<10 ? "0"+m : m));
			}
		});
		moncycle_app.charger_actu();
		$(window).focus(function() {
			if (moncycle_app.date.str(moncycle_app.date.now()) != moncycle_app.date_chargement) location.reload(false); 
		})
		if (moncycle_app.utilisateurs_beta.includes(moncycle_app.constante.id_utilisateur)) $(".beta").show();
	},
	redirection_connexion : function(err) {
		if (err.status == 401 || err.status == 403 || err.status == 407) {	
			window.localStorage.clear();
			window.location.replace('/connexion');
		}
	},
	charger_actu : function() {
		$.get("https://www.moncycle.app/actu.html", function(data) {
			let html = $.parseHTML(data);
			$("#actu_contenu").html(html);
			let titre = $("#actu_contenu").find("h4").text();
			if (titre && localStorage.actu_lu != titre) $("#actu").show();
			$("#fermer_actu").click(function () {
				localStorage.actu_lu = $("#actu_contenu").find("h4").text();
				$("#actu").hide();
			});
		});	
	},
	charger_cycle : function() {
		if (moncycle_app.cycle_curseur >= moncycle_app.constante.tous_les_cycles.length) {
			moncycle_app.form_nouveau_cycle();
			return;
		}
		let c = moncycle_app.cycle_curseur;
		moncycle_app.cycle_curseur += 1;
		let date_cycle_str = moncycle_app.constante.tous_les_cycles[c];
		let date_fin = moncycle_app.date.now();
		let fin_auj = true;
		if (c>0) {
			date_fin = new Date(moncycle_app.date.parse(moncycle_app.constante.tous_les_cycles[c-1]) - (1000*60*60*24));
			date_fin.setHours(9);
			fin_auj = false;
		}
		let date_cycle = moncycle_app.date.parse(date_cycle_str);
		date_cycle.setHours(9);
		let form_nouv_cycle = false;
		for (let i = 0; i < moncycle_app.constante.toutes_les_grossesses.length; i++) {
			let gross = moncycle_app.date.parse(moncycle_app.constante.toutes_les_grossesses[i]);
			gross.setHours(9);
			if (gross>=date_cycle && gross<=date_fin) {
				date_fin = gross;
				if (fin_auj) form_nouv_cycle = true;
			}
		}
		let nb_jours = parseInt(Math.round((date_fin-date_cycle)/(1000*60*60*24)+1));
		$("#timeline").prepend(moncycle_app.cycle2timeline(date_cycle_str, nb_jours, date_fin));
		$("#recap").prepend(moncycle_app.cycle2recap(date_cycle_str, nb_jours, date_fin));
		if (JSON.parse(localStorage.cycle_cache || "[]").includes(date_cycle_str)) moncycle_app.cycle_aff_switch(date_cycle_str);
		$(`#c-${date_cycle_str} .aff_masquer_cycle`).click(function (e) {
			moncycle_app.cycle_aff_switch($(this).attr("for"));
		});
		for (let pas = 0; pas < nb_jours; pas++) {
			let date_obs = new Date(date_cycle);
			date_obs.setDate(date_obs.getDate()+pas);
			date_obs_str = moncycle_app.date.str(date_obs);
			let data = {date: date_obs_str, pos: pas+1, chargement: true, temperature: NaN, cycle: date_cycle_str};
			moncycle_app.graph_preparation_data(data);
			$(`#c-${date_cycle_str} .contenu`).append(moncycle_app.observation2timeline(data));
			$(`#rc-${date_cycle_str} .contenu`).append(moncycle_app.observation2recap(data));
			moncycle_app.charger_observation(date_obs_str);
		}
		if (moncycle_app.constante.methode == 1) moncycle_app.cycle2graph(date_cycle_str);
		if (form_nouv_cycle) {
			moncycle_app.form_nouveau_cycle(false);
		}
	},
	charger_observation : function(o_date) {
		$.get("api/observation.php", { date: o_date }).done(function(data) {
			$(`#o-${data.date}`).replaceWith(moncycle_app.observation2timeline(data));
			$(`#ro-${data.date}`).replaceWith(moncycle_app.observation2recap(data));
			$(`.pas_${moncycle_app.constante.methode_diminutif}`).css("display", "none");
			if (data.jour_sommet) moncycle_app.sommets[data.date] = [data.cycle, 0];
			else if (!data.jour_sommet && data.date in moncycle_app.sommets) delete moncycle_app.sommets[data.date];
			moncycle_app.trois_jours();
			moncycle_app.graph_preparation_data(data);
		}).fail(moncycle_app.redirection_connexion);
	},
	form_nouveau_cycle: function (prepend=true) {
		let max_date = moncycle_app.date.str(moncycle_app.date.now());
		let min_date = "";
		if (prepend && moncycle_app.cycle_curseur>0) {
			max_date = moncycle_app.constante.tous_les_cycles[moncycle_app.cycle_curseur-1];
			max_date = moncycle_app.date.str(new Date(moncycle_app.date.parse(max_date) - (1000*60*60*24)));
		}
		else if (!prepend) {
			let min_calc = moncycle_app.date.parse(moncycle_app.constante.toutes_les_grossesses[0]);
			min_calc.setDate(min_calc.getDate()+1);
			min_date = moncycle_app.date.str(min_calc);
		}
		let text = "Entrer la date du premier jour du cycle à créer.";
		if (!prepend) text = "Entrer la date du jour de reprise du suivi du cycle.";
		let html = `<div class="cycle" id="nouveau_cycle"><h2 class="titre">Créer un nouveau cycle</h2><div class="nouveau_cycle_form">${text}<br><input id="nouveau_cycle_date" type="date" value="${max_date}" max="${max_date}" min="${min_date}" /> <input type="button" id="but_creer_cycle" value="✔️" /></div></div>`;	
		let nocycle = `<div id="nocycle">Plus de cycle à afficher.</div>`;
		if (prepend) {
			$("#charger_cycle").prop("disabled", true);
			$("#timeline").prepend(html);
			$("#recap").prepend(nocycle);
		}
		else {
			$("#timeline").append(html);
			$("#recap").append(nocycle);
		}
		$("#but_creer_cycle").click(function () {
			let nouveau_cycle_date = $("#nouveau_cycle_date").val();
			let max = moncycle_app.date.parse($("#nouveau_cycle_date").attr("max"));
			let min = moncycle_app.date.parse($("#nouveau_cycle_date").attr("min"));
			if (moncycle_app.date.parse(nouveau_cycle_date) > max || (!isNaN(min) && moncycle_app.date.parse(nouveau_cycle_date) < min)) {
				alert("Erreur: la date du premier jour du cycle à créer ne doit pas être dans un cycle existant et doit être antérieure à aujourd'hui.");
				return;
			}
			$.post("api/observation.php", `date=${nouveau_cycle_date}&premier_jour=1`).done(function(data){
				if (data.err){
					console.error(data.err);
				}
				if (data.outcome == "ok"){
					if (!prepend) {
						location.reload(true);
						return;
					}
					moncycle_app.constante.tous_les_cycles.push(nouveau_cycle_date);
					$("#charger_cycle").prop("disabled", false);
					$("#nouveau_cycle").remove();
					$("#nocycle").remove();
					moncycle_app.charger_cycle();
				}		
			}).fail(function (ret) {
				console.error(ret.responseText); 
			});
		});
	},
	trois_jours : function() {
		$(".day .s").empty();
		$(".obs .s").empty();
		$(".day .s").removeClass("petit");				
		for (const [s, data] of Object.entries(moncycle_app.sommets)) {	
			let nb_j_sommet = [1, 2, 3, $(`#o-${s}`).parent()[0].children.length - $(`#o-${s}`).index() - 1];
			nb_j_sommet.forEach(n => {
				let s_date = moncycle_app.date.parse(s);
				s_date.setDate(s_date.getDate()+n);		
				let s_id = moncycle_app.date.str(s_date);
				$(`#o-${s_id} .s`).html(`${moncycle_app.text.sommet}+${n}`);
				$(`#o-${s_id} .s`).addClass("petit");
				$(`#ro-${s_id} .s`).html(n);
			});
			$(`#o-${s} .s`).html(moncycle_app.text.sommet);	
			$(`#ro-${s} .s`).html(moncycle_app.text.sommet);
			$(`#o-${s} .s`).removeClass("petit");
		}
	},
	ligne_sympto : function () {
		for (const [s, data] of Object.entries(moncycle_app.sommets)) {
			let somme = 0;
			let nb = 0;
			for (let n=0; n<=6 ; n+=1) {
				let s_date = moncycle_app.date.parse(s);
				s_date.setDate(s_date.getDate()-n);		
				let s_id = moncycle_app.date.str(s_date);
				let temp = parseFloat($(`#o-${s_id} .t`).text());
				if (!isNaN(temp)) {
					somme += temp;
					nb += 1;
				}
			}
			moncycle_app.sommets[s] = [data[0], (somme/nb)+0.2];
		};
	},
	cycle_aff_switch: function (id) {
		let cache = JSON.parse(localStorage.cycle_cache || "[]");
		if ($("#contenu-c-" + id).is(":hidden")) {
			$("#contenu-c-" + id).show();
			$("#but-contenu-c-" + id).html("&#x1F440; Masquer");
			if (cache.includes(id)) cache.splice(cache.indexOf(id) , 1);
		}
		else {
			$("#contenu-c-" + id).hide();
			$("#but-contenu-c-" + id).html("&#x1F440; Afficher");
			if (!cache.includes(id)) cache.push(id);
		}
		localStorage.cycle_cache = JSON.stringify(cache);
	},
	cycle2timeline : function (c, nb, fin) {
		let c_id = "c-" + c;
		let cycle = $("<div>", {id: c_id, class: "cycle"});
		let c_date = moncycle_app.date.parse(c);
		let c_fin = new Date(fin);
		let c_fin_text = `au ${c_fin.getDate()} ${moncycle_app.text.mois[c_fin.getMonth()]} `;
		cycle.append(`<h2 class='titre'>Cycle du ${c_date.getDate()} ${moncycle_app.text.mois[c_date.getMonth()]} <span class='cycle_fin'>${c_fin_text}</span> de <span class='nb_jours'>${nb}</span>j</h2>`);
		cycle.append(`<div class='options'><button class='aff_masquer_cycle' for='${c}' id='but-contenu-${c_id}'>&#x1F440; Masquer</button> <a href='api/export?cycle=${moncycle_app.date.str(c_date)}&type=pdf'><button>&#x1F4C4; export PDF</button></a> <a href='api/export?cycle=${moncycle_app.date.str(c_date)}&type=csv'><button>&#x1F522; export CSV</button></a></div>`);
		cycle.append(`<div class='contenu' id='contenu-${c_id}'><div class='graph pas_glaire pas_fc' id='graph-${c_id}' ><canvas id='canvas-${c_id}'></canvas></div></div>`);
		return cycle;
	},
	cycle2recap : function (c, nb, fin) {
		let c_id = "rc-" + c;
		let cycle = $("<div>", {id: c_id, class: "cycle_recap"});
		let c_date = moncycle_app.date.parse(c);
		let c_fin = new Date(fin);
		let c_fin_text = `au ${c_fin.getDate()} ${moncycle_app.text.mois[c_fin.getMonth()]}. `;
		cycle.append(`<h5 class='titre'>Cycle du ${c_date.getDate()} ${moncycle_app.text.mois[c_date.getMonth()]}. <span class='cycle_fin'>${c_fin_text}</span> de <span class='nb_jours'>${nb}</span> jours</h5>`);
		cycle.append($("<div>", {id: "rc_contenu_" + c, class: "contenu"}));
		return cycle;
	},
	cycle2graph : function (id) {
		const temp_chart = new Chart($(`#canvas-c-${id}`), {
			type: 'line',
			data: {
				datasets: [{
					data: moncycle_app.graph_data[id],
					fill: false,
					borderColor: '#1e824c',
					tension: 0.1,
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
		moncycle_app.graphs[id] = temp_chart;
	},
	graph_update : function(id) {
		let vide = true;
		for (let k in moncycle_app.graph_data[id]) {
			if (vide && !isNaN(moncycle_app.graph_data[id][k])) vide = false;
		}
		if (vide) {
			$("#graph-c-" + id).hide();
		}
		else {
			$("#graph-c-" + id).show();
			moncycle_app.graphs[id].data.datasets[0].data = moncycle_app.graph_data[id];
			moncycle_app.graphs[id].update();
		}
		if (!vide && moncycle_app.constante.methode==1) {
			let j_sommet = "";
			for (const [s, data] of Object.entries(moncycle_app.sommets)) {
				if (id == data[0]) j_sommet = s;
			}
			if (j_sommet) {
				let date = moncycle_app.date.parse(j_sommet);
				let data = {};
				data[`${date.getDate()} ${moncycle_app.text.mois[date.getMonth()]}`] = moncycle_app.sommets[j_sommet][1];
				date.setDate(date.getDate()-6);
				data[`${date.getDate()} ${moncycle_app.text.mois[date.getMonth()]}`] = moncycle_app.sommets[j_sommet][1];
				let graph_data = {
					data: data,
					fill: false,
					borderColor: '#ac2433',
					pointRadius: 0,
					tension: 0,
					borderDash: [2, 2]
				};
				if (moncycle_app.graphs[id].data.datasets.length<=1) moncycle_app.graphs[id].data.datasets.push(graph_data);
				else moncycle_app.graphs[id].data.datasets[1].data = graph_data.data;
				moncycle_app.graphs[id].update();
			}
			if (!j_sommet && moncycle_app.graphs[id].data.datasets.length>1) {
				moncycle_app.graphs[id].data.datasets = moncycle_app.graphs[id].data.datasets.slice(0,1);
				moncycle_app.graphs[id].update();
			}
		}
	},
	observation2recap : function(j) {
		let o_date = moncycle_app.date.parse(j.date);
		let o_id = "ro-" + moncycle_app.date.str(o_date);
		let observation = $("<div>", {id: o_id, class: "obs"});
		observation.click(moncycle_app.open_menu);
		observation.append(`<span class='data' style='display:none'>${JSON.stringify(j)}</span>`);
		let color = "bleu";
		let index_couleur = j.gommette;
		let bebe = (j.gommette == ":)");
		if (j.gommette && j.gommette.includes(':)') && j.gommette.length>2) {
			color = moncycle_app.gommette[":)"][1];
			index_couleur = index_couleur.replace(":)", "");
			bebe = true;
		}
		if (moncycle_app.gommette[index_couleur]) color = moncycle_app.gommette[index_couleur][1]; 
		let car_du_bas = j.union_sex ? moncycle_app.text.union : "";
		if (j.err && j.err.includes("no data")) car_du_bas = moncycle_app.text.a_renseigner.substring(0,2);
		let car_du_milieu = bebe ? moncycle_app.gommette[":)"][0] : "";
		if (j.jenesaispas) {
			car_du_milieu = "?";		
			color = "bleu";
		}
		observation.append(`<span class='s'>${j.jour_sommet ? moncycle_app.text.sommet : ""}</span>`);
		observation.append(`<span class='g ${color}'>${car_du_milieu}</span>`);
		observation.append(`<span class=''>${car_du_bas}</span>`);
		return observation;
	},
	observation2timeline : function(j) {
		let o_date = moncycle_app.date.parse(j.date);
		let o_id = "o-" + moncycle_app.date.str(o_date);
		let observation = $("<div>", {id: o_id, class: "day"});
		observation.click(moncycle_app.open_menu);
		observation.append(`<span class='data' style='display:none'>${JSON.stringify(j)}</span>`);
		observation.append(`<span class='d'>${moncycle_app.text.semaine[o_date.getDay()][0]} ${o_date.getDate()} ${moncycle_app.text.mois[o_date.getMonth()]} </span>`);	
		observation.append(`<span class='j'>${j.pos}</span>`);
		if (j.chargement) {
			observation.append(`<span class='l'>${moncycle_app.text.chargement}</span>`);
			return observation;
		}
		let tbd = true;
		if (j.grossesse) {
			observation.append(`<span class='e'>${moncycle_app.text.grossesse}</span>`);
			tbd = false;
		}
		else if (j.jenesaispas) {
			observation.append(`<span class='p'>${moncycle_app.text.je_sais_pas}</span>`);
			tbd = false;
		}
		else {
			if (j.gommette) {
				let contenu = "o";
				let color = j.gommette;
				if (j.gommette.includes(':)') && j.gommette.length>2){
					contenu = moncycle_app.gommette[":)"][0];
					color = j.gommette.replace(":)", "");
				}
				else {
					contenu = moncycle_app.gommette[j.gommette][0];
				}
				observation.append(`<span class='g ${moncycle_app.gommette[color][1]}'>${contenu}</span>`);
				tbd = false;
			}
			if (moncycle_app.constante.methode==1 && j.temperature) {
				let temp = parseFloat(j.temperature);
				let color = "#4169e1";
				if (temp > 37.5) color = "#b469e1";
				else if (temp <= 37.5 && temp >= 36.5) {
					let r = parseInt((1-(37.5-temp))*115)+65;
					color = `rgb(${r}, 105, 225)`;
				}
				observation.append(`<span class='t pas_glaire pas_fc' style='background-color: ${color}'>${temp}</span>`);
				if (j.heure_temp) {
					let h = j.heure_temp.substring(0,5).replace(':','h');
					observation.append(`<span class='th pas_glaire pas_fc' style='color: ${color}'> à ${h}</span>`);
				}
				tbd = false;
			}
			if (moncycle_app.constante.methode==3 && j.note_fc) {
				tbd = false;
			}
		}
		if (tbd) {
			observation.append(`<span class='r'>${moncycle_app.text.a_renseigner}</span>`);
			observation.append(`<span class='s'></span>`);
			return observation;
		}
		observation.append(`<span class='s'>${j.jour_sommet ? moncycle_app.text.sommet : ""}</span>`);
		observation.append(`<span class='u'>${j.union_sex ? moncycle_app.text.union : ""}</span>`);
		if (!j.jenesaispas) {
			observation.append(`<span class='o pas_fc'>${j.sensation || ""}</span>`);
			observation.append(`<span class='fc pas_temp pas_glaire'>${j.note_fc || ""}</span>`);
			if (moncycle_app.fleche[j.fleche_fc]) observation.append(`<span class='fle pas_temp pas_glaire'>${moncycle_app.fleche[j.fleche_fc][1] || ""}</span>`);
		}
		if (j.commentaire) {
			let comment = j.commentaire.trim();
			while (comment.includes('\n')) {
				comment = comment.replace('\n', "<br />");
			}
			observation.append(`<span class='c'>${comment}</span>`);
		}
		return observation;
	},
	open_menu : function(e) {
		let j = JSON.parse($("#" + $(this).attr('id') + " .data").text());
		let o_date = moncycle_app.date.parse(j.date);
		let gommette = j.gommette? j.gommette : "";
		let titre = [moncycle_app.text.semaine[o_date.getDay()], o_date.getDate(), moncycle_app.text.mois_long[o_date.getMonth()], o_date.getFullYear()];
		titre.push(`<span>J${j.pos}</span>`);
		$("#jour_form_titre").html(titre.join(" "));
		$("#jour_form")[0].reset();
		$("#fc_msg").empty();
		$("#jour_form_saving").hide();
		$("#jour_form_saved").hide();
		$("#form_date").val(j.date);
		if (j.note_fc && moncycle_app.constante.methode==3) {
			$("#form_fc").val(j.note_fc);
			moncycle_app.fc_test_note();
		}
		if (j.fleche_fc && moncycle_app.constante.methode==3) $("#fc_f" + moncycle_app.fleche[j.fleche_fc][0]).prop('checked', true);
		if (gommette.includes(":)") && gommette.length>2) {
			$("#go_" + moncycle_app.gommette[":)"][1]).prop('checked', true);
			gommette = gommette.replace(":)", "");
		}
		if (moncycle_app.gommette[gommette]) $("#go_" + moncycle_app.gommette[gommette][1]).prop('checked', true);
		$("#form_temp").val(j.temperature);
		$("#form_h_temp").val(j.heure_temp);
		$("#vos_obs").empty();
		let n = 0;
		Object.entries(moncycle_app.sensation).sort((a,b) => b[1] - a[1]).forEach(function (o){
			if (n<10) {
				let ob_id = btoa(unescape(encodeURIComponent(o[0]))).replace(/[^A-Za-z0-9 -]/g, "");
				let html = $(`<input type="checkbox" name="ob_${n}" id="ob_${ob_id}" value="${o[0]}" /><label for="ob_${ob_id}">${o[0]}</label><br />`);
				html.on("keyup change", moncycle_app.submit_menu);
				$("#vos_obs").append(html);
			}
			n += 1;	
		});
		let extra = []
		if (j.sensation) j.sensation.split(',').forEach(ob => {
			if (ob == moncycle_app.text.a_renseigner) return;
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
		if (j.grossesse) $("#ev_grossesse").prop('checked', true);
		$("#ev_grossesse").attr('initial', new Boolean(j.grossesse));
		$(".ev_reload").change(function () {
			moncycle_app.page_a_recharger = (JSON.parse($("#ev_premier_jour").attr('initial')) != $("#ev_premier_jour").is(':checked'));
			if (!moncycle_app.page_a_recharger) moncycle_app.page_a_recharger = (JSON.parse($("#ev_grossesse").attr('initial')) != $("#ev_grossesse").is(':checked'));
		});
		$("#from_com").val(j.commentaire);
		$("html, body").css({
			"overflow": "hidden",
			"touch-action": "none"
		});
		$("#jour_form").show();
		$("#jour_form").scrollTop(0);
		$("#timeline").addClass("flou");
		$("#recap").addClass("flou");
	},
	close_menu : function (e) {
		$("html, body").css({
			"overflow": "visible",
			"touch-action": "auto"
		});
		$("#timeline").removeClass("flou");
		$("#recap").removeClass("flou");
		$("#jour_form").hide();
		if (moncycle_app.page_a_recharger) location.reload();
	},
	submit_menu : function () {
		$("#jour_form_saving").show();
		$("#jour_form_saved").hide();
		$("#ob_extra").val().split(',').forEach(function(o) {
			o = o.trim().toLowerCase();
			if (!o) return;
			if (!(o in moncycle_app.sensation)) moncycle_app.sensation[o] = 0;
			moncycle_app.sensation[o] += 1;
		});
		let d = $("#jour_form").serializeArray();
		$.post("api/observation.php", $.param(d)).done(function(data){
			$("#jour_form_saving").hide();
			if (data.err){
				$("#form_err").val(data.err);
				console.error(data.err);
			}
			if (data.outcome == "ok") {
				$("#jour_form_saved").show();
				moncycle_app.charger_observation(data.date);
			}
		}).fail(function (ret) {
			$("#jour_form_saving").hide();
			console.error(ret.responseText);
			$("#form_err").val(ret.responseText);
			moncycle_app.redirection_connexion(ret);
		});
	},
	suppr_observation : function () {
		let date = moncycle_app.date.parse($("#form_date").val());
		date.setHours(9);
		let jour = [moncycle_app.text.semaine[date.getDay()], date.getDate(), moncycle_app.text.mois_long[date.getMonth()], date.getFullYear()].join(" ");
		if (confirm(`Voulez-vous vraiment supprimer définitivement les données de la journée du ${jour}?`)) {
			$.post("api/observation.php", `suppr=${moncycle_app.date.str(date)}`).done(function(data){
				if (data.err){
					$("#form_err").val(data.err);
					console.error(data.err);
				}
				if (data.outcome == "ok") {
					moncycle_app.charger_observation(data.date);
					moncycle_app.close_menu();
				}		
			}).fail(function (ret) {
				console.error(ret.responseText); 
				$("#form_err").val(ret.responseText);
				moncycle_app.redirection_connexion(ret);
			});
		}
	},
	graph_preparation_data : function (data) {
		if (moncycle_app.graph_data[data.cycle] == undefined) moncycle_app.graph_data[data.cycle] = {};
		let date = moncycle_app.date.parse(data.date);
		let label = `${date.getDate()} ${moncycle_app.text.mois[date.getMonth()]}`;
		moncycle_app.graph_data[data.cycle][label] = parseFloat(data.temperature);
		if (moncycle_app.graphs[data.cycle]) {
			moncycle_app.ligne_sympto();
			moncycle_app.graph_update(data.cycle);
		}
	},	
	fc_note_regex : /^((h|m|l|vl|H|M|L|VL|VH)\s*(b|B)?\s*)?(2W|10KL|10SL|10DL|10WL|2w|10kl|10sl|10dl|10wl|[024]|(([68]|10)\s*[BCGKLPYRbcgklpyr]{1,8}))?\s*([xX][123]|AD|ad)?(\s*[RrLl]?(ap|AP))?$/,
	fc_test_note : function() {
		if (!$("#form_fc").val()) {	
			$("#fc_msg").empty();
		}
		else if (moncycle_app.fc_note_regex.test($("#form_fc").val().toUpperCase())) {
			$("#fc_msg").html("(syntaxe valide)");
			$("#fc_msg").addClass("vert");
			$("#fc_msg").removeClass("rouge");
		}
		else {
			$("#fc_msg").html("(syntaxe invalide)");
			$("#fc_msg").addClass("rouge");
			$("#fc_msg").removeClass("vert");
		}
		moncycle_app.fc_note2form();
	},
	fc_form2note : function() {
		let note = $('input[name="fc_regles"]:checked').val();
		note += $('input[name="fc_regles_b"]:checked').val() ? $('input[name="fc_regles_b"]:checked').val() : "";
		if (note.length && !note.endsWith(' ')) note += " " ;
		note += $('input[name="fc_sens"]:checked').val();
		note += $(".fc_obs:checked").map(function(){ return this.value }).get().join("");
		if (note.length && !note.endsWith(' ')) note += " ";
		note += $('input[name="fc_rec"]:checked').val();
		if (note.length && !note.endsWith(' ')) note += " ";
		note += $('input[name="fc_dou"]:checked').val();
		$("#form_fc").val(note.trim());
		moncycle_app.fc_test_note();
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
		['10DL', '10SL', '10WL', 'RAP', 'LAP', 'X1', 'X2', 'X3', 'AD', 'AP', 'VL', 'VH', '2W', '10', 'H', 'M', 'L', 'B', '0', '2', '4', '6', '8', 'C', 'G', 'K', 'P', 'Y', 'R'].forEach(c => {
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
		['VL', 'VH', 'H', 'M', 'L'].forEach(c => {
			if (note.includes(c)) no_regle=false;
			note = note.replace(c,'');
		});
		['0', '2', '4', '6', '8'].forEach(c => {
			if (note.includes(c)) no_sens=false;
			note = note.replace(c,'');
		});
		if (no_regle) $("#fc_nr").prop("checked", true);
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
			let d = moncycle_app.date.now();
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

