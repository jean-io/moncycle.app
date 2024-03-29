<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "../vendor/autoload.php";

require_once "../config.php";
require_once "../lib/db.php";
require_once "../lib/date.php";
require_once "../lib/sec.php";

use OTPHP\TOTP;

header('Content-Type: application/json');

$output = "";
$jetton = "";
$no_compte = 0;

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
			$output .= "Compte désactivé. Contactez-nous pour plus d'informations.";
		}
		elseif (isset($compte["motdepasse"]) && password_verify($_POST["mdp"], $compte["motdepasse"])) {
			unset($compte["motdepasse"]);
			unset($_POST["mdp"]);

			$usr_totp_code = 0;
			if (isset($_POST["code"]) && strlen($_POST["code"])>0) $usr_totp_code = intval(preg_replace('/\s+/','',$_POST["code"]));

			if ($compte["totp_etat"] != TOTP_STATE_ACTIVE) {
				$jetton = sec_auth_succes($db, $compte);
				$output .= "Connecté!";
				$no_compte = $compte["no_compte"];
			}
			elseif ($usr_totp_code>0 && (TOTP::createFromSecret($compte["totp_secret"]))->verify($usr_totp_code)) {
				unset($compte["totp_secret"]);
				unset($_POST["code"]);

				$jetton = sec_auth_succes($db, $compte);
				$output .= "Connecté! (avec TOTP)";
				$no_compte = $compte["no_compte"];
			}
			else {
				db_update_co_echoue($db, $_POST["email1"]);
				$output .= "Bon mot de passe mais code à usage unique mauvais ou manquant.";
			}

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
	"no_compte" => $no_compte,
	"jetton" => $jetton,
	"message" => $output
]);

