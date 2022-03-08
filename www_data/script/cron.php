<?php

require_once "../config.php";
require_once "../lib/db.php";
require_once "../lib/doc.php";
require_once "../lib/date.php";
require_once "../lib/mail.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../module/phpmailer/src/Exception.php';
require_once '../module/phpmailer/src/PHPMailer.php';
require_once '../module/phpmailer/src/SMTP.php';
require_once "../module/fpdf/fpdf.php";



header("Content-Type: text/plain");

echo ".............................................................................";
echo PHP_EOL;

echo "moncycle.app cron worker";
echo PHP_EOL;


$db = db_open();

$cycles = db_select_cycles_recent($db);

foreach($cycles as $cyc) {
	
	$debut_cycle = db_select_cycle($db, $cyc["cycle_complet"], $cyc["no_compte"])[0]["cycle"];
	$cycle_complet = db_select_cycle_complet($db, $debut_cycle,  $cyc["cycle_complet"], $cyc["no_compte"]);
	$cycle_complet = doc_ajout_jours_manquant($cycle_complet, $cyc["methode"]);
	
	$pdf = doc_cycle_vers_pdf ($cycle_complet, $cyc["methode"], $cyc["nom"]);



	$csv = fopen('php://memory','rw');
	doc_cycle_vers_csv ($csv, $cycle_complet, $cyc["methode"]);
	rewind($csv);

	$mail = mail_init();

	$mail->addAddress($cyc["email1"], $cyc["email1"]);     //Add a recipient
	if (!empty($cyc["email2"])) $mail->addAddress($cyc["email2"], $cyc["email2"]);

	$nb_j = count($cycle_complet);
	$dh = date_humain(new Datetime($cycle_complet[0]["date_obs"]));
	$fh = date_humain(new Datetime(end($cycle_complet)["date_obs"]));

	$mail->isHTML(true);
	$mail->Subject = "Cycle de $nb_j jours du $dh";
	$mail->Body    = "Bonjour {$cyc['nom']},<br />
		<br />
		Vous trouverez en PJ un export au format PDF et CSV de votre cycle du $dh au $fh d'une durée de $nb_j jours.<br />
		<br />
		A bientôt,<br />
		<br />
		<a href='https://www.moncycle.app' style='color: unset; text-decoration:none'>mon<span style='color: #1e824c;font-weight:bold'>cycle</span>.app</a><br />";

	$mail->AltBody = "Export de votre cycle du $dh au $fh de $nb_j jours.\n\nmoncycle.app";

	$mail->addStringAttachment($pdf->Output('', 'S'), 'moncycle_app_'. $debut_cycle . '.pdf');
	$mail->addStringAttachment(stream_get_contents($csv), 'moncycle_app_'. $debut_cycle . '.csv');

	$mail->send();

	fclose($csv);

	echo "cycle de $nb_j envoye a {$cyc["email1"]} (et {$cyc["email2"]})";
	echo PHP_EOL;

}

