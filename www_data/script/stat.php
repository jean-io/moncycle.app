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

echo "moncycle_app_nb_compte ";
echo db_select_nb_compte($db)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_compte_actif ";
echo db_select_nb_compte_actif($db)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_compte_recent ";
echo db_select_nb_compte_recent($db)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_cycle ";
echo db_select_nb_cycle($db)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_cycle_recent ";
echo db_select_nb_cycle_recent($db)[0][0];
echo PHP_EOL;

echo "moncycle_app_age_moyen ";
echo db_select_age_moyen($db)[0][0];
echo PHP_EOL;

echo "moncycle_app_age_moyen_recent ";
echo db_select_age_moyen_recent($db)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_observation_aujourdhui ";
echo db_select_observation_aujourdhui($db)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_observation_1j ";
echo db_select_observation_count($db, 1)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_observation_5j ";
echo db_select_observation_count($db, 5)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_observation_15j ";
echo db_select_observation_count($db, 15)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_session ";
echo count(glob(session_save_path() . '/*'));
echo PHP_EOL;
