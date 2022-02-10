bill = {
	gommette : {
		"_"   : ["⌛", "chargement"],
		"."  : [".", "rouge"],
		"I"  : ["I", "vert"],
		"="  : ["=", "jaune"],
		"?"  : ["?", "jesaispas"],
		":)" : ["👶", "bebe"],
		""   : ["👋", "aremplir"]
	},
	text : {
		a_renseigner : "à renseigner",
		a_aujourdhui : "à auj.",
		union : "❤️",
		sommet : "⛰️",
		mois : ["jan", "fév", "mars", "avr", "mai", "juin", "juil", "août", "sep", "oct", "nov", "déc"],
		mois_long : ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
		semaine : ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
	},
	sommets : [],
	page_a_recharger: false,
	letsgo : function() {
		console.log("Bill - cahier à gommettes pour la méthode Billings.");
		bill.charger_cycle();
		$("#charger_cycle").click(bill.charger_cycle);
		$("#jour_form_close").click(function () {
			$("#jour_form").hide();
		});
		$("#jour_form_submit").click(bill.submit_menu);	
	},
	cycle_curseur : 0,
	charger_cycle : function() {
		if (bill.cycle_curseur >= tous_les_cycles.length) {
			bill.form_nouveau_cycle();
			return;
		}
		let c = bill.cycle_curseur;
		bill.cycle_curseur += 1;
		let date_cycle_str = tous_les_cycles[c];
		let date_fin = new Date();
		if (c>0) { date_fin = new Date(new Date(tous_les_cycles[c-1]) - (1000*60*60*24)); }
		let date_cycle = new Date(date_cycle_str);
		let nb_jours = parseInt((date_fin-date_cycle)/(1000*60*60*24)+1);
		$("#timeline").prepend(bill.cycle2html(date_cycle_str, nb_jours, date_fin));
		for (let pas = 0; pas < nb_jours; pas++) {
			let date_obs = new Date(date_cycle);
			date_obs.setDate(date_obs.getDate()+pas);
			date_obs_str = date_obs.toISOString().substring(0, 10);
			$(`#c-${date_cycle_str} .contenu`).append(bill.observation2html({date: date_obs_str, pos: pas+1, gommette: '_'}));
			bill.charger_observation(date_obs_str);
		}
	},
	charger_observation : function(o_date) {
		$.get("observation.php", { date: o_date }).done(function(data) {
			console.log(data);
			$(`#o-${data.date}`).replaceWith(bill.observation2html(data));
			if (data.jour_sommet && !bill.sommets.includes(data.date)) bill.sommets.push(data.date);
			else if (!data.jour_sommet && bill.sommets.includes(data.date)) bill.sommets.splice(bill.sommets.indexOf(data.date),1);
			bill.trois_jours();
		});
	},
	form_nouveau_cycle: function () {
		let max_date = new Date().toISOString().substring(0, 10);
		if (bill.cycle_curseur>0) {
			max_date = tous_les_cycles[bill.cycle_curseur-1];
			max_date = new Date(new Date(max_date) - (1000*60*60*24)).toISOString().substring(0, 10);
		}
		let html = `<div class="cycle" id="nouveau_cycle"><h2 class="titre">Créer un nouveau cycle</h2><div class="nouveau_cycle_form">Entrer la date du premier jour du cycle à créer.<br><input id="nouveau_cycle_date" type="date" value="${max_date}" max="${max_date}" /> <input type="button" id="but_creer_cycle" value="✔️" /></div></div>`;
		$("#charger_cycle").prop("disabled", true);
		$("#timeline").prepend(html);
		$("#but_creer_cycle").click(function () {
			let nouveau_cycle_date = $("#nouveau_cycle_date").val();
			$.post("observation.php", `date=${nouveau_cycle_date}&premier_jour=1`).done(function(data){
			console.log(data);
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
			let nb_j_sommet = [1, 2, 3, 15, $(`#o-${s}`).parent()[0].children.length - $(`#o-${s}`).index() - 1];
			nb_j_sommet.forEach(n => {
				let s_date = new Date(s);
				s_date.setDate(s_date.getDate()+n);		
				let s_id = s_date.getFullYear() + "-";
				s_id += (s_date.getMonth()+1).toLocaleString('fr-FR', {minimumIntegerDigits: 2, useGrouping:false}) + "-";
				s_id += s_date.getDate().toLocaleString('fr-FR', {minimumIntegerDigits: 2, useGrouping:false});
				$(`#o-${s_id} .s`).html(`${bill.text.sommet}+${n}`);
				$(`#o-${s_id} .s`).addClass("petit");
			});
		});
	},
	cycle2html : function (c, nb, fin) {
		let c_id = "c-" + c;
		let cycle = $("<div>", {id: c_id, class: "cycle"});
		let c_date = new Date(c);
		let c_fin = new Date(fin);
		let c_fin_text = `au ${c_fin.getDate()} ${bill.text.mois[c_fin.getMonth()]} `;
		cycle.append(`<h2 class='titre'><a href='export.php?cycle=${c_date.toISOString().substring(0, 10)}&type=pdf'>PDF</a><a href='export.php?cycle=${c_date.toISOString().substring(0, 10)}&type=csv'>CSV</a>Cycle du ${c_date.getDate()} ${bill.text.mois[c_date.getMonth()]} <span class='cycle_fin'>${c_fin_text}</span> <span class='nb_jours'>${nb}</span>j</h2>`);
		cycle.append("<div class='contenu'></div>");
		return cycle;
	},
	observation2html : function(j) {
		let o_id = "o-" + j.date;
		let observation = $("<div>", {id: o_id, class: "day"});
		let o_date = new Date(j.date);
		observation.append(`<span class='d'>${o_date.getDate()} ${bill.text.mois[o_date.getMonth()]} </span>`);	
		observation.append(`<span class='j'>${j.pos}</span>`);
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
		if (j.premier_jour) {
			$("#ev_premier_jour").prop('checked', true);
			$("#ev_premier_jour").attr('initial', true);
		}
		else $("#ev_premier_jour").attr('initial', false);
		if (j.union_sex) $("#ev_union").prop('checked', true);
		if (j.jour_sommet) $("#ev_jour_sommet").prop('checked', true);
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
				if (bill.page_a_recharger) location.reload();
				bill.charger_observation(data.date);
				$("#jour_form").hide();
			}		
		}).fail(function (ret) {
			console.error(ret.responseText); 
			$("#form_err").val(ret.responseText);
		});
	}
}

