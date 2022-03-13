<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

function sec_motdepasse_aleatoire($taille=12){
	$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	$pass = [];
	$alphaLength = strlen($alphabet)-1;
	for ($i = 0; $i < $taille; $i++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass);
}

function sec_hash($text) {
	return password_hash($text, PASSWORD_BCRYPT);
}

