<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

function date_sql($date) {
	return date_format($date, 'Y-m-d');
}

function date_humain($date, $sep='/') {
	return date_format($date, 'd' . $sep . 'm' . $sep . 'y');
}

function date_humain_week_day($date, $week_days, $sep='/') {
	return $week_days[intval(date_format($date, 'w'))] . date_format($date, ' d' . $sep . 'm');
}
