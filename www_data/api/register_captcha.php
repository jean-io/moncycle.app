<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/moncycle-app/backend-api-web-app
*/

require_once "../vendor/autoload.php";
require_once "../config.php";
require_once "../lib/db.php";

use Gregwar\Captcha\CaptchaBuilder;

$captcha = new CaptchaBuilder;
$captcha->build();

$db = db_open();

header('Content-type: image/jpeg');

if (isset($_COOKIE["MONCYCLEAPP_TOKEN"]) && strlen($_COOKIE["MONCYCLEAPP_TOKEN"])>0) {
	$db_ret = db_select_auth_token_captcha($db, $_COOKIE["MONCYCLEAPP_TOKEN"]);
	if (isset($db_ret[0]["no_auth_token"])) db_update_auth_token_captcha($db, $_COOKIE["MONCYCLEAPP_TOKEN"], $captcha->getPhrase());
	$captcha->output();
}
