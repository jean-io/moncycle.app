<?php

require_once "config.php";
require_once "lib/db.php";

header("Content-Type: text/plain");

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

echo "moncycle_app_nb_observation_24h ";
echo db_select_observation_count($db, 1)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_observation_5j ";
echo db_select_observation_count($db, 5)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_observation_15j ";
echo db_select_observation_count($db, 15)[0][0];
echo PHP_EOL;
