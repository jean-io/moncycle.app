ALTER TABLE `observation`
CHANGE `dernier_modif` `last_write_db` timestamp NULL ON UPDATE CURRENT_TIMESTAMP AFTER `commentaire`;

ALTER TABLE `observation`
ADD `last_write_client_UTC` timestamp NULL AFTER `commentaire`;
