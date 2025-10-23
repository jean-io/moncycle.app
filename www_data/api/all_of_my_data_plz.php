<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/moncycle-app/backend-api-web-app
*/

require_once "../config.php";
require_once "../lib/date.php";
require_once "../lib/db.php";
require_once "../lib/doc.php";
require_once "../lib/sec.php";

require_once "../vendor/autoload.php";


$db = db_open();

$user_account = sec_auth_token($db);
sec_redirect_non_connecte($user_account);


$export_user_account  = db_select_user_account_par_nouser_account($db, $user_account["no_user_account"]);
$export_obs     = db_select_all_day_timeline($db, $user_account["no_user_account"]);
$export_auth_tokens = db_select_tous_les_auth_token($db, $user_account["no_user_account"]);

// print_r($export_auth_tokens);

header("content-type:application/csv;charset=UTF-8");
header('Content-Disposition: attachment; filename="export_moncycle_app.csv"');

$out = fopen('php://output', 'w');
fputs($out, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

fputs($out,"Export des données MONCYCLE.APP de " . $user_account["name_user_account"] . PHP_EOL);
fputs($out, PHP_EOL);

// export des informations du user_account
foreach ($export_user_account[0] as $key => $value) {
	fputs($out, $key . CSV_SEP);
}
fputs($out, PHP_EOL);
if (isset($export_user_account[0]["password"])) $export_user_account[0]["password"] = sec_offuscate_str($export_user_account[0]["password"]);
if (isset($export_user_account[0]["totp_secret"])) $export_user_account[0]["totp_secret"] = sec_offuscate_str($export_user_account[0]["totp_secret"]);
fputcsv($out, $export_user_account[0], CSV_SEP);
fputs($out, PHP_EOL);

// exports des auth_tokens
if (isset($export_auth_tokens[0])) {
	foreach ($export_auth_tokens[0] as $key => $value) {
		fputs($out, $key . CSV_SEP);
	}
	fputs($out, PHP_EOL);
	
	foreach ($export_auth_tokens as $key => $value) {
		if (isset($value["auth_token_str"])) $value["auth_token_str"] = sec_offuscate_str($value["auth_token_str"]);
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
