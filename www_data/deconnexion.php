<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "config.php";
require_once "lib/db.php";
require_once "lib/date.php";

setcookie("MONCYCLEAPP_JETTON", '', -1, '/');

header('Location: /');

echo "deconexion OK";
echo PHP_EOL;

