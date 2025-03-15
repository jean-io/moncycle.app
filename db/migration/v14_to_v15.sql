ALTER TABLE `observation`
CHANGE `dernier_modif` `last_write_db` timestamp NULL ON UPDATE CURRENT_TIMESTAMP AFTER `commentaire`;

ALTER TABLE `observation`
ADD `last_write_client_UTC` timestamp NULL AFTER `commentaire`;

ALTER TABLE `compte`
CHANGE `recherche` `research` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `donateur`;
