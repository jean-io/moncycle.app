<?php

function date_sql($date) {
	return date_format($date, 'Y-m-d');
}

function date_humain($date) {
	return date_format($date, 'd/m/y');
	return $d . $sep . $m . $sep . $date['year'];
}
