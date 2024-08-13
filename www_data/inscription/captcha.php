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

use Gregwar\Captcha\CaptchaBuilder;

$captcha = new CaptchaBuilder;
$captcha->build();

$db = db_open();

header('Content-type: image/jpeg');

if (isset($_COOKIE["MONCYCLEAPP_JETTON"]) && strlen($_COOKIE["MONCYCLEAPP_JETTON"])>0) {
	$db_ret = db_select_jetton_captcha($db, $_COOKIE["MONCYCLEAPP_JETTON"]);
	if (isset($db_ret[0]["no_jetton"])) db_update_jetton_captcha($db, $_COOKIE["MONCYCLEAPP_JETTON"], $captcha->getPhrase());
	$captcha->output();
}
