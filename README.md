# MONCYCLE.APP

A menstrual cycle tracking app designed for natural birth control methods (aka natural family planning).

More info 👉 [https://moncycle.app](https://moncycle.app)  

Source code 👉 [https://github.com/moncycle-app/backend-api-web-app](https://github.com/moncycle-app/backend-api-web-app)  

Support us on Tipeee 👉 [https://fr.tipeee.com/moncycleapp](https://fr.tipeee.com/moncycleapp)  

---

### License

Creative Commons **CC BY-NC-SA**  

Attribution – Non-Commercial Use – Share Alike  

Full license details 👉 [https://creativecommons.org/licenses/by-nc-sa/4.0/](https://creativecommons.org/licenses/by-nc-sa/4.0/)  

Legal code 👉 [https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode.fr](https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode.fr)  

---

### Installation

To self-host your own instance of Moncycle.app, you can use:  
- [YunoHost](https://install-app.yunohost.org/?app=moncycle)  
- [Docker](https://hub.docker.com/r/jeanio/moncycle.app)  

> **Note for Docker users:** The database must be installed manually. The SQL file is available at [www_data/script/db/table.sql](https://github.com/moncycle-app/backend-api-web-app/blob/coding/www_data/script/db/table.sql)  

---

### System Requirements

Tested with:  
- **PHP 8.3**  
- **MariaDB 11.1.3**  

---

### Security

- The `www-data/script` directory **must be protected** and should **not** be publicly accessible.  
- The script `www-data/script/cron.php` deletes expired session tokens. It is crucial to run it **once per day**:  
  - More than once per day → may send duplicate emails.  
  - Less than once per day → expired tokens may not be deleted on time, causing missed emails.  

---

### Docker Environment Variables

| Variable | Description |
|----------|-------------|
| DB_HOST | MariaDB server hostname |
| DB_ID | MariaDB login ID |
| DB_NAME | MariaDB database name |
| DB_PORT | MariaDB connection port |
| DB_PASSWORD | MariaDB password |
| SMTP_HOST | SMTP server hostname |
| SMTP_PORT | SMTP server port |
| SMTP_MAIL | SMTP email address (also used for authentication) |
| SMTP_PASSWORD | SMTP password |
| CREATION_COMPTE | Enable account creation for MONCYCLE.APP (boolean, default: true) |
| CONNEXION_COMPTE | Enable authentication for MONCYCLE.APP (boolean, default: true) |
| CSV_SEP | Separator for CSV exports |
| APP_URL | URL of the hosted app (used for correct links in emails) |
| PHP_CACHE | Enable PHP OPcache (default: `On`) |
| PHP_SHOW_ERR | Show PHP errors in browser (default: `Off`) |
| PHP_SECURE_COOKIES | Restrict cookies to HTTPS only (default: `On`) |

> For development, it is recommended to disable cache, display PHP errors, and disable cookie security.
