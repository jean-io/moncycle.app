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
require_once "../lib/sec.php";

header('Content-Type: application/json');

$output = "";
$jetton = "";

try {

	if (isset($_POST["email1"]) && isset($_POST["mdp"]) && filter_var($_POST["email1"], FILTER_VALIDATE_EMAIL)) {

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
			unset($compte["motdepasse"]);
			unset($_POST["mdp"]);

			$jetton = sec_motdepasse_aleatoire(256);

			db_insert_jetton($db, $compte["no_compte"], $_POST["appareil"] ?? $_SERVER['HTTP_USER_AGENT'], "FR", $jetton);	
			db_update_compte_connecte($db, $compte["no_compte"]);

			$arr_cookie_options = array (
				'expires' => strtotime('+5 years'), 
				'path' => '/',
				'secure' => true,
				'httponly' => true,
			);
			setcookie("MONCYCLEAPP_JETTON", $jetton, $arr_cookie_options);

			$output .= "Connecté!";
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
	"auth" => $jetton!='',
	"jetton" => $jetton,
	"message" => $output
]);

