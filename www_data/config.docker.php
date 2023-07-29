<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

define("DB_HOST", $_ENV["DB_HOST"] ?? "");
define("DB_ID",   $_ENV["DB_ID"]   ?? "");
define("DB_NAME", $_ENV["DB_NAME"] ?? "");
define("DB_PORT", $_ENV["DB_PORT"] ?? "");
define("DB_PASSWORD", $_ENV["DB_PASSWORD"] ?? "");

define("SMTP_HOST", $_ENV["SMTP_HOST"] ?? "");
define("SMTP_PORT", $_ENV["SMTP_PORT"] ?? "");
define("SMTP_MAIL", $_ENV["SMTP_MAIL"] ?? "");
define("SMTP_PASSWORD", $_ENV["SMTP_PASSWORD"] ?? "");

define("CREATION_COMPTE",  isset($_ENV["CREATION_COMPTE"])  ? filter_var($_ENV["CREATION_COMPTE"],  FILTER_VALIDATE_BOOLEAN) : true);
define("CONNEXION_COMPTE", isset($_ENV["CONNEXION_COMPTE"]) ? filter_var($_ENV["CONNEXION_COMPTE"], FILTER_VALIDATE_BOOLEAN) : true);

define("CSV_SEP", $_ENV["CSV_SEP"] ?? ";");


