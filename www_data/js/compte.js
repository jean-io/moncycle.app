var moncycle_app_usr = {};

const TOTP_STATE_NEVER_USED = 0;
const TOTP_STATE_DISABLED = 1;
const TOTP_STATE_INIT = 2;
const TOTP_STATE_ACTIVE = 3;

$(document).ready(function(){

	// TELECHARGEMENT DES DONNES DES UTILISATEUR
	$.get("api/constante", {}).done(function(data) {
		moncycle_app_usr = data;
		$("#f_info_pref")[0].reset();
		$("#nom").text(moncycle_app_usr.nom);
		document.title = "moncycle.app - compte " + moncycle_app_usr.nom;
		$("#i_prenom").val(moncycle_app_usr.nom);
		$("#tech_info_no").text(moncycle_app_usr.id_utilisateur);
		if(moncycle_app_usr.donateur) $("#merci_don").show();
		if(moncycle_app_usr.id_utilisateur == 2 || moncycle_app_usr.id_utilisateur == 3) $("#warning_demo").show();
		$("#tech_info_id").text(moncycle_app_usr.email1);
		$("#i_email1").val(moncycle_app_usr.email1);
		$("#i_email2").val(moncycle_app_usr.email2);
		$(`#m_${moncycle_app_usr.methode}`).attr("checked", "");
		if (moncycle_app_usr.recherche) $("#i_recherche").prop('checked', true);
		if (moncycle_app_usr.timeline_asc) $("#i_timeline_asc").prop('checked', true);
		let d = new Date(moncycle_app_usr.date_inscription);
		let m = d.getMonth()+1;
		let j = d.getDate();
		$("#tech_info_insc").text([j<10 ? "0"+j : j, m<10 ? "0"+m : m, d.getFullYear()].join("/"));
		const cette_annee = (new Date()).getFullYear();
		for (let y = (cette_annee - cette_annee%5)-75; y < cette_annee-5; y += 5) {
			var selected = ""
			if (y==moncycle_app_usr.age) selected = 'selected';
			$("#i_anaissance").append(`<option ${selected} value="${y}">entre ${y} et ${y+4}</option>`);
		}
		if (moncycle_app_usr.totp_actif < 3) $("#totp_explications").show();
		else $("#totp_actif").show();
	}).fail(function (err) {
		if (err.status == 401 || err.status == 403 || err.status == 407) {	
			window.localStorage.clear();
			window.location.replace('/connexion');
		}
	});
	$.get("api/version", {}).done(function(data) {
		$("#tech_info_ver").text(data.version ?? "?");
	}).fail(function (err) {
		if (err.status == 401 || err.status == 403 || err.status == 407) {	
			window.localStorage.clear();
			window.location.replace('/connexion');
		}
	});

	// MISE A JOURS DES PARAMETTRE DU COMPTE
	$(".auto_save").on("keyup change", function() {
		$("#net_stat").text('⏳');
		localStorage.timeline_asc = $("#i_timeline_asc").prop('checked');
		let val = this.value;
		if ($(this)[0].type=="checkbox") val = $(this)[0].checked ? 1 : 0;
		$.post("../api/compte", `${$(this).attr('name')}=${val}`).fail(function(data){
			console.error(data);
			$("#net_stat").html(' ❌&nbsp;erreur');
			$("#net_stat").addClass('rouge');
			$("#net_stat").removeClass('vert');
		}).done(function(data){
			if(data.hasOwnProperty("nom")) {
				$("#nom").text(data.nom);
			}
			$("#net_stat").html(' ✅&nbsp;enregistré');
			$("#net_stat").addClass('vert');
			$("#net_stat").removeClass('rouge');
		});
	});

	// CHANGEMENT DU MOT DE PASSE
	$("#form_mdp_change").on("submit", function(event) {
		event.preventDefault();
		$("#mdp_change_ok").text('');
		$("#mdp_ret_msg").text('');
		if ($("#i_mdp1").val() != $("#i_mdp2").val()) {
			$("#mdp_ret_msg").html("❌ <b>erreur:</b> le nouveau mot de passe et sa confirmation ne sont pas identiques.");
			return;
		}
		$("#but_mdp_change").prop("disabled", true);
		var form_data = $("#form_mdp_change").serializeArray();
		$.post("../api/mdp_change", $.param(form_data)).done(function(ret){
			$("#but_mdp_change").prop("disabled", false);
			if (ret.change_ok) {
				$("#form_mdp_change input[type=password]").val('');
				$("#mdp_change_ok").text('✅ enregistré');
			}
			else {
				$("#mdp_ret_msg").html(`❌ <b>erreur:</b> ${ret.msg}.`);
			}
		}).fail(function(ret){
			console.error(ret);
			$("#but_mdp_change").prop("disabled", false);
		});
	});

	// TOTP AUTH MULTI FACTEUR
	$("#i_activate_otp").on("click", function(event) {
		$("#i_activate_otp").prop("disabled", true);
		$("#totp_err_msg").html("");
		$.get("api/totp", {}).done(function(data) {
			$("#i_activate_otp").prop("disabled", false);
			$("#totp_explications").hide();
			$("#totp_miseenpalce").show();
			$("#totp_auto_conf").attr("href", data.otpauth);
			$("#totp_qrcode").html(data.qrcode);
			$("#totp_copy_secret").on("click", function(event) {
				navigator.clipboard.writeText(data.init_secret).then(function () {
					$("#totp_copy_secret_ok").text("✅ copié");
				}, function () {
					alert('Failure to copy. Check permissions for clipboard');
				});				
			});
		});
	});

	$("#f_totp_validation").on("submit", function(event) {
		event.preventDefault();
		$("#totp_err_msg").html("");
		var form_data = $("#f_totp_validation").serializeArray();
		$("#f_totp_validation").trigger("reset");
		$.post("../api/totp", $.param(form_data)).done(function(ret){
			if (ret.totp_actif == TOTP_STATE_ACTIVE) {
				$("#totp_miseenpalce").hide();
				$("#totp_actif").show();
			}
			else {
				$("#totp_err_msg").html("<b>❌&nbsp;erreur:</b> " + ret.msg);
			}
		}).fail(function(err) {
			console.error(err);
		});
	});

	$("#f_totp_desac").on("submit", function(event) {
		event.preventDefault();
		$("#totp_err_msg").html("");
		var form_data = $("#f_totp_desac").serializeArray();
		$("#f_totp_desac").trigger("reset");
		$.ajax({type : 'DELETE', "url" : "../api/totp?desactivation", "data" : $.param(form_data)}).done(function(ret){
			if (ret.totp_actif == TOTP_STATE_DISABLED) {
				$("#totp_explications").show();
				$("#totp_actif").hide();
			}
			else {
				$("#totp_err_msg").html("<b>❌&nbsp;erreur:</b> " + ret.msg);
			}
		}).fail(function(err) {
			console.error(err);
		});
	});

	// EXPORT PDF
	$("#i_start_date").attr("max", new Date().toISOString().substr(0, 10));
	$("#i_end_date").attr("max", new Date().toISOString().substr(0, 10));
	$("#f_export_complexe").on("submit", function(event) {
		event.preventDefault();
		var start_date = $("#i_start_date").val();
		var end_date = $("#i_end_date").val();
		if (start_date == "" || end_date == "") $("#export_err").html("<br /><b>❌&nbsp;erreur:</b> dates vides.");
		else if (new Date(start_date) >= new Date(end_date)) $("#export_err").html("<br /><b>❌&nbsp;erreur:</b> la 1ère date doit être antérieur à la 2ème.");
		else window.location.replace(`api/export?type=pdf&start_date=${start_date}&end_date=${end_date}`);
	});

	// SUPPRESSION DU COMPTE
	$("#f_suppr_compte").on("submit", function(event) {
		event.preventDefault();
		var form_data = $("#f_suppr_compte").serializeArray();
		if (!confirm(moncycle_app_usr.nom + ', êtes-vous sur de vouloir supprimer votre compte ainsi que toutes vos données? Cette action est irréversible. 😟')) return;
		$.ajax({type : 'DELETE', "url" : "../api/compte", "data" : $.param(form_data)}).done(function(ret){
			if (ret.suppr) {
				window.localStorage.clear();
				alert(moncycle_app_usr.nom + ", votre compte a bien été supprimé. 😢💔");
				window.location.replace('connexion');
			}
			else alert(moncycle_app_usr.nom + ", votre compte n'a pas été supprimé: " + ret.msg);
		}).fail(function(err) {
			console.error(err);
		});
	});

});
