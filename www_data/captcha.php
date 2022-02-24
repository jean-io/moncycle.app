<?php

session_start();

$captcha_num = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghijkmnpqrstuvwxyz';
$captcha_num = substr(str_shuffle($captcha_num), 0, 6);
$_SESSION["captcha"] = $captcha_num;


$font_size = 20;
$img_width = 150;
$img_height = 40;


header('Content-type: image/jpeg');


$image = imagecreate($img_width, $img_height);
imagecolorallocate($image, 30, 130, 76); // couleur de fond
$text_color = imagecolorallocate($image, 255, 255, 255); // couleur du texte

imagettftext($image, $font_size, 0, 15, 30, $text_color, 'font/montserrat/static/Montserrat-Regular.ttf', $captcha_num);
imagejpeg($image);

