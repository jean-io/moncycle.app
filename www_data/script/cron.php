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
require_once "../lib/doc.php";
require_once "../lib/date.php";
require_once "../lib/mail.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/phpmailer/phpmailer/src/Exception.php';
require_once '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once "../vendor/fpdf/fpdf/src/Fpdf/Fpdf.php";



header("Content-Type: text/plain");

echo ".............................................................................";
echo PHP_EOL;

echo "moncycle.app cron worker";
echo PHP_EOL;


$db = db_open();

// ENVOIE DES MAILS CYCLES TERMINE

$cycles = db_select_cycles_recent($db);

foreach($cycles as $cyc) {
	
	$debut_cycle = db_select_cycle($db, $cyc["cycle_complet"], $cyc["no_compte"]);
	
	if(!empty($debut_cycle)) {

		$debut_cycle = $debut_cycle[0]["cycle"];
		$cycle_complet = db_select_cycle_complet($db, $debut_cycle,  $cyc["cycle_complet"], $cyc["no_compte"]);
		$cycle_complet = doc_ajout_jours_manquant($cycle_complet, $cyc["methode"]);

		$nb_j = count($cycle_complet);
		
		if ($nb_j>=5) {

			$pdf = doc_cycle_vers_pdf ($cycle_complet, $cyc["methode"], $cyc["nom"]);

			$csv = fopen('php://memory','rw');
			doc_cycle_vers_csv ($csv, $cycle_complet, $cyc["methode"]);
			rewind($csv);

			$mail = mail_init();

			$mail->addAddress($cyc["email1"], $cyc["email1"]);
			if (!empty($cyc["email2"])) $mail->addAddress($cyc["email2"], $cyc["email2"]);

			$dh = date_humain(new Datetime($cycle_complet[0]["date_obs"]));
			$fh = date_humain(new Datetime(end($cycle_complet)["date_obs"]));

			$mail->isHTML(true);
			$mail->Subject = "Cycle de $nb_j jours du $dh";
			$mail->Body = mail_body_cycle($cyc['nom'], $dh, $fh, $nb_j);
			$mail->AltBody = "Export de votre cycle du $dh au $fh de $nb_j jours.\n\nmoncycle.app";

			$mail->addStringAttachment($pdf->Output('', 'S'), 'moncycle_app_'. $debut_cycle . '.pdf');
			$mail->addStringAttachment(stream_get_contents($csv), 'moncycle_app_'. $debut_cycle . '.csv');

			$mail->send();

			fclose($csv);

			echo "cycle de $nb_j jours envoyé à {$cyc["email1"]} (et {$cyc["email2"]}).";
			echo PHP_EOL;
		}
	}
}

// RELANCE COMPTES INACTIF

$compte = db_select_compte_inactif($db);

foreach($compte as $com) {

	$mail = mail_init();

	$mail->addAddress($com["email1"], $com["email1"]);
	if (!empty($com["email2"])) $mail->addAddress($com["email2"], $com["email2"]);

	$mail->isHTML(true);
	$mail->Subject = "Comment allez-vous?";
	$mail->Body = mail_body_relance($com["nom"], $com["email1"]);
	$mail->AltBody = "Cela fait longtemps que l'on ne vous a pas vu sur moncycle.app, tout va bien?";

	$mail->send();

	db_update_relance($db, $com["no_compte"], 1);

	echo "relance envoyée à {$com["email1"]} (et {$com["email2"]})";
	echo PHP_EOL;
}

// SUPPR DES JETTONS EXPIRES

$ret = db_delete_vieux_jetton($db);
echo $ret . " vieux jettons supprimés";
echo PHP_EOL;

// RESET DES COMPTEURS DE STAT

db_update_reset_stats($db, "pub_visite_jour");
echo "stats du jour réinitialisées";

$auj = getdate();

if ($auj["wday"]==0) {
	db_update_reset_stats($db, "pub_visite_hebdo");
	echo ", stats de la semaine réinitialisées";
}

if ($auj["mday"]==1) {
	db_update_reset_stats($db, "pub_visite_mensuel");
	echo ", stats du mois réinitialisées";
}

echo PHP_EOL;
