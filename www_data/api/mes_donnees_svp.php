<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "../config.php";
require_once "../lib/date.php";
require_once "../lib/db.php";
require_once "../lib/doc.php";
require_once "../lib/sec.php";

require_once "../vendor/autoload.php";


$db = db_open();

$compte = sec_auth_jetton($db);
sec_redirect_non_connecte($compte);


$export_compte  = db_select_compte_par_nocompte($db, $compte["no_compte"]);
$export_obs     = db_select_all_observation($db, $compte["no_compte"]);
$export_jettons = db_select_tous_les_jetton($db, $compte["no_compte"]);

// print_r($export_jettons);

header("content-type:application/csv;charset=UTF-8");
header('Content-Disposition: attachment; filename="export_moncycle_app.csv"');

$out = fopen('php://output', 'w');
fputs($out, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

fputs($out,"Export des données MONCYCLE.APP de " . $compte["nom_compte"] . PHP_EOL);
fputs($out, PHP_EOL);

// export des informations du compte
foreach ($export_compte[0] as $key => $value) {
	fputs($out, $key . CSV_SEP);
}
fputs($out, PHP_EOL);
if (isset($export_compte[0]["motdepasse"])) $export_compte[0]["motdepasse"] = sec_offuscate_str($export_compte[0]["motdepasse"]);
if (isset($export_compte[0]["totp_secret"])) $export_compte[0]["totp_secret"] = sec_offuscate_str($export_compte[0]["totp_secret"]);
fputcsv($out, $export_compte[0], CSV_SEP);
fputs($out, PHP_EOL);

// exports des jettons
if (isset($export_jettons[0])) {
	foreach ($export_jettons[0] as $key => $value) {
		fputs($out, $key . CSV_SEP);
	}
	fputs($out, PHP_EOL);
	
	foreach ($export_jettons as $key => $value) {
		if (isset($value["jetton_str"])) $value["jetton_str"] = sec_offuscate_str($value["jetton_str"]);
		fputcsv($out, $value, CSV_SEP);
	}
	
	fputs($out, PHP_EOL);
}

// exports des observations
if (isset($export_obs[0])) {
	foreach ($export_obs[0] as $key => $value) {
		fputs($out, $key . CSV_SEP);
	}
	fputs($out, PHP_EOL);
	
	foreach ($export_obs as $key => $value) {
		fputcsv($out, $value, CSV_SEP);
	}
	
	fputs($out, PHP_EOL);
}

fclose($out);
