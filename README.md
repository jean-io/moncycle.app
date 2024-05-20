# MONCYCLE.APP

Application de suivi de cycle menstruel pour les méthodes naturelles de régulation de naissance.

Plus d'information 👉 [https://moncycle.app](https://moncycle.app)

Code source 👉 [https://github.com/jean-io/moncycle.app](https://github.com/jean-io/moncycle.app)

Page Tipeee 👉 [https://fr.tipeee.com/moncycleapp](https://fr.tipeee.com/moncycleapp)

### Licence

Creative Commons **CC BY-NC-SA**

Attribution - Utilisation non commerciale - Partage dans les mêmes conditions

Détail de la licence 👉 [https://creativecommons.org/licenses/by-nc-sa/4.0/](https://creativecommons.org/licenses/by-nc-sa/4.0/)

Code légal 👉 [https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode.fr](https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode.fr)

### Installation

Si vous souhaitez auto-héberger votre propre instance Moncycle.app, vous pouvez utiliser:
- [YunoHost](https://install-app.yunohost.org/?app=moncycle)
- [Docker](https://hub.docker.com/r/jeanio/moncycle.app)

Pour Docker, il faudra installer manuellement la base de donnée. Le fichier SQL est dispnnible à l'empalcement [db/table.sql](https://github.com/jean-io/moncycle.app/blob/master/db/table.sql)

### Prérequis système

L'appli a été testée sous **PHP 8.3** et **MariaDB 11.1.3**.

### Sécurité

Le répertoire `www-data/script` doit être protégé et ne doit pas être accessible publiquement.

Le script `www-data/script/cron.php` a pour fonction de supprimer les jetons de session expirés, il est donc important pour des raisons de sécurité de bien l'executer **une fois par jour**. La fréquence d'une fois par jour est importante: plus d'une fois par jours, des mails pourraient être expédiés en doublon. Moins d'une fois par jours, des jetons expirés seraient supprimé trop tardivement et des envois de mails seraient manquants.

### Variables Docker

|Variable name    | Variable description |
|-----------------|----------------------|
|DB_HOST          | MariaDB server hostname |
|DB_ID            | MariaDB login id |
|DB_NAME          | MariaDB database name |
|DB_PORT          | MariaDB connection port |
|DB_PASSWORD      | MariaDB password |
|SMTP_HOST        | SMTP server hostname |
|SMTP_PORT        | SMTP server connection port |
|SMTP_MAIL        | SMTP mail address (which is also used for identification) |
|SMTP_PASSWORD    | SMTP password |
|CREATION_COMPTE  | allow account creation for MONCYCLE.APP (boolean, default: true) |
|CONNEXION_COMPTE | allow authentification in MONCYCLE.APP (boolean, default: true) |
|CSV_SEP          | separator for CSV export |
|APP_URL | URL of hosted app in order to be correct in mails |
|PHP_CACHE | activate PHP OPcache, default `On` |
|PHP_SHOW_ERR | show php errors in browser, default `Off` |
|PHP_SECURE_COOKIES | allow cookie set up only in HTTPS, default `On` |

Pour le developpement de l'application, pensez à désactiver le cache, à afficher les erreurs PHP et à désactiver la securité sur les cookies.
