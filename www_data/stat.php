<?php

require_once "config.php";

header("Content-Type: text/plain");

$db = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_ID, DB_PASSWORD);



echo "moncycle_app_nb_compte ";
$sql = "select count(no_compte) as MONCYCLE_APP_NB_COMPTE from compte";
$statement = $db->prepare($sql);
$statement->execute();
echo $statement->fetchAll(PDO::FETCH_NUM)[0][0];
echo PHP_EOL;


echo "moncycle_app_nb_compte_actif ";
$sql = "select count(distinct no_compte) as MONCYCLE_APP_NB_COMPTE_ACTIF from observation where date_obs >= DATE(NOW()) - INTERVAL 15 DAY and no_compte!=2";
$statement = $db->prepare($sql);
$statement->execute();
echo $statement->fetchAll(PDO::FETCH_NUM)[0][0];
echo PHP_EOL;



echo "moncycle_app_nb_compte_recent ";
$sql = "select count(no_compte) as MONCYCLE_APP_NB_COMPTE_RECENT from compte where inscription_date >= DATE(NOW()) - INTERVAL 15 DAY and derniere_co_date is not null and no_compte!=2";
$statement = $db->prepare($sql);
$statement->execute();
echo $statement->fetchAll(PDO::FETCH_NUM)[0][0];
echo PHP_EOL;



echo "moncycle_app_nb_cycle ";
$sql = "select count(no_observation) as MONCYCLE_APP_NB_CYCLE from observation where premier_jour=1 and no_compte!=2";
$statement = $db->prepare($sql);
$statement->execute();
echo $statement->fetchAll(PDO::FETCH_NUM)[0][0];
echo PHP_EOL;



echo "moncycle_app_nb_cycle_recent ";
$sql = "select count(no_observation) as MONCYCLE_APP_NB_CYCLE_RECENT from observation where premier_jour=1 and date_obs>= DATE(NOW()) - INTERVAL 30 DAY and no_compte!=2";
$statement = $db->prepare($sql);
$statement->execute();
echo $statement->fetchAll(PDO::FETCH_NUM)[0][0];
echo PHP_EOL;



echo "moncycle_app_age_moyen ";
$sql = "select year(now())-avg(age)+2.5 as MONCYCLE_APP_NB_AGE_MOYEN from compte";
$statement = $db->prepare($sql);
$statement->execute();
echo $statement->fetchAll(PDO::FETCH_NUM)[0][0];
echo PHP_EOL;



echo "moncycle_app_age_moyen_recent ";
$sql = "select year(now())-avg(age)+2.5 as MONCYCLE_APP_NB_AGE_MOYEN_RECENT from compte where inscription_date >= DATE(NOW()) - INTERVAL 15 DAY and derniere_co_date is not null and no_compte!=2";
$statement = $db->prepare($sql);
$statement->execute();
echo $statement->fetchAll(PDO::FETCH_NUM)[0][0];
echo PHP_EOL;

