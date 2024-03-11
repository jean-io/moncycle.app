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
		grossesse_court: "🤰",
		a_renseigner : "👋 à renseigner",
		chargement : "⏳ chargement",
		a_aujourdhui : "à auj.",
		union : "❤️",
		sommet_bill : "⛰️",
		sommet_fc : "PIC",
		mois : ["jan", "fév", "mars", "avr", "mai", "juin", "juil", "août", "sep", "oct", "nov", "déc"],
		mois_long : ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
		semaine : ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
	},
	sommets : [],
	compteurs : {},
	page_a_recharger: false,
	graph_data : {},
	graphs : {},
	cycle_curseur : 0,
	a_le_focus: true,
	date_chargement: null,
	utilisateurs_beta : [5],
	constante : {},
	sensation : {},
	observation : {},
	timeline_asc : true,
	letsgo : function() {
		console.log("moncycle.app - app de suivi de cycle pour les méthodes naturelles");
		if (!localStorage.authok) window.location.replace('/connexion');
		moncycle_app.date_chargement = moncycle_app.date.str(moncycle_app.date.now());
		if (localStorage.constante != null && localStorage.timeline_asc != null) {
			moncycle_app.constante = JSON.parse(localStorage.constante);
			moncycle_app.timeline_asc = JSON.parse(localStorage.timeline_asc);
			moncycle_app.remplir_page_de_cycle();
		}
		if (localStorage.sensation != null) {
			moncycle_app.sensation = JSON.parse(localStorage.sensation);
		}
		$.get("api/sensation", {}).done(function(data) {
			moncycle_app.sensation = data;
			localStorage.sensation = JSON.stringify(data);
		}).fail(moncycle_app.redirection_connexion);
		$.get("api/constante", {}).done(function(data) {
			moncycle_app.constante = data;
			localStorage.constante = JSON.stringify(data);
			moncycle_app.timeline_asc = data.timeline_asc;
			localStorage.timeline_asc = JSON.stringify(data.timeline_asc);
			if (moncycle_app.cycle_curseur == 0) moncycle_app.remplir_page_de_cycle();
			$("#nom").html(moncycle_app.constante.nom);
			if (moncycle_app.constante.donateur) $("#nom").append(" &#x1F396;&#xFE0F;");
			$(".main_button").css("display","inline-block");
			if (moncycle_app.timeline_asc) $("#charger_cycle").hide();
			else $("#charger_cycle").show();
		}).fail(moncycle_app.redirection_connexion);
		if (moncycle_app.timeline_asc) $("#charger_cycle").hide();
		$("#charger_cycle").click(moncycle_app.charger_cycle);
		$("#jour_form_close").click(moncycle_app.close_menu);
		$("#jour_form_submit").click(moncycle_app.submit_menu);
		$("#jour_form_next").click(moncycle_app.open_menu);
		$("#jour_form_prev").click(moncycle_app.open_menu);
		$("#jour_form input, #jour_form textarea").on("change", moncycle_app.submit_menu);
		$("#form_fc").on("keyup", moncycle_app.fc_note2form);
		$("#jour_form_suppr").click(moncycle_app.suppr_observation);
		$("#but_mini_maxi").click(moncycle_app.mini_maxi_switch);
		if (localStorage.mini_maxi == "mini") moncycle_app.mini_maxi = "maxi";
		else moncycle_app.mini_maxi = "mini";
		moncycle_app.mini_maxi_switch();
		$("#form_heure_temp").focus(function () {
			if($("#form_heure_temp").val().trim().length==0) {
				let d  = new Date();
				let h = d.getHours();
				let m = d.getMinutes();
				$("#form_heure_temp").val((h<10 ? "0"+h : h) + ":" + (m<10 ? "0"+m : m));
				moncycle_app.submit_menu();
			}
		});
		$(window).scroll(function() {
			if(moncycle_app.timeline_asc && $(window).scrollTop() + $(window).height() +50 >= $(document).height()){
				moncycle_app.charger_cycle();
			}
		});
		moncycle_app.charger_actu();
		$(window).focus(function() {
			if (moncycle_app.date.str(moncycle_app.date.now()) != moncycle_app.date_chargement) location.reload(false); 
		})
		if (moncycle_app.utilisateurs_beta.includes(moncycle_app.constante.id_utilisateur)) $(".beta").show();
	},
	mini_maxi : "mini",
	mini_maxi_switch : function () {
		if (moncycle_app.mini_maxi=="maxi"){
			$("#timeline").hide();
			$("#recap").show();
			$("#but_mini_maxi").text("🔭 Vue maxi");
			moncycle_app.mini_maxi="mini";
			localStorage.mini_maxi="mini";
			if ($("#recap").children().length<=1) moncycle_app.remplir_page_de_cycle();
		}
		else {
			$("#timeline").show();
			$("#recap").hide();
			$("#but_mini_maxi").text("🔬 Vue mini");
			moncycle_app.mini_maxi="maxi";
			localStorage.mini_maxi="maxi";
			if ($("#timeline").children().length<=1) moncycle_app.remplir_page_de_cycle();
		}
	},
	remplir_page_de_cycle : function() {
		while ($(window).height() == $(document).height() && moncycle_app.cycle_curseur < moncycle_app.constante.tous_les_cycles.length) {
			moncycle_app.charger_cycle();
		}
		moncycle_app.charger_cycle();
		if ($(window).height() == $(document).height()) moncycle_app.form_nouveau_cycle();
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
		if (form_nouv_cycle && moncycle_app.timeline_asc) moncycle_app.form_nouveau_cycle(false);
		let nb_jours = parseInt(Math.round((date_fin-date_cycle)/(1000*60*60*24)+1));
		if (moncycle_app.timeline_asc) {
			$("#timeline").append(moncycle_app.cycle2timeline(date_cycle_str, nb_jours, date_fin));
			$("#recap").append(moncycle_app.cycle2recap(date_cycle_str, nb_jours, date_fin));
		}
		else {
			$("#timeline").prepend(moncycle_app.cycle2timeline(date_cycle_str, nb_jours, date_fin));
			$("#recap").prepend(moncycle_app.cycle2recap(date_cycle_str, nb_jours, date_fin));
		}
		if (JSON.parse(localStorage.cycle_cache || "[]").includes(date_cycle_str)) moncycle_app.cycle_aff_switch(date_cycle_str);
		$(`#c-${date_cycle_str} .aff_masquer_cycle`).click(function (e) {
			moncycle_app.cycle_aff_switch($(this).attr("for"));
		});
		let dates_req = [];
		let dates_data_holder = {};
		let sotred_obs = {}
		if (localStorage.observation) sotred_obs = JSON.parse(localStorage.observation);
		for (let pas = 0; pas < nb_jours; pas++) {
			let date_obs = new Date(date_cycle);
			date_obs.setDate(date_obs.getDate()+pas);
			let date_obs_str = moncycle_app.date.str(date_obs);
			let data = null;
			if (sotred_obs[date_obs_str]) data = sotred_obs[date_obs_str];
			else data = {date_obs: date_obs_str, pos: pas+1, chargement: true, temperature: NaN, cycle: date_cycle_str};
			dates_data_holder[date_obs_str] = data;
			moncycle_app.observation[date_obs_str] = data;
			if (moncycle_app.timeline_asc) $(`#c-${date_cycle_str} .contenu`).prepend(moncycle_app.observation2timeline(data));
			else $(`#c-${date_cycle_str} .contenu`).append(moncycle_app.observation2timeline(data));
			$(`#rc-${date_cycle_str} .contenu`).append(moncycle_app.observation2recap(data));
			dates_req.push(date_obs_str);
		}
		moncycle_app.graph_preparation_data(dates_data_holder);
		moncycle_app.charger_observation(dates_req.join(','));
		if (moncycle_app.constante.methode == 1 || moncycle_app.constante.methode == 4) moncycle_app.cycle2graph(date_cycle_str);
		if (form_nouv_cycle && !moncycle_app.timeline_asc) moncycle_app.form_nouveau_cycle(false);
	},
	charger_observation : function(o_date) {
		$.get("api/observation", { date: o_date }).done(function(data) {
			let sotred_obs = {};
			if (localStorage.observation) sotred_obs = JSON.parse(localStorage.observation);
			$.each(data, function (o_date, o_data) {
				moncycle_app.observation[o_date] = o_data;
				sotred_obs[o_date] = o_data;
				$(`#o-${o_date}`).replaceWith(moncycle_app.observation2timeline(o_data));
				$(`#ro-${o_date}`).replaceWith(moncycle_app.observation2recap(o_data));
				if (o_data.jour_sommet && $.inArray(o_date, moncycle_app.sommets)<0) moncycle_app.sommets.push(o_date);
				else if (!o_data.jour_sommet && $.inArray(o_date, moncycle_app.sommets)>=0) moncycle_app.sommets.splice($.inArray(o_date, moncycle_app.sommets), 1);
				if (o_data.compteur) moncycle_app.compteurs[o_date] = o_data.compteur;
				else if (!o_data.compteur && o_date in moncycle_app.compteurs) delete moncycle_app.compteurs[o_date];
			});
			localStorage.observation = JSON.stringify(sotred_obs);
			$(`.pas_${moncycle_app.constante.methode_diminutif}`).css("display", "none");
			moncycle_app.trois_jours();
			moncycle_app.graph_preparation_data(data);
		}).fail(moncycle_app.redirection_connexion);
	},
	form_nouveau_cycle_active: false,
	form_nouveau_cycle: function (prepend=true) {
		if (moncycle_app.form_nouveau_cycle_active) return;
		moncycle_app.form_nouveau_cycle_active = true;
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
		let nocycle = `<div id="nocycle">Tous les cycles sont affichées.</div>`;
		if (moncycle_app.cycle_curseur == 0) nocycle = `<div id="nocycle">Dans la page MAXI vous avez la possibilité de créer un nouveau cycle.</div>`;
		if (prepend && !moncycle_app.timeline_asc) {
			$("#charger_cycle").prop("disabled", true);
			$("#timeline").prepend(html);
			$("#recap").prepend(nocycle);
		}
		else {
			$("#timeline").append(html);
			if (moncycle_app.cycle_curseur == 0) $("#recap").append(nocycle);
		}
		$("#but_creer_cycle").click(function () {
			let nouveau_cycle_date = $("#nouveau_cycle_date").val();
			let max = moncycle_app.date.parse($("#nouveau_cycle_date").attr("max"));
			let min = moncycle_app.date.parse($("#nouveau_cycle_date").attr("min"));
			if (moncycle_app.date.parse(nouveau_cycle_date) > max || (!isNaN(min) && moncycle_app.date.parse(nouveau_cycle_date) < min)) {
				alert("Erreur: la date du premier jour du cycle à créer ne doit pas être dans un cycle existant et doit être antérieure à aujourd'hui.");
				return;
			}
			$.post("api/observation", `date=${nouveau_cycle_date}&premier_jour=1`).done(function(data){
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
					moncycle_app.form_nouveau_cycle_active = false;
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
		$(".obs .s").show();
		$(".day .s").removeClass("petit");
		let txt_sommet = moncycle_app.text.sommet_bill;
		if (moncycle_app.constante.methode==3 || moncycle_app.constante.methode==4) txt_sommet = moncycle_app.text.sommet_fc;
		moncycle_app.sommets.forEach(s => {
			let last = 3;
			if (!moncycle_app.timeline_asc) last = $(`#o-${s}`).parent()[0].children.length - $(`#o-${s}`).index() - 1;
			else last = $(`#o-${s}`).index();
			[1, 2, 3, last].forEach(n => {
				let s_date = moncycle_app.date.parse(s);
				s_date.setDate(s_date.getDate()+n);
				let s_id = moncycle_app.date.str(s_date);
				$(`#o-${s_id} .s`).html(`${txt_sommet}+${n}`);
				$(`#o-${s_id} .s`).addClass("petit");
				$(`#ro-${s_id} .s`).html(n);
			});
			$(`#o-${s} .s`).html(txt_sommet);	
			$(`#ro-${s} .s`).html(txt_sommet);
			$(`#o-${s} .s`).removeClass("petit");
		});
		if (moncycle_app.constante.methode==3 || moncycle_app.constante.methode==4) return;
		$(".day .n").empty();
		$(".obs .n").empty();
		$(".obs .n").hide();
		$.each(moncycle_app.compteurs,function(d,c){
			for (let i = 0; i < c; i++) {
				let s_date = moncycle_app.date.parse(d);
				s_date.setDate(s_date.getDate()+i);
				let s_id = moncycle_app.date.str(s_date);
				$(`#o-${s_id} .n`).html(`+${i+1}`);
				$(`#o-${s_id} .n`).addClass("petit");
				if (($(`#ro-${s_id} .s`).text()).length==0) {
					$(`#ro-${s_id} .n`).html(i+1);
					$(`#ro-${s_id} .n`).show();
					$(`#ro-${s_id} .s`).hide();
				}
				
			}
		});
	},
	cycle_aff_switch: function (id) {
		let cache = JSON.parse(localStorage.cycle_cache || "[]");
		if ($("#contenu-c-" + id).is(":hidden")) {
			$("#contenu-c-" + id).show();
			if(parseInt($("#graph-c-" + id).attr("vide"))!=1 && (moncycle_app.constante.methode==1 || moncycle_app.constante.methode==4)) $("#graph-c-" + id).show();
			$("#but-contenu-c-" + id).html("&#x1F440; Masquer");
			if (cache.includes(id)) cache.splice(cache.indexOf(id) , 1);
		}
		else {
			$("#contenu-c-" + id).hide();
			$("#graph-c-" + id).hide();
			$("#but-contenu-c-" + id).html("&#x1F440; Afficher");
			if (!cache.includes(id)) cache.push(id);
		}
		localStorage.cycle_cache = JSON.stringify(cache);
		if (moncycle_app.timeline_asc) moncycle_app.remplir_page_de_cycle();
	},
	cycle2timeline : function (c, nb, fin) {
		let c_id = "c-" + c;
		let cycle = $("<div>", {id: c_id, class: "cycle"});
		let c_date = moncycle_app.date.parse(c);
		let c_fin = new Date(fin);
		let c_fin_text = `au ${c_fin.getDate()} ${moncycle_app.text.mois[c_fin.getMonth()]} `;
		let c_title = $(`<h2 class='titre'>Cycle du ${c_date.getDate()} ${moncycle_app.text.mois[c_date.getMonth()]} <span class='cycle_fin'>${c_fin_text}</span> de <span class='nb_jours'>${nb}</span>j</h2>`);
		let c_action = $(`<div class='options'><button class='aff_masquer_cycle' for='${c}' id='but-contenu-${c_id}'>&#x1F440; Masquer</button> <a href='api/export?start_date=${moncycle_app.date.str(c_date)}&end_date=${moncycle_app.date.str(fin)}&type=pdf'><button>&#x1F4C4; export PDF</button></a> <a href='api/export?start_date=${moncycle_app.date.str(c_date)}&end_date=${moncycle_app.date.str(fin)}&type=csv'><button>&#x1F522; export CSV</button></a></div>`);
		let c_graph = $(`<div class='graph pas_bill pas_fc' id='graph-${c_id}' style='display:none' ><canvas id='canvas-${c_id}'></canvas></div>`);
		let c_content = $(`<div class='contenu' id='contenu-${c_id}'></div>`);
		cycle.append(c_title);
		cycle.append(c_action);
		cycle.append(c_graph);
		cycle.append(c_content);
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
		for (let k in moncycle_app.graph_data[id]) if (vide && !isNaN(moncycle_app.graph_data[id][k])) vide = false;
		$("#graph-c-" + id).attr("vide", vide ? 1 : 0);
		if (vide) $("#graph-c-" + id).hide();
		else {
			if (!$("#contenu-c-" + id).is(":hidden")) $("#graph-c-" + id).show();
			moncycle_app.graphs[id].data.datasets[0].data = moncycle_app.graph_data[id];
			moncycle_app.graphs[id].update();
		}
		if (!vide && (moncycle_app.constante.methode==1 || moncycle_app.constante.methode==4)) {
			moncycle_app.graphs[id].data.datasets = moncycle_app.graphs[id].data.datasets.slice(0,1);
			moncycle_app.graphs[id].update();
		}
	},
	observation2recap : function(j) {
		let o_date = moncycle_app.date.parse(j.date_obs);
		let o_id = "ro-" + moncycle_app.date.str(o_date);
		let o_class = "obs";
		if (j.grossesse) o_class += " o_gross";
		let observation = $("<div>", {id: o_id, class: o_class, date: moncycle_app.date.str(o_date)});
		observation.click(moncycle_app.open_menu);
		let color = "vide";
		let index_couleur = j.gommette;
		let bebe = (j.gommette == ":)");
		if (j.gommette && j.gommette.includes(':)') && j.gommette.length>2) {
			color = moncycle_app.gommette[":)"][1];
			index_couleur = index_couleur.replace(":)", "");
			bebe = true;
		}
		if (moncycle_app.gommette[index_couleur]) color = moncycle_app.gommette[index_couleur][1]; 
		let car_du_milieu = bebe ? moncycle_app.gommette[":)"][0] : "";
		let car_du_bas = j.union_sex ? moncycle_app.text.union : "";
		if (j.err && j.err.includes("no data")) car_du_milieu = moncycle_app.text.a_renseigner.substring(0,2);
		let recap_note = j.note_fc;
		if ((moncycle_app.constante.methode==3 || moncycle_app.constante.methode==4) && j.note_fc) {
			recap_note = recap_note.toUpperCase();
			recap_note = recap_note.replace('RAP','').replace('LAP','').replace('AD','').replace('AP','').replace('B','');
		}
		else recap_note = "";
		if ((moncycle_app.constante.methode==3 || moncycle_app.constante.methode==4) && car_du_milieu == "" && j.note_fc) {
			const should_be_red = ['VL', 'L', 'VH', 'H', 'M'];
			should_be_red.forEach(c => {
				recap_note = recap_note.trim();
				if (recap_note.indexOf(c) == 0) {
					car_du_milieu += c;
					recap_note = recap_note.replace(c,'');
				}
			});
		}
		if (j.grossesse) {
			color = "pink";
			car_du_milieu = "G"
			car_du_bas = moncycle_app.text.grossesse_court;
		}
		if (j.jenesaispas) {
			car_du_milieu = "?";		
			color = "jcpas";
		}
		observation.append(`<span class='s'>${j.jour_sommet ? moncycle_app.text.sommet_bill : ""}</span>`);
		if (moncycle_app.constante.methode==1 || moncycle_app.constante.methode==2) observation.append(`<span class='n'></span>`);
		observation.append(`<span class='g ${color}'>${car_du_milieu}</span>`);
		if ((moncycle_app.constante.methode==3 || moncycle_app.constante.methode==4) && !j.grossesse && !j.jenesaispas && j.note_fc){
			recap_note = recap_note.replace('X1','').replace('X2','').replace('X3','');
			let fc_glaire = recap_note.match(/\d+/);
			if (fc_glaire) recap_note = recap_note.replace(fc_glaire[0], '');
			observation.append(`<span class='fc'>${fc_glaire? fc_glaire[0] : ""}</span>`);
			recap_note = recap_note.trim().replace(/\s+/g, '')
			if (recap_note.length>2) recap_note='*';
			observation.append(`<span class='fc'>${recap_note}</span>`);
		}
		else if (moncycle_app.constante.methode==3 || moncycle_app.constante.methode==4) {
			observation.append(`<span class='fc'></span>`);
			observation.append(`<span class='fc'></span>`);
		}
		observation.append(`<span class='c'>${car_du_bas}</span>`);
		return observation;
	},
	observation2timeline : function(j) {
		let o_date = moncycle_app.date.parse(j.date_obs);
		let o_id = "o-" + moncycle_app.date.str(o_date);
		let o_class = "day";
		if (moncycle_app.constante.methode==3 || moncycle_app.constante.methode==4) o_class += " o_fc";
		else o_class += " o_bill";
		if (j.grossesse) o_class += " o_gross";
		let observation = $("<div>", {id: o_id, class: o_class, date : moncycle_app.date.str(o_date)});
		observation.click(moncycle_app.open_menu);
		observation.append(`<span class='d'>${moncycle_app.text.semaine[o_date.getDay()][0]} ${o_date.getDate()} ${moncycle_app.text.mois[o_date.getMonth()]} </span>`);	
		observation.append(`<span class='j'>${j.pos}</span>`);
		if (j.chargement) {
			observation.append(`<span class='l'>${moncycle_app.text.chargement}</span>`);
			return observation;
		}
		let tbd = true;
		if (j.grossesse) {
			observation.append(`<span class='e'>${moncycle_app.text.grossesse}</span>`);
			observation.append(`<span class='s'></span>`);
			tbd = false;
		}
		else {
			if (j.jenesaispas) {
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
				let html_note_fc = moncycle_app.fc_note2html(j.note_fc || "");
				observation.append(`<span class='fc pas_bill pas_bill_temp'>${html_note_fc}</span>`);
				observation.append(`<span class='s'>${j.jour_sommet ? moncycle_app.text.sommet_bill : ""}</span>`);
				observation.append(`<span class='n'></span>`);
				if ((moncycle_app.constante.methode==1 || moncycle_app.constante.methode==4) && j.temperature) {
					let temp = parseFloat(j.temperature);
					let color = "#4169e1";
					if (temp > 37.5) color = "#b469e1";
					else if (temp <= 37.5 && temp >= 36.5) {
						let r = parseInt((1-(37.5-temp))*115)+65;
						color = `rgb(${r}, 105, 225)`;
					}
					observation.append(`<span class='t pas_bill pas_fc' style='background-color: ${color}'>${temp}</span>`);
					if (j.heure_temp) {
						let h = j.heure_temp.substring(0,5).replace(':','h');
						observation.append(`<span class='th bill pas_fc pas_bill' style='color: ${color}'> à ${h}</span>`);
					}
					tbd = false;
				}
				if ((moncycle_app.constante.methode==3 || moncycle_app.constante.methode==4) && j.note_fc) tbd = false;
			}
			if (tbd) {
				observation.append(`<span class='r'>${moncycle_app.text.a_renseigner}</span>`);
				observation.append(`<span class='s'></span>`);
				observation.append(`<span class='n'></span>`);
				return observation;
			}
			if (!j.jenesaispas) {
				observation.append(`<span class='o pas_fc pas_fc_temp'>${j.sensation || ""}</span>`);
				if (moncycle_app.fleche[j.fleche_fc]) observation.append(`<span class='fle pas_bill pas_bill_temp'>${moncycle_app.fleche[j.fleche_fc][1] || ""}</span>`);
			}
			observation.append(`<span class='u'>${j.union_sex ? moncycle_app.text.union : ""}</span>`);
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
	menu_opened_date : null,
	open_menu : function(e, date = null) {
		let o_date;
		if (date) o_date = moncycle_app.date.parse(date);
		else o_date = moncycle_app.date.parse($(this).attr('date'));
		date = moncycle_app.date.str(o_date);
		moncycle_app.menu_opened_date = date;
		let j = moncycle_app.observation[moncycle_app.menu_opened_date];
		let gommette = j.gommette? j.gommette : "";
		let titre = [moncycle_app.text.semaine[o_date.getDay()], o_date.getDate(), moncycle_app.text.mois_long[o_date.getMonth()], o_date.getFullYear()];
		titre.push(`<span>J${j.pos}</span>`);
		$("#jour_form_titre").html(titre.join(" "));
		let arrow = {"next" : '↑', "prev" : '↓'};
		if (!moncycle_app.timeline_asc) arrow = {"next" : '↓', "prev" : '↑'};
		$("#jour_form_prev").text(arrow["prev"] + " J" + (j.pos-1));
		$("#jour_form_next").text(arrow["next"] + " J" + (j.pos+1));
		let date_cursor = new Date(j.date_obs);
		date_cursor.setDate(date_cursor.getDate()+1);
		let str_date_cursor = moncycle_app.date.str(date_cursor);
		if (moncycle_app.observation[str_date_cursor] && !moncycle_app.observation[str_date_cursor].premier_jour) {
			$("#jour_form_next").attr("date", str_date_cursor);
			$("#jour_form_next").show();
		}
		else $("#jour_form_next").hide();
		date_cursor.setDate(date_cursor.getDate()-2);
		str_date_cursor = moncycle_app.date.str(date_cursor);
		if (j.pos-1 > 0 && moncycle_app.observation[str_date_cursor]) {
			$("#jour_form_prev").attr("date", str_date_cursor);
			$("#jour_form_prev").show();
		}
		else $("#jour_form_prev").hide();
		$("#jour_form")[0].reset();
		$("#fc_msg").empty();
		$("#jour_form_saving").hide();
		$("#jour_form_saved").hide();
		$("#form_date").val(j.date_obs);
		if (j.note_fc && (moncycle_app.constante.methode==3 || moncycle_app.constante.methode==4)) {
			$("#form_fc").val(j.note_fc);
			moncycle_app.fc_note2form();
			moncycle_app.fc_test_note();
		}
		if (j.fleche_fc && (moncycle_app.constante.methode==3 || moncycle_app.constante.methode==4)) $("#fc_f" + moncycle_app.fleche[j.fleche_fc][0]).prop('checked', true);
		if (gommette.includes(":)") && gommette.length>2) {
			$("#go_" + moncycle_app.gommette[":)"][1]).prop('checked', true);
			gommette = gommette.replace(":)", "");
		}
		if (moncycle_app.gommette[gommette]) $("#go_" + moncycle_app.gommette[gommette][1]).prop('checked', true);
		$("#form_temp").val(j.temperature);
		$("#form_heure_temp").val(j.heure_temp);
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
		if (j.compteur && j.compteur > 0) {
			$("#ev_compteur_actif").prop('checked', true);
			$("#ev_compteur_nb").val(j.compteur);
			$("#ev_hidden_compteur").val(j.compteur)
		}
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
		moncycle_app.menu_opened_date = null;
		if (moncycle_app.page_a_recharger) location.reload();
	},
	submit_menu : function () {
		$("#jour_form_saving").show();
		$("#jour_form_saved").hide();
		let o_data = moncycle_app.menu_opened_date;
		if (this.id == "form_fc") moncycle_app.fc_note2form();
		else moncycle_app.fc_form2note();
		moncycle_app.fc_test_note();
		if ($("#ev_compteur_actif")[0].checked) $("#ev_hidden_compteur").val($("#ev_compteur_nb").val());
		else $("#ev_hidden_compteur").val(0);
		$("#ob_extra").val().split(',').forEach(function(o) {
			o = o.trim().toLowerCase();
			if (!o) return;
			if (!(o in moncycle_app.sensation)) moncycle_app.sensation[o] = 0;
			moncycle_app.sensation[o] += 1;
		});
		localStorage.sensation = JSON.stringify(moncycle_app.sensation);
		let d = $("#jour_form").serializeArray();
		$.post("api/observation", $.param(d)).done(function(data){
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
			$.ajax({type : 'DELETE', "url" : "api/observation", "data" : `date=${moncycle_app.date.str(date)}`}).done(function(data){
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
		$.each(data, function(o_date, o_data) {
			if (moncycle_app.graph_data[o_data.cycle] == undefined) moncycle_app.graph_data[o_data.cycle] = {};
			let date = moncycle_app.date.parse(o_date);
			let label = `${date.getDate()} ${moncycle_app.text.mois[date.getMonth()]}`;
			moncycle_app.graph_data[o_data.cycle][label] = parseFloat(o_data.temperature);
			if (moncycle_app.graphs[o_data.cycle]) moncycle_app.graph_update(o_data.cycle);
		});
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
		return note;
	},
	fc_note2html (note) {
		const should_be_red = ['VL', 'VH', 'H', 'M', 'B'];
		const less_important = ['RAP', 'LAP', 'AP', 'X1', 'X2', 'X3', 'AD',];
		note = note.toUpperCase();
		less_important.forEach(c => {
			note = note.replace(c,`<span class='note_not_imp'>${c}</span>`);
		});
		should_be_red.forEach(c => {
			note = note.replace(c,`<span class='note_rouge'>${c}</span>`);
		});
		if (note.startsWith('L')) {
			note = note.slice(1);
			note = `<span class='note_rouge'>L</span>` + note;
		}
		return note;
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
		return note;
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

