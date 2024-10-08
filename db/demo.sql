-- moncycle.app
--
-- licence Creative Commons CC BY-NC-SA
--
-- https://www.moncycle.app
-- https://github.com/jean-io/moncycle.app

SET NAMES utf8mb4;

DELETE FROM `account` WHERE `id_account` = '2';
DELETE FROM `account` WHERE `id_account` = '3';

INSERT INTO `account` (`id_account`, `name`, `age`, `email1`, `password`, `disabled`, `nfp_method`) VALUES
(2, 'Démo Billings', 1990, 'demo.bill@moncycle.app', '$2y$10$hTn9Xjg4wk/ovWEY8BWXau.Y1ODRoX03c2zlp6Rnmib1yUcVpp0sC', 1, 2),
(3, 'Démo FertilityCare', 1990, 'demo.fc@moncycle.app', '$2y$10$hTn9Xjg4wk/ovWEY8BWXau.Y1ODRoX03c2zlp6Rnmib1yUcVpp0sC', 1, 3);


INSERT INTO `observation` (`id_observation`, `id_account`, `observation_date`, `i_dont_know`, `note_fc`, `arrow_fc`, `stamp`, `feeling`, `temperature`, `temperature_time`, `peak`, `counter`, `sexual_union`, `cycle_first_day`, `pregnancy`, `comment`) VALUES
(0+50,	2,	CURDATE() + INTERVAL - (50-0) DAY,	NULL,	NULL,	NULL,	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	''),
(1+50,	2,	CURDATE() + INTERVAL - (50-1) DAY,	NULL,	NULL,	NULL,	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(2+50,	2,	CURDATE() + INTERVAL - (50-2) DAY,	NULL,	NULL,	NULL,	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(3+50,	2,	CURDATE() + INTERVAL - (50-3) DAY,	1,	'',	'',	'',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(4+50,	2,	CURDATE() + INTERVAL - (50-4) DAY,	NULL,	'',	'',	'I:)',	'sec',	36.55,	NULL,	NULL,	3,	NULL,	NULL,	NULL,	''),
(5+50,	2,	CURDATE() + INTERVAL - (50-5) DAY,	NULL,	'',	'',	'I:)',	'sec',	36.50,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(6+50,	2,	CURDATE() + INTERVAL - (50-6) DAY,	1,	'',	'',	'',	'sec',	36.60,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(7+50,	2,	CURDATE() + INTERVAL - (50-7) DAY,	NULL,	NULL,	NULL,	'I',	'humide',	36.50,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(8+50,	2,	CURDATE() + INTERVAL - (50-8) DAY,	NULL,	NULL,	NULL,	':)',	'humide',	36.60,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'Je suis pas sûr'),
(9+50,	2,	CURDATE() + INTERVAL - (50-9) DAY,	NULL,	'',	'',	':)',	'humide',	36.65,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(10+50,	2,	CURDATE() + INTERVAL - (50-10) DAY,	NULL,	'',	'',	':)',	'humide',	36.60,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(11+50,	2,	CURDATE() + INTERVAL - (50-11) DAY,	NULL,	'',	'',	':)',	'humide',	36.65,	NULL,	1,	NULL,	NULL,	NULL,	NULL,	''),
(12+50,	2,	CURDATE() + INTERVAL - (50-12) DAY,	NULL,	'',	'',	':)',	'humide',	37.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'Un peu moins'),
(13+50,	2,	CURDATE() + INTERVAL - (50-13) DAY,	NULL,	'',	'',	'I:)',	'sec',	37.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(14+50,	2,	CURDATE() + INTERVAL - (50-14) DAY,	NULL,	'',	'',	'I:)',	'sec',	37.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(15+50,	2,	CURDATE() + INTERVAL - (50-15) DAY,	NULL,	'',	'',	'I',	'sec',	37.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(16+50,	2,	CURDATE() + INTERVAL - (50-16) DAY,	NULL,	NULL,	NULL,	'I',	NULL,	37.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(17+50,	2,	CURDATE() + INTERVAL - (50-17) DAY,	NULL,	NULL,	NULL,	'I',	NULL,	37.05,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(18+50,	2,	CURDATE() + INTERVAL - (50-18) DAY,	NULL,	NULL,	NULL,	'I',	NULL,	37.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(19+50,	2,	CURDATE() + INTERVAL - (50-19) DAY,	NULL,	NULL,	NULL,	'I',	NULL,	37.01,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(20+50,	2,	CURDATE() + INTERVAL - (50-20) DAY,	NULL,	NULL,	NULL,	'I',	NULL,	37.00,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(21+50,	2,	CURDATE() + INTERVAL - (50-21) DAY,	1,	NULL,	NULL,	'',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(22+50,	2,	CURDATE() + INTERVAL - (50-22) DAY,	NULL,	NULL,	NULL,	'I',	NULL,	37.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(23+50,	2,	CURDATE() + INTERVAL - (50-23) DAY,	NULL,	NULL,	NULL,	'I',	NULL,	37.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(24+50,	2,	CURDATE() + INTERVAL - (50-24) DAY,	NULL,	NULL,	NULL,	'I',	NULL,	37.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(25+50,	2,	CURDATE() + INTERVAL - (50-25) DAY,	NULL,	'',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	''),
(26+50,	2,	CURDATE() + INTERVAL - (50-26) DAY,	NULL,	'',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(27+50,	2,	CURDATE() + INTERVAL - (50-27) DAY,	NULL,	'',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(28+50,	2,	CURDATE() + INTERVAL - (50-28) DAY,	NULL,	'',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(29+50,	2,	CURDATE() + INTERVAL - (50-29) DAY,	NULL,	'',	'',	'=',	'collant',	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	'avec traces rouges'),
(30+50,	2,	CURDATE() + INTERVAL - (50-30) DAY,	1,	'',	'',	'=',	'collant',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(31+50,	2,	CURDATE() + INTERVAL - (50-31) DAY,	NULL,	'',	'',	'=',	'collant',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(32+50,	2,	CURDATE() + INTERVAL - (50-32) DAY,	NULL,	'',	'',	'=',	'collant, pâteux',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(33+50,	2,	CURDATE() + INTERVAL - (50-33) DAY,	NULL,	'',	'',	':)',	'humide, collant, pâteux',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(34+50,	2,	CURDATE() + INTERVAL - (50-34) DAY,	NULL,	'',	'',	':)',	'humide, pâteux',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(35+50,	2,	CURDATE() + INTERVAL - (50-35) DAY,	NULL,	'',	'',	':)',	'humide',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(36+50,	2,	CURDATE() + INTERVAL - (50-36) DAY,	NULL,	'',	'',	':)',	'humide, glissant, blanc',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(37+50,	2,	CURDATE() + INTERVAL - (50-37) DAY,	NULL,	'',	'',	':)',	'humide, glissant',	NULL,	NULL,	1,	NULL,	NULL,	NULL,	NULL,	'douleur abdomen à droite'),
(38+50,	2,	CURDATE() + INTERVAL - (50-38) DAY,	NULL,	'',	'',	'I:)',	'sec',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(39+50,	2,	CURDATE() + INTERVAL - (50-39) DAY,	NULL,	'',	'',	'I:)',	'sec',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(40+50,	2,	CURDATE() + INTERVAL - (50-40) DAY,	NULL,	'',	'',	'I:)',	'sec',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(41+50,	2,	CURDATE() + INTERVAL - (50-41) DAY,	NULL,	'',	'',	'I',	'sec',	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(42+50,	2,	CURDATE() + INTERVAL - (50-42) DAY,	NULL,	'',	'',	'I',	'sec',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(43+50,	2,	CURDATE() + INTERVAL - (50-43) DAY,	NULL,	'',	'',	'I',	'sec',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(44+50,	2,	CURDATE() + INTERVAL - (50-44) DAY,	NULL,	'',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(45+50,	2,	CURDATE() + INTERVAL - (50-45) DAY,	NULL,	'',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(46+50,	2,	CURDATE() + INTERVAL - (50-46) DAY,	NULL,	'',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(47+50,	2,	CURDATE() + INTERVAL - (50-47) DAY,	NULL,	'',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(48+50,	2,	CURDATE() + INTERVAL - (50-48) DAY,	NULL,	'',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(49+50,	2,	CURDATE() + INTERVAL - (50-49) DAY,	NULL,	'',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'');



INSERT INTO `observation` (`id_observation`, `id_account`, `observation_date`, `i_dont_know`, `note_fc`, `arrow_fc`, `stamp`, `feeling`, `temperature`, `temperature_time`, `peak`, `counter`, `sexual_union`, `cycle_first_day`, `pregnancy`, `comment`) VALUES
(1,	3,	CURDATE() + INTERVAL - (50-1) DAY,	NULL,	'H AP',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	''),
(2,	3,	CURDATE() + INTERVAL - (50-2) DAY,	NULL,	'M',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(3,	3,	CURDATE() + INTERVAL - (50-3) DAY,	NULL,	'MB',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(4,	3,	CURDATE() + INTERVAL - (50-4) DAY,	NULL,	'LB',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(5,	3,	CURDATE() + INTERVAL - (50-5) DAY,	NULL,	'VLB 4 X1',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(6,	3,	CURDATE() + INTERVAL - (50-6) DAY,	NULL,	'6CP X2',	'',	'=',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(7,	3,	CURDATE() + INTERVAL - (50-7) DAY,	NULL,	'8CP X1',	'',	'=',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(8,	3,	CURDATE() + INTERVAL - (50-8) DAY,	NULL,	'PY AD',	'',	'=',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(9,	3,	CURDATE() + INTERVAL - (50-9) DAY,	NULL,	'6PY X1',	'',	'=',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(10,	3,	CURDATE() + INTERVAL - (50-10) DAY,	NULL,	'8Y X2',	'',	'=',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(11,	3,	CURDATE() + INTERVAL - (50-11) DAY,	NULL,	'8KL X2',	'↑',	':)',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(12,	3,	CURDATE() + INTERVAL - (50-12) DAY,	NULL,	'10KL AD',	'',	':)',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(13,	3,	CURDATE() + INTERVAL - (50-13) DAY,	NULL,	'VL 10KL AD',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(14,	3,	CURDATE() + INTERVAL - (50-14) DAY,	NULL,	'10KL AD',	'',	':)',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(15,	3,	CURDATE() + INTERVAL - (50-15) DAY,	NULL,	'12KL X1 AP',	'',	':)',	NULL,	NULL,	NULL,	1,	NULL,	NULL,	NULL,	NULL,	''),
(16,	3,	CURDATE() + INTERVAL - (50-16) DAY,	NULL,	'8CP X3',	'↓',	'=:)',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(17,	3,	CURDATE() + INTERVAL - (50-17) DAY,	NULL,	'6Y X2',	'',	'=:)',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(18,	3,	CURDATE() + INTERVAL - (50-18) DAY,	NULL,	'6Y X1',	'',	'=:)',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(19,	3,	CURDATE() + INTERVAL - (50-19) DAY,	NULL,	'6PY X2',	'',	'=',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(20,	3,	CURDATE() + INTERVAL - (50-20) DAY,	NULL,	'6PY X3',	'',	'=',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(21,	3,	CURDATE() + INTERVAL - (50-21) DAY,	NULL,	'6CP X1',	'',	'=',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(22,	3,	CURDATE() + INTERVAL - (50-22) DAY,	NULL,	'6CP X2',	'',	'=',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(23,	3,	CURDATE() + INTERVAL - (50-23) DAY,	NULL,	'6CP X3',	'',	'=',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(24,	3,	CURDATE() + INTERVAL - (50-24) DAY,	NULL,	'10SL X1',	'',	'=',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(25,	3,	CURDATE() + INTERVAL - (50-25) DAY,	NULL,	'H AP',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	''),
(26,	3,	CURDATE() + INTERVAL - (50-26) DAY,	NULL,	'M',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(27,	3,	CURDATE() + INTERVAL - (50-27) DAY,	NULL,	'MB',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(28,	3,	CURDATE() + INTERVAL - (50-28) DAY,	NULL,	'LB 0 AD',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(29,	3,	CURDATE() + INTERVAL - (50-29) DAY,	NULL,	'2 X1',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(30,	3,	CURDATE() + INTERVAL - (50-30) DAY,	NULL,	'4 X2',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(31,	3,	CURDATE() + INTERVAL - (50-31) DAY,	NULL,	'0 AD',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(32,	3,	CURDATE() + INTERVAL - (50-32) DAY,	NULL,	'0 AD',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(33,	3,	CURDATE() + INTERVAL - (50-33) DAY,	NULL,	'0 X2',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(34,	3,	CURDATE() + INTERVAL - (50-34) DAY,	NULL,	'0 AD',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(35,	3,	CURDATE() + INTERVAL - (50-35) DAY,	NULL,	'0 AD',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(36,	3,	CURDATE() + INTERVAL - (50-36) DAY,	NULL,	'6C X1',	'',	':)',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(37,	3,	CURDATE() + INTERVAL - (50-37) DAY,	NULL,	'10C X3',	'',	':)',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(38,	3,	CURDATE() + INTERVAL - (50-38) DAY,	NULL,	'10KL AD',	'',	':)',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(39,	3,	CURDATE() + INTERVAL - (50-39) DAY,	NULL,	'10K X1 RAP',	'',	':)',	NULL,	NULL,	NULL,	1,	NULL,	NULL,	NULL,	NULL,	'nausées'),
(40,	3,	CURDATE() + INTERVAL - (50-40) DAY,	NULL,	'8C X1',	'',	':)',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(41,	3,	CURDATE() + INTERVAL - (50-41) DAY,	NULL,	'0 AD',	'',	'I:)',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(42,	3,	CURDATE() + INTERVAL - (50-42) DAY,	NULL,	'2 X1',	'',	'I:)',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(43,	3,	CURDATE() + INTERVAL - (50-43) DAY,	NULL,	'0 AD',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(44,	3,	CURDATE() + INTERVAL - (50-44) DAY,	NULL,	'0 AD',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(45,	3,	CURDATE() + INTERVAL - (50-45) DAY,	NULL,	'0 AD',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	''),
(46,	3,	CURDATE() + INTERVAL - (50-46) DAY,	NULL,	'0 X2',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(47,	3,	CURDATE() + INTERVAL - (50-47) DAY,	NULL,	'0 X1',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(48,	3,	CURDATE() + INTERVAL - (50-48) DAY,	NULL,	'0 AD',	'',	'I',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	''),
(49,	3,	CURDATE() + INTERVAL - (50-49) DAY,	NULL,	'VLB 0 AD',	'',	'.',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'');


