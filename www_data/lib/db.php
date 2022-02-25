<?php

function db_open() {
	return new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_ID, DB_PASSWORD);
}

function db_select_cycles($db, $no_compte) {
	$sql = "SELECT date_obs AS cycles FROM observation WHERE no_compte = :no_compte AND premier_jour = 1 ORDER BY cycles DESC";

	$statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_COLUMN);
}

function db_select_sensations($db, $no_compte) {
	$sql = "select distinct sensation, count(sensation) as nb from observation where sensation is not null and no_compte=:no_compte group by sensation order by nb desc";

	$statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_par_nocompte($db, $no_compte) {
	$sql = "select * from compte where no_compte = :no_compte";

	$statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_par_mail($db, $mail) {
	$sql = "select * from compte where email1 like :email1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_compte_connecte($db, $no_compte){
	$sql ="update compte set derniere_co_date = now(), nb_co_echoue = 0 where no_compte = :no_compte";

	$statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_co_echoue($db, $mail){
	$sql ="update compte set nb_co_echoue = nb_co_echoue + 1 where email1 like :email1";
	
	$statement = $db->prepare($sql);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_existe($db, $mail) {
	$sql = "select count(no_compte)>0 as compte_existe from compte where email1 like :email1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_insert_compte($db, $nom, $age, $mail, $mdp) {
	$sql = "INSERT INTO compte (nom, age, email1, motdepasse) VALUES (:nom, :age, :email1, :motdepasse)";

	$statement = $db->prepare($sql);
	$statement->bindValue(":nom", $nom, PDO::PARAM_STR);
	$statement->bindValue(":age", $age, PDO::PARAM_INT);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->bindValue(":motdepasse", $mdp, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_compte($db, $nom, $mail2, $age, $no_compte) {
	$sql = "UPDATE compte SET nom = :nom, email2 = :email2, age = :age WHERE no_compte = :no_compte";

	$statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":nom", $nom, PDO::PARAM_STR);
	$statement->bindValue(":email2", $mail2, PDO::PARAM_STR);
	$statement->bindValue(":age", $age, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_motdepasse_par_mail ($db, $mdp, $mail) {
	$sql = "UPDATE compte SET motdepasse = :motdepasse, mdp_change_date = NULL WHERE email1 = :email1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->bindValue(":motdepasse", $mdp, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_udpate_motdepasse_par_nocompte($db, $mdp, $no_compte) {
	$sql = "UPDATE compte SET motdepasse = :motdepasse, mdp_change_date = now() WHERE no_compte = :no_compte";

	$statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":motdepasse", $mdp, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_delete_compte($db, $no_compte){
	$sql = "DELETE FROM compte WHERE no_compte = :no_compte";
	
	$statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_delete_observation($db, $no_compte, $date){
	$sql = "DELETE FROM observation WHERE no_compte = :no_compte AND date_obs = :date";
	
	$statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_all_observation($db, $no_compte) {
	$sql = "select * from observation where no_compte = :no_compte";

	$statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_observation ($db, $date, $no_compte) {
	$sql = "SELECT * FROM observation WHERE date_obs = :date AND no_compte = :no_compte LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_insert_observation ($db, $date, $no_compte) {
	$sql = "INSERT INTO observation (no_compte, date_obs, gommette) VALUES (:no_compte, :date, '')";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_observation ($db, $date, $no_compte, $gommette='', $sensation=null, $temp=null, $jour_sommet=null, $union_sex=null, $premier_jour=null, $commentaire=null) {
	$sql = "UPDATE observation SET gommette = :gommette, temperature = :temp, sensation = :sensation, jour_sommet = :jour_sommet, union_sex = :union_sex, premier_jour = :premier_jour, commentaire = :commentaire WHERE date_obs = :date AND no_compte = :no_compte";

	$statement = $db->prepare($sql);
	$statement->bindValue(":gommette", $gommette, PDO::PARAM_STR);
	$statement->bindValue(":sensation", $sensation, PDO::PARAM_STR);
	$statement->bindValue(":temp", $temp, PDO::PARAM_STR);
	$statement->bindValue(":jour_sommet", $jour_sommet, PDO::PARAM_INT);
	$statement->bindValue(":union_sex", $union_sex, PDO::PARAM_INT);
	$statement->bindValue(":premier_jour", $premier_jour, PDO::PARAM_INT);
	$statement->bindValue(":commentaire", $commentaire, PDO::PARAM_STR);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cycle($db, $date, $no_compte) {
	$sql = "SELECT date_obs AS cycle FROM observation WHERE premier_jour=1 AND date_obs<=:date AND no_compte = :no_compte ORDER BY date_obs DESC LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cycle_end($db, $date, $no_compte) {
	$sql = "SELECT date_obs AS cycle_end FROM observation WHERE premier_jour=1 and date_obs>:date AND no_compte = :no_compte ORDER BY date_obs ASC LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cycle_complet($db, $date_start, $date_end, $no_compte) {
	$sql = "SELECT date_obs, gommette, COALESCE(sensation,'') as sensation, COALESCE(jour_sommet, '') as sommet, COALESCE(union_sex, '') as 'unions', commentaire FROM observation WHERE date_obs>=:date_start AND date_obs<=:date_end AND no_compte = :no_compte ORDER BY date_obs ASC";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date_start", $date_start, PDO::PARAM_STR);
	$statement->bindValue(":date_end", $date_end, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_nb_compte($db) {
	$sql = "select count(no_compte) as MONCYCLE_APP_NB_COMPTE from compte";

	$statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_compte_actif($db) {
	$sql = "select count(distinct no_compte) as MONCYCLE_APP_NB_COMPTE_ACTIF from observation where date_obs >= DATE(NOW()) - INTERVAL 15 DAY and no_compte!=2";

	$statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_compte_recent($db) {
	$sql = "select count(no_compte) as MONCYCLE_APP_NB_COMPTE_RECENT from compte where inscription_date >= DATE(NOW()) - INTERVAL 15 DAY and derniere_co_date is not null and no_compte!=2";

	$statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_cycle($db) {
	$sql = "select count(no_observation) as MONCYCLE_APP_NB_CYCLE from observation where premier_jour=1 and no_compte!=2";

	$statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_cycle_recent($db) {
	$sql = "select count(no_observation) as MONCYCLE_APP_NB_CYCLE_RECENT from observation where premier_jour=1 and date_obs>= DATE(NOW()) - INTERVAL 30 DAY and no_compte!=2";

	$statement = $db->prepare($sql);
	$statement->execute();
       	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_age_moyen($db) {
	$sql = "select year(now())-avg(age)+2.5 as MONCYCLE_APP_NB_AGE_MOYEN from compte";

	$statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_age_moyen_recent($db) {
	$sql = "select year(now())-avg(age)+2.5 as MONCYCLE_APP_NB_AGE_MOYEN_RECENT from compte where inscription_date >= DATE(NOW()) - INTERVAL 15 DAY and derniere_co_date is not null and no_compte!=2";

	$statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_observation_aujourdhui($db) {
	$sql = "select count(no_observation) as MONCYCLE_APP_NB_OBSERVATION_AUJOURDHUI from observation where date_obs like DATE(NOW()) and no_compte!=2";

	$statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_observation_count($db, $nbj) {
	$sql = "select count(no_observation) as MONCYCLE_APP_NB_OBSERVATION from observation where date_obs>= DATE(NOW()) - INTERVAL :nbj DAY and no_compte!=2";

	$statement = $db->prepare($sql);
	$statement->bindValue(":nbj", $nbj, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}
