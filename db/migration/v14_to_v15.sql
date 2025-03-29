ALTER TABLE `observation`
CHANGE `dernier_modif` `last_write_db` timestamp NULL ON UPDATE CURRENT_TIMESTAMP AFTER `commentaire`;

ALTER TABLE `observation`
ADD `last_write_client_UTC` timestamp NULL AFTER `commentaire`;

-- TRANSLATION TO ENGLISH
ALTER TABLE `compte`
CHANGE `recherche` `research` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `donateur`;
ALTER TABLE `compte`
CHANGE `nom` `name` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL AFTER `no_compte`;
ALTER TABLE `jetton`
CHANGE `nom` `name` varchar(256) COLLATE 'utf8mb4_bin' NOT NULL AFTER `no_compte`;
ALTER TABLE `compte`
CHANGE `donateur` `sponsor` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `timeline_asc`;
ALTER TABLE `observation`
CHANGE `premier_jour` `cycle_1st_day` tinyint(1) unsigned NULL AFTER `union_sex`;
ALTER TABLE `observation`
CHANGE `note_fc` `fc_score` varchar(32) COLLATE 'utf8mb4_bin' NULL AFTER `jenesaispas`;
ALTER TABLE `observation`
CHANGE `fleche_fc` `fc_arrow` varchar(1) COLLATE 'utf8mb4_bin' NULL AFTER `fc_score`;
ALTER TABLE `observation`
CHANGE `heure_temp` `time_temp_taken` time NULL AFTER `temperature`;
ALTER TABLE `observation`
CHANGE `jenesaispas` `day_not_observed` tinyint(1) unsigned NULL AFTER `date_obs`;
ALTER TABLE `observation`
CHANGE `jour_sommet` `is_peak` tinyint(1) unsigned NULL AFTER `time_temp_taken`;
ALTER TABLE `observation`
CHANGE `compteur` `counter_start` tinyint(1) unsigned NULL AFTER `is_peak`;
ALTER TABLE `observation`
CHANGE `grossesse` `pregnancy` tinyint(1) unsigned NULL AFTER `cycle_1st_day`;
ALTER TABLE `observation`
CHANGE `commentaire` `comment` varchar(256) COLLATE 'utf8mb4_bin' NULL AFTER `pregnancy`;


ALTER TABLE `compte`
CHANGE `motdepasse` `password` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL AFTER `email2`;
ALTER TABLE `compte`
CHANGE `totp_etat` `totp_state` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `password`;
ALTER TABLE `compte`
CHANGE `nb_co_echoue` `nb_connection_attempts` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `totp_secret`;
ALTER TABLE `compte`
CHANGE `actif` `user_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `research`;
ALTER TABLE `compte`
CHANGE `derniere_co_date` `last_auth_date` timestamp NULL AFTER `relance`;
ALTER TABLE `compte`
CHANGE `mdp_change_date` `last_password_change` timestamp NULL AFTER `inscription_date`;
ALTER TABLE `compte`
CHANGE `relance` `is_inactive` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `user_enabled`;
ALTER TABLE `compte`
CHANGE `methode` `nfp_method` smallint(5) unsigned NOT NULL DEFAULT '1' AFTER `name`;

