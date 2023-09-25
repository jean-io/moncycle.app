SET NAMES utf8mb4;

CREATE TABLE `compte` (
  `no_compte` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `methode` smallint(5) unsigned NOT NULL DEFAULT 1,
  `age` smallint(5) unsigned NOT NULL,
  `email1` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `email2` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `motdepasse` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `nb_co_echoue` smallint(5) unsigned NOT NULL DEFAULT 0,
  `donateur` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `actif` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `relance` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `derniere_co_date` timestamp NULL DEFAULT NULL,
  `inscription_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `mdp_change_date` timestamp NULL DEFAULT NULL,
  `decouvert` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`no_compte`),
  UNIQUE KEY `email1` (`email1`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `observation` (
  `no_observation` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `no_compte` mediumint(8) unsigned NOT NULL,
  `date_obs` date NOT NULL DEFAULT '0000-00-00',
  `jenesaispas` tinyint(1) unsigned DEFAULT NULL,
  `note_fc` varchar(32) COLLATE utf8mb4_bin DEFAULT NULL,
  `fleche_fc` varchar(1) COLLATE utf8mb4_bin DEFAULT NULL,
  `gommette` varchar(3) COLLATE utf8mb4_bin NOT NULL,
  `sensation` varchar(256) COLLATE utf8mb4_bin DEFAULT NULL,
  `temperature` decimal(4,2) unsigned DEFAULT NULL,
  `heure_temp` time DEFAULT NULL,
  `jour_sommet` tinyint(1) unsigned DEFAULT NULL,
  `union_sex` tinyint(1) unsigned DEFAULT NULL,
  `premier_jour` tinyint(1) unsigned DEFAULT NULL,
  `grossesse` tinyint(1) unsigned DEFAULT NULL,
  `commentaire` varchar(256) COLLATE utf8mb4_bin DEFAULT NULL,
  `dernier_modif` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`no_observation`),
  KEY `no_compte` (`no_compte`),
  KEY `date_obs` (`date_obs`),
  CONSTRAINT `observation_ibfk_1` FOREIGN KEY (`no_compte`) REFERENCES `compte` (`no_compte`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `jetton` (
  `no_jetton` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `no_compte` mediumint(8) unsigned DEFAULT NULL,
  `nom` varchar(256) COLLATE utf8mb4_bin NOT NULL,
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

