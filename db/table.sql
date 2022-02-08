SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS `dev_moncyle_app_nas`;
CREATE DATABASE `dev_moncyle_app_nas` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin */;
USE `dev_moncyle_app_nas`;

DROP TABLE IF EXISTS `compte`;
CREATE TABLE `compte` (
  `no_compte` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `age` smallint(5) unsigned NOT NULL,
  `email1` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `email2` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `motdepasse` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `actif` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `derniere_co_date` timestamp NULL DEFAULT NULL,
  `inscription_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `mdp_change_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`no_compte`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
ALTER TABLE `compte` AUTO_INCREMENT=5;


DROP TABLE IF EXISTS `observation`;
CREATE TABLE `observation` (
  `no_observation` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `no_compte` mediumint(8) unsigned NOT NULL,
  `date_obs` date NOT NULL DEFAULT '0000-00-00',
  `gommette` varchar(2) COLLATE utf8mb4_bin NOT NULL,
  `sensation` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `jour_sommet` tinyint(1) unsigned zerofill DEFAULT NULL,
  `union_sex` tinyint(1) unsigned zerofill DEFAULT NULL,
  `premier_jour` tinyint(1) unsigned DEFAULT NULL,
  `commentaire` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `dernier_modif` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`no_observation`),
  KEY `no_compte` (`no_compte`),
  CONSTRAINT `observation_ibfk_1` FOREIGN KEY (`no_compte`) REFERENCES `compte` (`no_compte`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
ALTER TABLE `observation` AUTO_INCREMENT=100;
