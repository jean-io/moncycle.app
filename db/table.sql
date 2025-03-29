SET NAMES utf8mb4;

CREATE TABLE `compte` (
  `no_compte` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `nfp_method` smallint(5) unsigned NOT NULL DEFAULT 1,
  `age` smallint(5) unsigned NOT NULL,
  `email1` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `email2` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `totp_state` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `totp_secret` varchar(255) DEFAULT NULL,
  `nb_connection_attempts` smallint(5) unsigned NOT NULL DEFAULT 0,
  `timeline_asc` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `sponsor` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `research` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `user_enabled` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `is_inactive` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `last_auth_date` timestamp NULL DEFAULT NULL,
  `inscription_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_password_change` timestamp NULL DEFAULT NULL,
  `register_comment` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`no_compte`),
  UNIQUE KEY `email1` (`email1`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `observation` (
  `no_observation` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `no_compte` mediumint(8) unsigned NOT NULL,
  `date_obs` date NOT NULL DEFAULT '0000-00-00',
  `day_not_observed` tinyint(1) unsigned DEFAULT NULL,
  `fc_score` varchar(32) COLLATE utf8mb4_bin DEFAULT NULL,
  `fc_arrow` varchar(1) COLLATE utf8mb4_bin DEFAULT NULL,
  `stamp` varchar(3) COLLATE utf8mb4_bin NOT NULL,
  `sensation` varchar(256) COLLATE utf8mb4_bin DEFAULT NULL,
  `temperature` decimal(4,2) unsigned DEFAULT NULL,
  `time_temp_taken` time DEFAULT NULL,
  `is_peak` tinyint(1) unsigned DEFAULT NULL,
  `counter_start` tinyint(1) unsigned DEFAULT NULL,
  `union_sex` tinyint(1) unsigned DEFAULT NULL,
  `cycle_1st_day` tinyint(1) unsigned DEFAULT NULL,
  `pregnancy` tinyint(1) unsigned DEFAULT NULL,
  `comment` varchar(256) COLLATE utf8mb4_bin DEFAULT NULL,
  `last_write_client_UTC` timestamp NULL DEFAULT NULL,
  `last_write_db` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`no_observation`),
  UNIQUE KEY `unique_compte_and_date` (`no_compte`,`date_obs`),
  KEY `no_compte` (`no_compte`),
  KEY `date_obs` (`date_obs`),
  CONSTRAINT `observation_ibfk_1` FOREIGN KEY (`no_compte`) REFERENCES `compte` (`no_compte`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `jetton` (
  `no_jetton` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `no_compte` mediumint(8) unsigned DEFAULT NULL,
  `name` varchar(256) COLLATE utf8mb4_bin NOT NULL,
  `expire` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `pays` varchar(2) COLLATE utf8mb4_bin DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_use` timestamp NULL DEFAULT NULL,
  `jetton_str` varchar(512) COLLATE utf8mb4_bin NOT NULL,
  `captcha` varchar(16) COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`no_jetton`),
  UNIQUE KEY `jetton_str` (`jetton_str`),
  KEY `no_compte` (`no_compte`),
  CONSTRAINT `observation_ibfk_2` FOREIGN KEY (`no_compte`) REFERENCES `compte` (`no_compte`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `description` (
  `no_description` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `no_compte` mediumint(8) unsigned DEFAULT NULL,
  `name` varchar(256) COLLATE utf8mb4_bin NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `last_write_client_UTC` timestamp NULL DEFAULT NULL,
  `last_write_db` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`no_description`),
  KEY `no_compte` (`no_compte`),
  UNIQUE KEY `unique_compte_and_name` (`no_compte`,`name`),
  CONSTRAINT `observation_ibfk_3` FOREIGN KEY (`no_compte`) REFERENCES `compte` (`no_compte`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `link_observation_description` (
  `no_observation` mediumint(8) unsigned NOT NULL,
  `no_description` mediumint(8) unsigned NOT NULL,
  `last_write_db` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  KEY `no_observation` (`no_observation`),
  KEY `no_description` (`no_description`),
  UNIQUE KEY `unique_observation_and_description` (`no_observation`,`no_description`),
  CONSTRAINT `observation_ibfk_4` FOREIGN KEY (`no_observation`) REFERENCES `observation` (`no_observation`) ON DELETE CASCADE,
  CONSTRAINT `observation_ibfk_5` FOREIGN KEY (`no_description`) REFERENCES `description` (`no_description`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `cle_valeur` (
  `cle` varchar(255) NOT NULL,
  `valeur` bigint(20) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

INSERT INTO `cle_valeur` (`cle`, `valeur`) VALUES
('pub_visite_mensuel',	0),
('pub_visite_hebdo',	0),
('pub_visite_jour',	0);
