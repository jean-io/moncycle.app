-- moncycle.app
--
-- licence Creative Commons CC BY-NC-SA
--
-- https://www.moncycle.app
-- https://github.com/jean-io/moncycle.app

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id_account` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `nfp_method` smallint(5) unsigned NOT NULL DEFAULT 1,
  `age` smallint(5) unsigned NOT NULL,
  `email1` varchar(255) NOT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `totp_status` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `totp_secret` varchar(255) DEFAULT NULL,
  `failed_login` smallint(5) unsigned NOT NULL DEFAULT 0,
  `timeline_asc` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `donor` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `recherche` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `relance` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `last_login_date` timestamp NULL DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `password_change_date` timestamp NULL DEFAULT NULL,
  `how_discovered` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_account`),
  UNIQUE KEY `email1` (`email1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;


DROP TABLE IF EXISTS `observation`;
CREATE TABLE `observation` (
  `id_observation` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_account` mediumint(8) unsigned NOT NULL,
  `observation_date` date NOT NULL DEFAULT '0000-00-00',
  `i_dont_know` tinyint(1) unsigned DEFAULT NULL,
  `note_fc` varchar(32) DEFAULT NULL,
  `arrow_fc` varchar(1) DEFAULT NULL,
  `stamp` varchar(3) NOT NULL,
  `feeling` varchar(256) DEFAULT NULL,
  `temperature` decimal(4,2) unsigned DEFAULT NULL,
  `temperature_time` time DEFAULT NULL,
  `peak` tinyint(1) unsigned DEFAULT NULL,
  `counter` tinyint(1) unsigned DEFAULT NULL,
  `sexual_union` tinyint(1) unsigned DEFAULT NULL,
  `cycle_first_day` tinyint(1) unsigned DEFAULT NULL,
  `pregnancy` tinyint(1) unsigned DEFAULT NULL,
  `comment` varchar(256) DEFAULT NULL,
  `last_modified_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_observation`),
  UNIQUE KEY `unique_account_and_date` (`id_account`,`observation_date`),
  KEY `no_compte` (`id_account`),
  KEY `date_obs` (`observation_date`),
  CONSTRAINT `observation_ibfk_1` FOREIGN KEY (`id_account`) REFERENCES `account` (`id_account`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;


DROP TABLE IF EXISTS `token`;
CREATE TABLE `token` (
  `id_token` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_account` mediumint(8) unsigned DEFAULT NULL,
  `nom` varchar(256) NOT NULL,
  `expired` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `country` varchar(2) DEFAULT NULL,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `use_date` timestamp NULL DEFAULT NULL,
  `token_str` varchar(512) NOT NULL,
  `captcha` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id_token`),
  UNIQUE KEY `token_str` (`token_str`),
  KEY `no_compte` (`id_account`),
  CONSTRAINT `observation_ibfk_2` FOREIGN KEY (`id_account`) REFERENCES `account` (`id_account`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;


DROP TABLE IF EXISTS `key_value`;
CREATE TABLE `key_value` (
  `key` varchar(255) NOT NULL,
  `value` bigint(20) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

INSERT INTO `key_value` (`key`, `value`) VALUES
('monthly_public_visits', 0),
('weekly_public_visits', 0),
('daily_public_visits', 0);

-- 2024-08-24 19:26:49
