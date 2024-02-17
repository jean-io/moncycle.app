ALTER TABLE `compte`
ADD `timeline_asc` tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `nb_co_echoue`,
ADD `recherche` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `donateur`;

ALTER TABLE `observation`
ADD `compteur` tinyint(1) unsigned NULL AFTER `jour_sommet`;
