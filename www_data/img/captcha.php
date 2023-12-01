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
require_once "../lib/sec.php";

$db = db_open();

$captcha_num = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghijkmnpqrstuvwxyz';
$captcha_num = substr(str_shuffle($captcha_num), 0, 6);

if (isset($_COOKIE["MONCYCLEAPP_JETTON"]) && strlen($_COOKIE["MONCYCLEAPP_JETTON"])>0) db_update_jetton_captcha($db, $_COOKIE["MONCYCLEAPP_JETTON"], $captcha_num);

$font_size = 20;
$img_width = 150;
$img_height = 40;


header('Content-type: image/jpeg');


$image = imagecreate($img_width, $img_height);
imagecolorallocate($image, 30, 130, 76); // couleur de fond
$text_color = imagecolorallocate($image, 255, 255, 255); // couleur du texte

imagettftext($image, $font_size, 0, 15, 30, $text_color, '../font/montserrat/static/Montserrat-Regular.ttf', $captcha_num);
imagejpeg($image);

