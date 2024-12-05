-- Rename tables
RENAME TABLE `compte` TO `account`,
             `jetton` TO `token`,
             `cle_valeur` TO `key_value`;

-- Drop foreign key constraints temporarily
ALTER TABLE `observation` DROP FOREIGN KEY `observation_ibfk_1`;
ALTER TABLE `token` DROP FOREIGN KEY `observation_ibfk_2`;

-- Alter `account` table
ALTER TABLE `account` 
  CHANGE COLUMN `no_compte` `id_account` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  CHANGE COLUMN `nom` `name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  CHANGE COLUMN `methode` `nfp_method` smallint(5) unsigned NOT NULL DEFAULT 1,
  CHANGE COLUMN `motdepasse` `password` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  CHANGE COLUMN `totp_etat` `totp_status` tinyint(1) unsigned NOT NULL DEFAULT 0,
  CHANGE COLUMN `nb_co_echoue` `failed_login` smallint(5) unsigned NOT NULL DEFAULT 0,
  CHANGE COLUMN `donateur` `donor` tinyint(1) unsigned NOT NULL DEFAULT 0,
  CHANGE COLUMN `recherche` `research` tinyint(1) unsigned NOT NULL DEFAULT 0,
  CHANGE COLUMN `actif` `disabled` tinyint(1) unsigned NOT NULL DEFAULT 1,
  CHANGE COLUMN `relance` `followup` tinyint(1) unsigned NOT NULL DEFAULT 1,
  CHANGE COLUMN `derniere_co_date` `last_login_date` timestamp NULL DEFAULT NULL,
  CHANGE COLUMN `inscription_date` `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  CHANGE COLUMN `mdp_change_date` `password_change_date` timestamp NULL DEFAULT NULL,
  CHANGE COLUMN `decouvert` `how_discovered` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL;

-- Alter `observation` table
ALTER TABLE `observation` 
  CHANGE COLUMN `no_observation` `id_observation` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  CHANGE COLUMN `no_compte` `id_account` mediumint(8) unsigned NOT NULL,
  CHANGE COLUMN `date_obs` `observation_date` date NOT NULL DEFAULT '0000-00-00',
  CHANGE COLUMN `jenesaispas` `i_dont_know` tinyint(1) unsigned DEFAULT NULL,
  CHANGE COLUMN `fleche_fc` `arrow_fc` varchar(1) COLLATE utf8mb4_bin DEFAULT NULL,
  CHANGE COLUMN `gommette` `stamp` varchar(3) COLLATE utf8mb4_bin NOT NULL,
  CHANGE COLUMN `sensation` `feeling` varchar(256) COLLATE utf8mb4_bin DEFAULT NULL,
  CHANGE COLUMN `heure_temp` `temperature_time` time DEFAULT NULL,
  CHANGE COLUMN `jour_sommet` `peak` tinyint(1) unsigned DEFAULT NULL,
  CHANGE COLUMN `compteur` `counter` tinyint(1) unsigned DEFAULT NULL,
  CHANGE COLUMN `union_sex` `sexual_union` tinyint(1) unsigned DEFAULT NULL,
  CHANGE COLUMN `premier_jour` `cycle_first_day` tinyint(1) unsigned DEFAULT NULL,
  CHANGE COLUMN `grossesse` `pregnancy` tinyint(1) unsigned DEFAULT NULL,
  CHANGE COLUMN `commentaire` `comment` varchar(256) COLLATE utf8mb4_bin DEFAULT NULL,
  CHANGE COLUMN `dernier_modif` `last_modified_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  DROP INDEX `unique_compte_and_date`,
  ADD UNIQUE INDEX `unique_account_and_date` (`id_account`, `observation_date`);

-- Add foreign key constraints back
ALTER TABLE `observation` ADD CONSTRAINT `observation_ibfk_1` FOREIGN KEY (`id_account`) REFERENCES `account` (`id_account`) ON DELETE CASCADE;

-- Alter `token` table
ALTER TABLE `token` 
  CHANGE COLUMN `no_jetton` `id_token` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  CHANGE COLUMN `no_compte` `id_account` mediumint(8) unsigned DEFAULT NULL,
  CHANGE COLUMN `expire` `expired` tinyint(1) unsigned NOT NULL DEFAULT 0,
  CHANGE COLUMN `pays` `country` varchar(2) COLLATE utf8mb4_bin DEFAULT NULL,
  CHANGE COLUMN `date_creation` `creation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  CHANGE COLUMN `date_use` `use_date` timestamp NULL DEFAULT NULL,
  CHANGE COLUMN `jetton_str` `token_str` varchar(512) COLLATE utf8mb4_bin NOT NULL,
  DROP INDEX `jetton_str`,
  ADD UNIQUE INDEX `token_str` (`token_str`);

-- Add foreign key constraints back
ALTER TABLE `token` ADD CONSTRAINT `observation_ibfk_2` FOREIGN KEY (`id_account`) REFERENCES `account` (`id_account`) ON DELETE CASCADE;

-- Alter `key_value` table
ALTER TABLE `key_value`
  CHANGE COLUMN `cle` `key` varchar(255) NOT NULL,
  CHANGE COLUMN `valeur` `value` bigint(20) unsigned DEFAULT NULL;

-- Insert initial data into `key_value` table
INSERT INTO `key_value` (`key`, `value`) VALUES
('monthly_public_visits', 0),
('weekly_public_visits', 0),
('daily_public_visits', 0)
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);