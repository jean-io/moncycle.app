var moncycle_app_usr = {};

$(document).ready(function(){

	// TELECHARGEMENT DES DONNES DES UTILISATEUR
	$.get("api/constante", {}).done(function(data) {
		moncycle_app_usr = data;
		$("#nom").text(moncycle_app_usr.nom);
		$("#i_prenom").val(moncycle_app_usr.nom);
		if(moncycle_app_usr.donateur) $("#merci_don").show();
		if(moncycle_app_usr.id_utilisateur == 2) $("#warning_demo").show();
		$("#i_email1").val(moncycle_app_usr.email1);
		$("#i_email2").val(moncycle_app_usr.email2);
		$(`#m_${moncycle_app_usr.methode}`).attr("checked", "");
		const cette_annee = (new Date()).getFullYear();
		for (let y = (cette_annee - cette_annee%5)-75; y < cette_annee-5; y += 5) {
			var selected = ""
			if (y==moncycle_app_usr.age) selected = 'selected';
			$("#i_anaissance").append(`<option ${selected} value="${y}">entre ${y} et ${y+4}</option>`);
		}
	}).fail(function (err) {
		if (err.status == 401 || err.status == 403 || err.status == 407) {	
			window.localStorage.clear();
			window.location.replace('/connexion');
		}
	});

	// MISE A JOURS DES PARAMETTRE DU COMPTE
	$(".auto_save").on("keyup change", function() {
		$("#net_stat").text('⏳');
		$.post("../api/param", `${$(this).attr('name')}=${this.value}`).fail(function(data){
			console.error(data);
			$("#net_stat").text('❌ erreur');
			$("#net_stat").addClass('rouge');
			$("#net_stat").removeClass('vert');
		}).done(function(data){
			if(data.hasOwnProperty("nom")) {
				$("#nom").text(data.nom);
			}
			$("#net_stat").text('✅ enregistré');
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
		$.get("api/totp?init", {}).done(function(data) {
			$("#i_activate_otp").hide();
			$("#totp_miseenpalce").show();
			$("#i_totp_secret").val(data.init_secret);
			$("#totp_secret").text(data.init_secret);
		});
	});
	$("#f_totp_validation").on("submit", function(event) {
		event.preventDefault();
		var form_data = $("#f_totp_validation").serializeArray();
		$.post("../api/totp?activation", $.param(form_data)).done(function(ret){
			console.log(ret);
		}).fail(function(err) {
			console.error(err);
		});
	});
});
