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
		sommet : "⛰️",
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
			let nb_j_sommet = [1, 2, 3, 15, $(`#o-${s}`).parent()[0].children.length - $(`#o-${s}`).index() - 2];
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
	cycle2html : function (c) {
		let c_id = "c-" + c;
		let cycle = $("<div>", {id: c_id, class: "cycle"});
		let c_date = new Date(c);
		cycle.append(`<h2 class='titre'><a href='export.php?cycle=${c_date.toISOString()}&type=pdf'>PDF</a><a href='export.php?cycle=${c_date.toISOString()}&type=csv'>CSV</a>Cycle de <span class='nb_jours'>1</span>j du ${c_date.getDate()} ${bill.text.mois[c_date.getMonth()]} <span class='cycle_fin'></span></h2>`);
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

