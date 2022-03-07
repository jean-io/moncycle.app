<?php

require_once "../config.php";
require_once "../lib/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../phpmailer/src/Exception.php';
require_once '../phpmailer/src/PHPMailer.php';
require_once '../phpmailer/src/SMTP.php';



header("Content-Type: text/plain");

echo ".............................................................................";
echo PHP_EOL;

echo "moncycle.app cron worker";


$db = db_open();

$cycles = db_select_cycles_recent($db);

foreach($cycles as $cyc) {

	print_r($cyc);
	
	$debut_cycle = db_select_cycle($db, $cyc["cycle_complet"], $cyc["no_compte"])[0]["cycle"];
	$cycle_complet = db_select_cycle_complet($db, $debut_cycle,  $cyc["cycle_complet"], $cyc["no_compte"]);
	
	print_r($cycle_complet);
}

