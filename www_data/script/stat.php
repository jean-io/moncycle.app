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

$db = db_open();

$nb_compte = db_select_nb_compte($db)[0][0];
echo "moncycle_app_nb_compte ";
echo $nb_compte;
echo PHP_EOL;

echo "moncycle_app_nb_session ";
echo db_select_jetton_compte($db)[0][0];
echo PHP_EOL;

echo "moncycle_app_visite_mensuel ";
echo db_select_cle_valeur($db, "pub_visite_mensuel")[0][0];
echo PHP_EOL;

echo "moncycle_app_visite_hebdo ";
echo db_select_cle_valeur($db, "pub_visite_hebdo")[0][0];
echo PHP_EOL;

echo "moncycle_app_visite_jour ";
echo db_select_cle_valeur($db, "pub_visite_jour")[0][0];
echo PHP_EOL;

$nb_compte_actif = db_select_nb_compte_actif($db)[0][0];
echo "moncycle_app_nb_compte_actif ";
echo $nb_compte_actif;
echo PHP_EOL;

echo "moncycle_app_pc_compte_actif ";
echo round(($nb_compte_actif/$nb_compte)*100,1);
echo PHP_EOL;

echo "moncycle_app_nb_compte_avec_totp ";
echo db_select_compte_avec_totp($db)[0][0];
echo PHP_EOL;

if ($nb_compte_actif<=0) exit;

$nb_compte_actif_billings = db_select_nb_compte_actif_par_methode($db, 2)[0][0];
echo "moncycle_app_nb_compte_actif_billings ";
echo $nb_compte_actif_billings;
echo PHP_EOL;
echo "moncycle_app_pc_compte_actif_billings ";
echo round(($nb_compte_actif_billings/$nb_compte_actif)*100,1);
echo PHP_EOL;

$nb_compte_actif_billings_temp = db_select_nb_compte_actif_par_methode($db, 1)[0][0];
echo "moncycle_app_nb_compte_actif_billings_temp ";
echo $nb_compte_actif_billings_temp;
echo PHP_EOL;
echo "moncycle_app_pc_compte_actif_billings_temp ";
echo round(($nb_compte_actif_billings_temp/$nb_compte_actif)*100,1);
echo PHP_EOL;

$nb_compte_actif_fertilitycare = db_select_nb_compte_actif_par_methode($db, 3)[0][0];
echo "moncycle_app_nb_compte_actif_fertilitycare ";
echo $nb_compte_actif_fertilitycare;
echo PHP_EOL;
echo "moncycle_app_pc_compte_actif_fertilitycare ";
echo round(($nb_compte_actif_fertilitycare/$nb_compte_actif)*100);
echo PHP_EOL;

$nb_compte_actif_fertilitycare_temp = db_select_nb_compte_actif_par_methode($db, 4)[0][0];
echo "moncycle_app_nb_compte_actif_fertilitycare_temp ";
echo $nb_compte_actif_fertilitycare_temp;
echo PHP_EOL;
echo "moncycle_app_pc_compte_actif_fertilitycare_temp ";
echo round(($nb_compte_actif_fertilitycare_temp/$nb_compte_actif)*100);
echo PHP_EOL;

echo "moncycle_app_nb_compte_recent ";
echo round(db_select_nb_compte_recent($db)[0][0]);
echo PHP_EOL;

echo "moncycle_app_nb_cycle ";
echo db_select_nb_cycle($db)[0][0];
echo PHP_EOL;

echo "moncycle_app_nb_cycle_recent ";
echo db_select_nb_cycle_recent($db)[0][0];
echo PHP_EOL;

echo "moncycle_app_age_moyen ";
echo round(db_select_age_moyen($db)[0][0],1);
echo PHP_EOL;

echo "moncycle_app_age_moyen_recent ";
echo round(db_select_age_moyen_recent($db)[0][0] ?? 0,1);
echo PHP_EOL;

echo "moncycle_app_nb_total_observation ";
echo db_select_total_observation_count($db)[0][0];
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

echo "moncycle_app_nb_observation_30j ";
echo db_select_observation_count($db, 30)[0][0];
echo PHP_EOL;

