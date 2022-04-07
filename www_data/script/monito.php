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

header("Content-Type: text/plain");

session_start();
session_gc();
session_destroy();

$db = db_open();

echo boolval(db_select_nb_compte($db)[0][0]) ? "oookkk" : "kkkooo";
echo PHP_EOL;

