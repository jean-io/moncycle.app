<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "../config.php";
require_once "../lib/db.php";
require_once "../lib/date.php";

session_start();

header('Content-Type: application/json');

$output = "";

try {

	if (isset($_SESSION["connected"]) && $_SESSION["connected"]) {
		$output .= "Déja connecté.";
	}


	elseif (isset($_POST["email1"]) && isset($_POST["mdp"]) && filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) {

		$db = db_open();

		$compte = db_select_compte_par_mail($db, $_POST["email1"])[0] ?? [];

		if (isset($compte["nb_co_echoue"]) && intval($compte["nb_co_echoue"])>=5) sleep(5);
		elseif (!isset($compte["nb_co_echoue"]) && rand(0,5)==0) sleep(5);

		if (!CONNEXION_COMPTE) $output .= "Les connexions aux comptes sont désactivées. Veuillez nous excuser pour ce désagrément.";
		elseif (empty($_POST["email1"]) || empty($_POST["mdp"])) {
			$output .= "E-mail et/ou mot de passe manquant.";
		}
		elseif (isset($compte["actif"]) && !boolval($compte["actif"])) {
			$output .= "Compte désactivé. Contactez nous pour plus d'informations.";
		}		
		elseif (isset($compte["motdepasse"]) && password_verify($_POST["mdp"], $compte["motdepasse"])) {
			$output .= "Connecté!";
		
			unset($compte["motdepasse"]);
			$_SESSION["connected"] = true;
			$_SESSION["compte"] = $compte;
			$_SESSION["no"] = intval($compte["no_compte"] ?? -1);
			$_SESSION["sess_refresh"] = date_sql(new DateTime());

			db_update_compte_connecte($db, $_SESSION["no"]);

			exit;
		}
		else {
			db_update_co_echoue($db, $_POST["email1"]);
			$output .= "Mauvais mot de passe ou compte inexistant.";
		}
	
	}
	

	else {
		$output .= "Données manquantes.";
	}

}
catch (Exception $e){
	
	$output .= $e->getMessage();

}

echo json_encode([
	"auth" => (isset($_SESSION["connected"]) && $_SESSION["connected"] == true),
	"message" => $output
]);

