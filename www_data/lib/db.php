<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

function db_open() {
	$db = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_ID, DB_PASSWORD);

	$sql = "SET NAMES utf8mb4;";
	$statement = $db->prepare($sql);
	$statement->execute();

	return $db;
}

function db_select_cycles($db, $no_compte) {
	static $sql = "SELECT date_obs AS cycles FROM observation WHERE no_compte = :no_compte AND cycle_1st_day=1 ORDER BY cycles DESC";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_COLUMN);
}

function db_select_pregnancys($db, $no_compte) {
	static $sql = "SELECT date_obs AS cycles FROM observation WHERE no_compte = :no_compte AND pregnancy=1 ORDER BY cycles DESC";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_COLUMN);
}

function db_select_description_with_count($db, $no_compte) {
	static $sql = "SELECT d.no_description, d.name, COUNT(od.no_observation) AS use_count, d.no_compte, d.name, d.type, d.last_write_client_UTC, d.last_write_db FROM description AS d LEFT JOIN link_observation_description AS od ON od.no_description = d.no_description WHERE d.no_compte = :no_compte GROUP BY d.no_description ORDER BY use_count DESC";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_description_with_count_modified ($db, $modified_since, $no_compte) {
	static $sql = "SELECT d.no_description, d.name, COUNT(od.no_observation) AS use_count, d.no_compte, d.name, d.type, d.last_write_client_UTC, d.last_write_db FROM description AS d LEFT JOIN link_observation_description AS od ON od.no_description = d.no_description WHERE d.no_compte = :no_compte AND d.last_write_client_UTC >= :modified_since GROUP BY d.no_description ORDER BY use_count DESC";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":modified_since", $modified_since, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_all_description_for_observation($db, $no_compte, $no_observation) {
	static $sql = "SELECT ld.no_description, ld.no_description, d.no_compte, d.name, d.type FROM link_observation_description AS ld LEFT JOIN description AS d ON ld.no_description = d.no_description WHERE ld.no_observation = :no_observation AND d.no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_observation", $no_observation, PDO::PARAM_INT);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_delete_linked_descriptions ($db, $no_observation, $no_description) {
	static $sql = "DELETE FROM link_observation_description WHERE no_description = :no_description AND no_observation = :no_observation";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_description", $no_description, PDO::PARAM_INT);
	$statement->bindValue(":no_observation", $no_observation, PDO::PARAM_INT);
	$statement->execute();

	return $statement->rowCount();
}

function db_delete_descriptions ($db, $name, $no_compte) {
	static $sql = "DELETE FROM description WHERE name = :name AND no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":name", $name, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->rowCount();
}

function db_select_description_from_name($db, $no_compte, $name) {
	static $sql = "SELECT * FROM description WHERE name LIKE :name AND no_compte= :no_compte LIMIT 1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":name", $name, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_insert_description($db, $no_compte, $name, $desc_type) {
	static $sql = "INSERT INTO `description` (`no_compte`, `name`, `type`) VALUES (:no_compte, :name, :desc_type)";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":desc_type", $desc_type, PDO::PARAM_INT);
	$statement->bindValue(":name", $name, PDO::PARAM_STR);
	$statement->execute();

	return $db->lastInsertId();
}

function db_update_description_name ($db, $no_compte, $no_description, $name) {
	static $sql ="UPDATE description SET name = :name WHERE no_description = :no_description AND no_compte = :no_compte";

	$statement = $db->prepare($sql);
	$statement->bindValue(":no_description", $no_description, PDO::PARAM_INT);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":name", $name, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_description_client_timestamp ($db, $no_compte, $no_description, $last_write_client_UTC) {
	static $sql = "UPDATE description SET last_write_client_UTC = :last_write_client_UTC WHERE no_description = :no_description AND no_compte = :no_compte";

	$statement = $db->prepare($sql);
	$statement->bindValue(":no_description", $no_description, PDO::PARAM_INT);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":last_write_client_UTC", $last_write_client_UTC, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_description_type ($db, $no_compte, $no_description, $type) {
	static $sql ="UPDATE description SET type = :type WHERE no_description = :no_description AND no_compte = :no_compte";

	$statement = $db->prepare($sql);
	$statement->bindValue(":no_description", $no_description, PDO::PARAM_INT);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":type", $type, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_insert_link_description_observation($db, $observation_no, $description_no) {
	static $sql = "INSERT INTO `link_observation_description` (`no_observation`, `no_description`) VALUES (:observation_no, :description_no)";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":observation_no", $observation_no, PDO::PARAM_INT);
	$statement->bindValue(":description_no", $description_no, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_par_nocompte($db, $no_compte) {
	static $sql = "select * from compte where no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_par_mail($db, $mail) {
	static $sql = "select * from compte where email1 like :email1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_compte_connecte($db, $no_compte){
	static $sql ="update compte set last_auth_date = now(), nb_connection_attempts = 0, is_inactive = 0 where no_compte = :no_compte";

	$statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_co_echoue($db, $mail){
	static $sql ="update compte set nb_connection_attempts = nb_connection_attempts + 1 where email1 like :email1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_existe($db, $mail) {
	static $sql = "select count(no_compte)>0 as compte_existe from compte where email1 like :email1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_insert_compte($db, $name, $nfp_method, $age, $mail, $mdp, $register_comment, $research) {
	static $sql = "INSERT INTO compte (name, nfp_method, age, email1, password, register_comment, research) VALUES (:name, :nfp_method, :age, :email1, :password, :register_comment, :research)";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":name", $name, PDO::PARAM_STR);
	$statement->bindValue(":nfp_method", $nfp_method, PDO::PARAM_INT);
	$statement->bindValue(":age", $age, PDO::PARAM_INT);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->bindValue(":password", $mdp, PDO::PARAM_STR);
	$statement->bindValue(":register_comment", $register_comment, PDO::PARAM_STR);
	$statement->bindValue(":research", $research, PDO::PARAM_INT);
	$statement->execute();

	return $db->lastInsertId();
}

function db_update_compte_param_str($db, $param, $value, $no_compte) {
	$param_list = ["name", "email1", "email2", "password", "totp_secret", "last_auth_date", "inscription_date", "last_password_change", "register_comment"];
	if (!in_array($param, $param_list, true)) return false;

	static $sql = "UPDATE compte SET " . $param . " = :cvalue WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":cvalue", $value, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_compte_param_int($db, $param, $value, $no_compte) {
	$param_list = ["nfp_method", "age", "nb_connection_attempts", "sponsor", "user_enabled", "is_inactive", "timeline_asc", "research"];
	if (!in_array($param, $param_list, true)) return false;

	static $sql = "UPDATE compte SET " . $param . " = :cvalue WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":cvalue", $value, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_password_par_mail ($db, $mdp, $mail) {
	static $sql = "UPDATE compte SET password = :password, last_password_change = NULL WHERE email1 = :email1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->bindValue(":password", $mdp, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_udpate_password_par_nocompte($db, $mdp, $no_compte) {
	static $sql = "UPDATE compte SET password = :password, last_password_change = now() WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":password", $mdp, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_delete_compte($db, $no_compte){
	static $sql = "DELETE FROM compte WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->rowCount();
}

function db_delete_compte_jamais_connecte($db, $month_without_connections=1){
	static $sql = "SELECT * FROM compte WHERE last_auth_date IS NULL AND inscription_date < NOW() - INTERVAL :month_without_connections MONTH";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":month_without_connections", $month_without_connections, PDO::PARAM_INT);
	$statement->execute();

	return $statement->rowCount();
}

function db_delete_compte_sans_connexions_recentes($db, $month_without_connections=48){
	static $sql = "DELETE compte FROM compte WHERE last_auth_date IS NOT NULL AND last_auth_date < NOW() - INTERVAL :month_without_connections MONTH";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":month_without_connections", $month_without_connections, PDO::PARAM_INT);
	$statement->execute();

	return $statement->rowCount();
}

function db_delete_jetton($db, $no_jetton, $no_compte){
	static $sql = "DELETE FROM `jetton` WHERE `no_jetton` = :no_jetton AND `no_compte` = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_jetton", $no_jetton, PDO::PARAM_INT);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->rowCount();
}

function db_delete_observation($db, $no_compte, $date){
	static $sql = "DELETE FROM observation WHERE no_compte = :no_compte AND date_obs = :date";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->execute();

	return $statement->rowCount();
}

function db_delete_vieux_jetton($db) {
	static $sql = "DELETE FROM jetton WHERE (date_creation < (CURDATE() + INTERVAL - 365 DAY) OR date_use < (CURDATE() + INTERVAL - 40 DAY)) AND expire>0";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->rowCount();
}

function db_select_all_observation($db, $no_compte) {
	static $sql = "select * from observation where no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_observation ($db, $date, $no_compte) {
	static $sql = "SELECT * FROM observation WHERE date_obs = :date AND no_compte = :no_compte LIMIT 1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_observations_modified ($db, $modified_since, $no_compte) {
	static $sql = "SELECT * FROM observation WHERE last_write_client_UTC >= :modified_since AND no_compte = :no_compte ORDER BY last_write_client_UTC DESC";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":modified_since", $modified_since, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_insert_observation ($db, $date, $no_compte) {
	static $sql = "INSERT INTO observation (no_compte, date_obs, stamp) VALUES (:no_compte, :date, '')";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $db->lastInsertId();
}

function db_update_observation ($db, $date, $no_compte, $last_write_client_UTC, $stamp='', $fc_score=null, $fc_arrow=null, $sensation=null, $temp=null, $htemp=null, $is_peak=null, $union_sex=null, $cycle_1st_day=null, $day_not_observed=null, $pregnancy=null, $comment=null, $counter_start=null) {
	static $sql = "UPDATE observation SET stamp = :stamp, fc_score = :fc_score, fc_arrow = :fc_arrow, temperature = :temp, time_temp_taken = :htemp, sensation = :sensation, is_peak = :is_peak, union_sex = :union_sex, cycle_1st_day = :cycle_1st_day, day_not_observed = :day_not_observed, pregnancy = :pregnancy, comment = :comment, counter_start = :counter_start, last_write_client_UTC = :last_write_client_UTC WHERE date_obs = :date AND no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":stamp", $stamp, PDO::PARAM_STR);
	$statement->bindValue(":fc_score", $fc_score, PDO::PARAM_STR);
	$statement->bindValue(":fc_arrow", $fc_arrow, PDO::PARAM_STR);
	$statement->bindValue(":sensation", $sensation, PDO::PARAM_STR);
	$statement->bindValue(":temp", $temp, PDO::PARAM_STR);
	$statement->bindValue(":htemp", $htemp, PDO::PARAM_STR);
	$statement->bindValue(":is_peak", $is_peak, PDO::PARAM_INT);
	$statement->bindValue(":union_sex", $union_sex, PDO::PARAM_INT);
	$statement->bindValue(":cycle_1st_day", $cycle_1st_day, PDO::PARAM_INT);
	$statement->bindValue(":comment", $comment, PDO::PARAM_STR);
	$statement->bindValue(":day_not_observed", $day_not_observed, PDO::PARAM_INT);
	$statement->bindValue(":pregnancy", $pregnancy, PDO::PARAM_INT);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":counter_start", $counter_start, PDO::PARAM_INT);
	$statement->bindValue(":last_write_client_UTC", $last_write_client_UTC, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cycle($db, $date, $no_compte) {
	static $sql = "SELECT date_obs AS cycle FROM observation WHERE cycle_1st_day=1 AND date_obs<=:date AND no_compte = :no_compte ORDER BY date_obs DESC LIMIT 1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cycle_end($db, $date, $no_compte) {
	static $sql = "SELECT date_obs AS cycle_end FROM observation WHERE cycle_1st_day=1 and date_obs>:date AND no_compte = :no_compte ORDER BY date_obs ASC LIMIT 1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cycle_pregnancy($db, $date, $no_compte) {
	static $sql = "SELECT date_obs AS pregnancy FROM observation WHERE pregnancy=1 and date_obs>:date AND no_compte = :no_compte ORDER BY date_obs ASC LIMIT 1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cycle_complet($db, $date_start, $date_end, $no_compte) {
	static $sql = "SELECT date_obs, COALESCE(day_not_observed,'') as '?', COALESCE(fc_score,'') as fc_score, COALESCE(fc_arrow,'') as fc_arrow, stamp, COALESCE(temperature,'') as temperature, COALESCE(time_temp_taken,'') as time_temp_taken, COALESCE(sensation,'') as sensation, COALESCE(is_peak, '') as sommet, COALESCE(counter_start, '') as counter_start, COALESCE(union_sex, '') as 'unions', COALESCE(pregnancy, '') as 'pregnancy', comment, COALESCE(cycle_1st_day, 0) as 'cycle_1st_day' FROM observation WHERE date_obs>=:date_start AND date_obs<=:date_end AND no_compte = :no_compte ORDER BY date_obs ASC";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":date_start", $date_start, PDO::PARAM_STR);
	$statement->bindValue(":date_end", $date_end, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_nb_compte($db) {
	static $sql = "select count(no_compte) as MONCYCLE_APP_NB_COMPTE from compte";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_compte_actif($db) {
	static $sql = "select count(distinct no_compte) as MONCYCLE_APP_NB_COMPTE_ACTIF from observation where date_obs >= DATE(NOW()) - INTERVAL 35 DAY";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_compte_actif_par_nfp_method($db, $nfp_method) {
	static $sql = "select count(distinct obs.no_compte) as MONCYCLE_APP_NB_COMPTE_ACTIF_METHODE from observation as obs left join compte as com on obs.no_compte = com.no_compte where date_obs >= DATE(NOW()) - INTERVAL 35 DAY and com.nfp_method = :nfp_method";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":nfp_method", $nfp_method, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_compte_recent($db) {
	static $sql = "select count(no_compte) as MONCYCLE_APP_NB_COMPTE_RECENT from compte where inscription_date >= DATE(NOW()) - INTERVAL 15 DAY and last_auth_date is not null";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_cycle($db) {
	static $sql = "select count(no_observation) as MONCYCLE_APP_NB_CYCLE from observation where cycle_1st_day=1 and no_compte!=2";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_cycle_recent($db) {
	static $sql = "select count(no_observation) as MONCYCLE_APP_NB_CYCLE_RECENT from observation where cycle_1st_day=1 and date_obs>= DATE(NOW()) - INTERVAL 30 DAY and no_compte!=2";

	static $statement = $db->prepare($sql);
	$statement->execute();
	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_age_moyen($db) {
	static $sql = "select year(now())-avg(age)+2.5 as MONCYCLE_APP_NB_AGE_MOYEN from compte";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_age_moyen_recent($db) {
	static $sql = "select year(now())-avg(age)+2.5 as MONCYCLE_APP_NB_AGE_MOYEN_RECENT from compte where inscription_date >= DATE(NOW()) - INTERVAL 15 DAY and last_auth_date is not null and no_compte!=2";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_total_observation_count($db) {
	static $sql = "select count(no_observation) as MONCYCLE_APP_NB_OBSERVATION from observation where no_compte!=2;";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_observation_aujourdhui($db) {
	static $sql = "select count(no_observation) as MONCYCLE_APP_NB_OBSERVATION_AUJOURDHUI from observation where date_obs like DATE(NOW()) and no_compte!=2";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_observation_count($db, $nbj) {
	static $sql = "select count(no_observation) as MONCYCLE_APP_NB_OBSERVATION from observation where date_obs>= DATE(NOW()) - INTERVAL :nbj DAY and no_compte!=2";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":nbj", $nbj, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_jetton_compte($db) {
	static $sql = "select count(no_jetton) as MONCYCLE_APP_NB_JETTON from jetton";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_cycles_recent($db) {
	static $sql = "select subdate(obs.date_obs, 1) as cycle_complet, obs.no_compte as no_compte, c.name as name, c.nfp_method as nfp_method, c.email1 as email1, c.email2 as email2 from observation as obs, compte as c where obs.no_compte=c.no_compte and date_obs= DATE(NOW()) - INTERVAL 2 DAY and (cycle_1st_day=1 or pregnancy=1)";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_inactif($db) {
	static $sql = "select `c`.`no_compte` as `no_compte`,`c`.`name` as `name`,max(`o`.`last_write_db`) as `derniere_obs_modif`,`c`.`email1` as `email1`,`c`.`email2` as `email2`,`c`.`inscription_date` as `inscription_date` from `compte` as     `c` left join `observation` as `o` on `c`.`no_compte` = `o`.`no_compte` where `c`.`no_compte` != 2 and `c`.`is_inactive`=0 group by `c`.`no_compte`  having (date(`derniere_obs_modif`) < date(now()) - interval 35 DAY or `derniere_obs_modif` is null) and `inscription_date` < date(now()) - interval 35 DAY order by `derniere_obs_modif` desc limit 20";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_is_inactive ($db, $no_compte, $is_inactive) {
	static $sql = "UPDATE compte SET is_inactive = :is_inactive WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":is_inactive", $is_inactive, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_insert_jetton($db, $no_compte, $name, $pays, $jetton_str, $expire=2) {
	static $sql = "INSERT INTO `jetton` (`no_compte`, `name`, `pays`, `jetton_str`, `expire`) VALUES (:no_compte, :name, :pays, :jetton_str, :expire)";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":expire", $expire, PDO::PARAM_INT);
	$statement->bindValue(":name", $name, PDO::PARAM_STR);
	$statement->bindValue(":pays", $pays, PDO::PARAM_STR);
	$statement->bindValue(":jetton_str", $jetton_str, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_jetton($db, $jetton_str) {
	static $sql = "SELECT J.no_compte, J.no_jetton, C.name AS name_compte, J.pays, J.name AS name_jetton, J.date_creation AS d_creation_jetton, J.date_use AS d_use_jetton, C.nfp_method, C.age, C.email1, C.email2, C.nb_connection_attempts, C.sponsor, C.user_enabled, C.is_inactive, C.last_auth_date, C.inscription_date, C.last_password_change, C.register_comment, C.totp_secret, C.totp_state, C.research, C.timeline_asc FROM `jetton` AS J INNER JOIN `compte` AS C ON J.no_compte=C.no_compte WHERE `jetton_str` = :jetton_str LIMIT 1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":jetton_str", $jetton_str, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_jetton_captcha($db, $jetton_str) {
	static $sql = "SELECT captcha, no_jetton FROM `jetton` WHERE `jetton_str` = :jetton_str LIMIT 1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":jetton_str", $jetton_str, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_jetton_use($db, $no_jetton){
	static $sql = "UPDATE `jetton` SET `date_use` = now() WHERE `no_jetton` = :no_jetton";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_jetton", $no_jetton, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_jetton_captcha($db, $jetton_str, $captcha){
	static $sql = "UPDATE `jetton` SET `date_use` = now(), `captcha` = :captcha WHERE `jetton_str` = :jetton_str";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":jetton_str", $jetton_str, PDO::PARAM_STR);
	$statement->bindValue(":captcha", $captcha, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_tous_les_jetton($db, $no_compte) {
	static $sql = "SELECT * FROM jetton where no_compte= :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_increment_cle_valeur($db, $cle){
	static $sql = "update cle_valeur set valeur = valeur+1 where cle like :cle";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":cle", $cle, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cle_valeur($db, $cle) {
	static $sql = "select valeur from cle_valeur where cle=:cle";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":cle", $cle, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_update_reset_cle_valeur($db, $cle){
	static $sql = "update cle_valeur set valeur = 0 where cle like :cle";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":cle", $cle, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_compte_totp_secret($db, $totp_secret, $no_compte) {
	static $sql = "UPDATE compte SET totp_secret = :cvalue WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":cvalue", $totp_secret, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_compte_totp_state($db, $totp_state, $no_compte) {
	static $sql = "UPDATE compte SET totp_state = :cvalue WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":cvalue", $totp_state, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_avec_totp($db) {
	static $sql = "select count(no_compte) as MONCYCLE_APP_NB_COMPTE_AVEC_TOTP from compte where totp_state=3 and no_compte!=2";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

