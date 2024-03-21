$(document).ready(() => {
	
	var p_left = 0;
	var p_top = 0;

	function calcul_point_depart () {

		// récupération de la positon de l'image	
		p_left = $("#img_couple").first().position().left;
		p_top = $(".img_container").first().position().top;

		i_width = $("#img_couple").width();
		i_height = $("#img_couple").height();

		// calcul du point de départ de l'animation
		p_left += i_width*0.88-40;
		p_top  += i_height*0.28;	

	}

	calcul_point_depart();
	$(window).resize(calcul_point_depart);
	setInterval(calcul_point_depart, 1000);

	// DEBUG: affichage du point de départ
	/*var div_debug = document.createElement("div");
	$(div_debug).css({
		"position": "fixed",
		"z-index": "100",
		"background-color": "red",
		"top": p_top + "px",
		"left": p_left + "px",
		"width": "5px",
		"height": "5px"
	});
	$("body").append(div_debug);*/

	const emojis = ["&#10084;&#65039;", "&#128994;", "&#x1f476;", "&#x1f534;", "&#127777;&#65039;", "&#128993;", "&#127956;&#65039;"];

	// boucle/interval de création des emojis
	var i = 0;
	var queu = [];
	setInterval(function () {

		// mélange des emoticones
		if (queu.length == 0) {
			queu = emojis.slice();
			queu.sort(() => Math.random() - 0.5);
		} 

		// creation d'un emoticone
		var emo = document.createElement("div");
		$(emo).html(queu.pop());
		$(emo).addClass("emo_fume");
		$(emo).attr('id', 'emo_id' + i);
		$(emo).css({
			"top": p_top + "px",
			"left": p_left + "px"
		});
		$("body").append(emo);
	
		// suppression des vieux emoticones	
		$('#emo_id' + (i-10)).remove();
		i += 1;
	}, 1500);

	// affichage de l'addresse mail
	var contact_mail = ['b','o','n','j','o','u','r','@','m','o','n','c','y','c','l','e','.','a','p','p'];
	document.getElementById("contact_mail").innerHTML = contact_mail.join('');
	document.getElementById("contact_mail").href = "mailto:" + contact_mail.join('');
	
	// gestion des captures d'écran
	$(".mini_img").click(function (event) {
		event.preventDefault();
		$("#grosse_img").attr('src', $(this).attr('href'));
		$(".mini_img").removeClass("active");
		$(this).addClass("active");
	});


	// telechargement des dernières stats
	$.ajax({url: "https://tableau.moncycle.app/api/pub_stat", success: function(result){
		$("#stat_compte").text(result.moncycle_app_nb_compte);
		$("#stat_cycle").text(result.moncycle_app_nb_cycle);
		$("#stat_obs").text(result.moncycle_app_nb_total_observation);
		console.log(result);
	}});
});
