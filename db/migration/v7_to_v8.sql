ALTER TABLE `compte`
ADD `totp_etat` smallint(5) unsigned DEFAULT NULL;

ALTER TABLE `compte`
ADD `totp_secret` varchar(255) DEFAULT NULL;

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

CREATE TABLE `cle_valeur` (
  `cle` varchar(255) NOT NULL,
  `valeur` bigint(20) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

INSERT INTO `cle_valeur` (`cle`, `valeur`) VALUES
('pub_visite_mensuel',	0),
('pub_visite_hebdo',	0),
('pub_visite_jour',	0);
