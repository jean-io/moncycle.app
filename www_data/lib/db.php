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
	static $sql = "SELECT date_obs AS cycles FROM observation WHERE no_compte = :no_compte AND premier_jour=1 ORDER BY cycles DESC";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_COLUMN);
}

function db_select_grossesses($db, $no_compte) {
	static $sql = "SELECT date_obs AS cycles FROM observation WHERE no_compte = :no_compte AND grossesse=1 ORDER BY cycles DESC";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_COLUMN);
}

function db_select_sensations($db, $no_compte) {
	static $sql = "select distinct sensation, count(sensation) as nb from observation where sensation is not null and no_compte=:no_compte group by sensation order by nb desc";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
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
	$sql ="update compte set derniere_co_date = now(), nb_co_echoue = 0, relance = 0 where no_compte = :no_compte";

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
	static $sql = "select count(no_compte)>0 as compte_existe from compte where email1 like :email1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_insert_compte($db, $nom, $methode, $age, $mail, $mdp, $decouvert, $recherche) {
	static $sql = "INSERT INTO compte (nom, methode, age, email1, motdepasse, decouvert, recherche) VALUES (:nom, :methode, :age, :email1, :motdepasse, :decouvert, :recherche)";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":nom", $nom, PDO::PARAM_STR);
	$statement->bindValue(":methode", $methode, PDO::PARAM_INT);
	$statement->bindValue(":age", $age, PDO::PARAM_INT);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->bindValue(":motdepasse", $mdp, PDO::PARAM_STR);
	$statement->bindValue(":decouvert", $decouvert, PDO::PARAM_STR);
	$statement->bindValue(":recherche", $recherche, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_compte_param_str($db, $param, $value, $no_compte) {
	$param_list = ["nom", "email1", "email2", "motdepasse", "totp_secret", "derniere_co_date", "inscription_date", "mdp_change_date", "decouvert"];
	if (!in_array($param, $param_list, true)) return false;

	static $sql = "UPDATE compte SET " . $param . " = :cvalue WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":cvalue", $value, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_compte_param_int($db, $param, $value, $no_compte) {
	$param_list = ["methode", "age", "nb_co_echoue", "donateur", "actif", "relance", "timeline_asc", "recherche"];
	if (!in_array($param, $param_list, true)) return false;

	static $sql = "UPDATE compte SET " . $param . " = :cvalue WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":cvalue", $value, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_motdepasse_par_mail ($db, $mdp, $mail) {
	static $sql = "UPDATE compte SET motdepasse = :motdepasse, mdp_change_date = NULL WHERE email1 = :email1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":email1", $mail, PDO::PARAM_STR);
	$statement->bindValue(":motdepasse", $mdp, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_udpate_motdepasse_par_nocompte($db, $mdp, $no_compte) {
	static $sql = "UPDATE compte SET motdepasse = :motdepasse, mdp_change_date = now() WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":motdepasse", $mdp, PDO::PARAM_STR);
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
	static $sql = "SELECT * FROM compte WHERE derniere_co_date IS NULL AND inscription_date < NOW() - INTERVAL :month_without_connections MONTH";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":month_without_connections", $month_without_connections, PDO::PARAM_INT);
	$statement->execute();

	return $statement->rowCount();
}

function db_delete_compte_sans_connexions_recentes($db, $month_without_connections=48){
	static $sql = "DELETE compte FROM compte WHERE derniere_co_date IS NOT NULL AND derniere_co_date < NOW() - INTERVAL :month_without_connections MONTH";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":month_without_connections", $month_without_connections, PDO::PARAM_INT);
	$statement->execute();

	return $statement->rowCount();
}

function db_select_compte_sans_connexions_recentes($db, $month_without_connections=48){
	static $sql = "SELECT * FROM compte WHERE derniere_co_date IS NOT NULL AND derniere_co_date < NOW() - INTERVAL :month_without_connections MONTH";

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

function db_insert_observation ($db, $date, $no_compte) {
	static $sql = "INSERT INTO observation (no_compte, date_obs, gommette) VALUES (:no_compte, :date, '')";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_observation ($db, $date, $no_compte, $gommette='', $note_fc=null, $fleche_fc=null, $sensation=null, $temp=null, $htemp=null, $jour_sommet=null, $union_sex=null, $premier_jour=null, $jenesaispas=null, $grossesse=null, $commentaire=null, $compteur=null) {
	static $sql = "UPDATE observation SET gommette = :gommette, note_fc = :note_fc, fleche_fc = :fleche_fc, temperature = :temp, heure_temp = :htemp, sensation = :sensation, jour_sommet = :jour_sommet, union_sex = :union_sex, premier_jour = :premier_jour, jenesaispas = :jenesaispas, grossesse = :grossesse, commentaire = :commentaire, compteur = :compteur WHERE date_obs = :date AND no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":gommette", $gommette, PDO::PARAM_STR);
	$statement->bindValue(":note_fc", $note_fc, PDO::PARAM_STR);
	$statement->bindValue(":fleche_fc", $fleche_fc, PDO::PARAM_STR);
	$statement->bindValue(":sensation", $sensation, PDO::PARAM_STR);
	$statement->bindValue(":temp", $temp, PDO::PARAM_STR);
	$statement->bindValue(":htemp", $htemp, PDO::PARAM_STR);
	$statement->bindValue(":jour_sommet", $jour_sommet, PDO::PARAM_INT);
	$statement->bindValue(":union_sex", $union_sex, PDO::PARAM_INT);
	$statement->bindValue(":premier_jour", $premier_jour, PDO::PARAM_INT);
	$statement->bindValue(":commentaire", $commentaire, PDO::PARAM_STR);
	$statement->bindValue(":jenesaispas", $jenesaispas, PDO::PARAM_INT);
	$statement->bindValue(":grossesse", $grossesse, PDO::PARAM_INT);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":compteur", $compteur, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cycle($db, $date, $no_compte) {
	static $sql = "SELECT date_obs AS cycle FROM observation WHERE premier_jour=1 AND date_obs<=:date AND no_compte = :no_compte ORDER BY date_obs DESC LIMIT 1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cycle_end($db, $date, $no_compte) {
	static $sql = "SELECT date_obs AS cycle_end FROM observation WHERE premier_jour=1 and date_obs>:date AND no_compte = :no_compte ORDER BY date_obs ASC LIMIT 1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cycle_grossesse($db, $date, $no_compte) {
	static $sql = "SELECT date_obs AS grossesse FROM observation WHERE grossesse=1 and date_obs>:date AND no_compte = :no_compte ORDER BY date_obs ASC LIMIT 1";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":date", $date, PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_cycle_complet($db, $date_start, $date_end, $no_compte) {
	static $sql = "SELECT date_obs, COALESCE(jenesaispas,'') as '?', COALESCE(note_fc,'') as note_fc, COALESCE(fleche_fc,'') as fleche_fc, gommette, COALESCE(temperature,'') as temperature, COALESCE(heure_temp,'') as heure_temp, COALESCE(sensation,'') as sensation, COALESCE(jour_sommet, '') as sommet, COALESCE(compteur, '') as compteur, COALESCE(union_sex, '') as 'unions', COALESCE(grossesse, '') as 'grossesse', commentaire, COALESCE(premier_jour, 0) as 'premier_jour' FROM observation WHERE date_obs>=:date_start AND date_obs<=:date_end AND no_compte = :no_compte ORDER BY date_obs ASC";

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

function db_select_nb_compte_actif_par_methode($db, $methode) {
	static $sql = "select count(distinct obs.no_compte) as MONCYCLE_APP_NB_COMPTE_ACTIF_METHODE from observation as obs left join compte as com on obs.no_compte = com.no_compte where date_obs >= DATE(NOW()) - INTERVAL 35 DAY and com.methode = :methode";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":methode", $methode, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_compte_recent($db) {
	static $sql = "select count(no_compte) as MONCYCLE_APP_NB_COMPTE_RECENT from compte where inscription_date >= DATE(NOW()) - INTERVAL 15 DAY and derniere_co_date is not null";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_cycle($db) {
	static $sql = "select count(no_observation) as MONCYCLE_APP_NB_CYCLE from observation where premier_jour=1 and no_compte!=2";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

function db_select_nb_cycle_recent($db) {
	static $sql = "select count(no_observation) as MONCYCLE_APP_NB_CYCLE_RECENT from observation where premier_jour=1 and date_obs>= DATE(NOW()) - INTERVAL 30 DAY and no_compte!=2";

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
	static $sql = "select year(now())-avg(age)+2.5 as MONCYCLE_APP_NB_AGE_MOYEN_RECENT from compte where inscription_date >= DATE(NOW()) - INTERVAL 15 DAY and derniere_co_date is not null and no_compte!=2";

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
	static $sql = "select subdate(obs.date_obs, 1) as cycle_complet, obs.no_compte as no_compte, c.nom as nom, c.methode as methode, c.email1 as email1, c.email2 as email2 from observation as obs, compte as c where obs.no_compte=c.no_compte and date_obs= DATE(NOW()) - INTERVAL 2 DAY and (premier_jour=1 or grossesse=1)";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_inactif($db) {
	static $sql = "select `c`.`no_compte` as `no_compte`,`c`.`nom` as `nom`,max(`o`.`dernier_modif`) as `derniere_obs_modif`,`c`.`email1` as `email1`,`c`.`email2` as `email2`,`c`.`inscription_date` as `inscription_date` from `compte` as     `c` left join `observation` as `o` on `c`.`no_compte` = `o`.`no_compte` where `c`.`no_compte` != 2 and `c`.`relance`=0 group by `c`.`no_compte`  having (date(`derniere_obs_modif`) < date(now()) - interval 35 DAY or `derniere_obs_modif` is null) and `inscription_date` < date(now()) - interval 35 DAY order by `derniere_obs_modif` desc limit 20";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_update_relance ($db, $no_compte, $relance) {
	static $sql = "UPDATE compte SET relance = :relance WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":relance", $relance, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_insert_jetton($db, $no_compte, $nom, $pays, $jetton_str, $expire=2) {
	static $sql = "INSERT INTO `jetton` (`no_compte`, `nom`, `pays`, `jetton_str`, `expire`) VALUES (:no_compte, :nom, :pays, :jetton_str, :expire)";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":expire", $expire, PDO::PARAM_INT);
	$statement->bindValue(":nom", $nom, PDO::PARAM_STR);
	$statement->bindValue(":pays", $pays, PDO::PARAM_STR);
	$statement->bindValue(":jetton_str", $jetton_str, PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_jetton($db, $jetton_str) {
	static $sql = "SELECT J.no_compte, J.no_jetton, C.nom AS nom_compte, J.pays, J.nom AS nom_jetton, J.date_creation AS d_creation_jetton, J.date_use AS d_use_jetton, C.methode, C.age, C.email1, C.email2, C.nb_co_echoue, C.donateur, C.actif, C.relance, C.derniere_co_date, C.inscription_date, C.mdp_change_date, C.decouvert, C.totp_secret, C.totp_etat, C.recherche, C.timeline_asc FROM `jetton` AS J INNER JOIN `compte` AS C ON J.no_compte=C.no_compte WHERE `jetton_str` = :jetton_str LIMIT 1";

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

function db_update_compte_totp_etat($db, $totp_etat, $no_compte) {
	static $sql = "UPDATE compte SET totp_etat = :cvalue WHERE no_compte = :no_compte";

	static $statement = $db->prepare($sql);
	$statement->bindValue(":no_compte", $no_compte, PDO::PARAM_INT);
	$statement->bindValue(":cvalue", $totp_etat, PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function db_select_compte_avec_totp($db) {
	static $sql = "select count(no_compte) as MONCYCLE_APP_NB_COMPTE_AVEC_TOTP from compte where totp_etat=3 and no_compte!=2";

	static $statement = $db->prepare($sql);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_NUM);
}

