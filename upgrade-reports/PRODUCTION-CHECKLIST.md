# Production checklist

Production-e haate korte hobe emon kaj, kromanushare. Kaj shesh hole `[x]` din, sathe tarikh likhe rakhun.

> Git diye shudhu code change jay. DB, nginx ba server-er change git-e jay na, tai sheigulo ekhane lekha thake.

## A. 5.3 upgrade-er AGE (4.5 thakar shomoy)

| # | Kaj | Command / ki dekhte hobe | Report | Done |
|---|---|---|---|---|
| A1 | `local_blcreport` uninstall | Commit `2e912220` pull korar por: `php admin/cli/uninstall_plugins.php --plugins=local_blcreport --run` | [01](01-2026-11-26-remove-local_blcreport.md) | [ ] |
| A2 | Server requirement | PHP 8.3+ (8.4 OK), MySQL 8.4+, 64-bit PHP, composer | [05](05-2026-11-26-moodle-5.3-upgrade-dev.md) | [ ] |
| A3 | **mod_chat / mod_survey data check** | `SELECT COUNT(*) FROM <prefix>chat_messages;` `SELECT COUNT(*) FROM <prefix>survey_answers;`. Data thakle **export korun**. Upgrade egulo auto-delete kore dey | 05 | [ ] |
| A4 | Custom plugin-er usage | `<prefix>quizaccess_proctoringlms`, `<prefix>googlemeet`, `<prefix>local_agreements`-er row count. Ar `course_contents` block-er instance koyta | 05 | [ ] |
| A4b | **S3 file system check** | Production `config.php`-e `$CFG->alternative_file_system_class` ache kina dekhun. Thakle site-er shob file S3-e. objectfs-er settings note korun | [06](06-2026-11-26-local_aws-edwiser-vendor-decisions.md) | [ ] |
| A5 | Boro table-er size | Guideline Step 2-er query. `logstore_standard_log` boro hole age cleanup korun | 00 | [ ] |
| A6 | Staging rehearsal | Production DB copy diye pura upgrade chalan, ar **koto shomoy lage note korun**. **S3 file upload ar download** test korun | 00, 06 | [ ] |
| A7 | Full backup | DB, moodledata, code, nginx config | 05 | [ ] |

## B. 5.3 upgrade-er dine

| # | Kaj | Command | Report | Done |
|---|---|---|---|---|
| B1 | Maintenance on | `php admin/cli/maintenance.php --enable` | 05 | [ ] |
| B2 | Code deploy | `upgrade/moodle-5.3` branch, `vendor/` shoho (commit kora na thakle `composer install --no-dev`) | 05 | [ ] |
| B3 | `config.php`, `rsa_keys_blc_for_diu/` | Root-e thakbe. `diuinformxyz1230.php` thakbe `public/`-e | 05 | [ ] |
| B4 | DB upgrade | `php admin/cli/upgrade.php --non-interactive` | 05 | [ ] |
| B5 | Nginx | root `.../public`, `try_files $uri $uri/ /r.php;`, ar php location `~ [^/]\.php(/|$)` | 05 | [ ] |
| B6 | Router | `config.php`-e `$CFG->routerconfigured = true;` | 05 | [ ] |
| B7 | Cache purge, check, cron | `php admin/cli/purge_caches.php`, `php admin/cli/checks.php`, `php admin/cli/cron.php` | 05 | [ ] |
| B8 | Maintenance off | `php admin/cli/maintenance.php --disable` | 05 | [ ] |
| B8b | `local_aws` uninstall | `php admin/cli/uninstall_plugins.php --plugins=local_aws --run` (upgrade.php-er por) | [07](07-2026-11-26-remove-local_aws.md) | [ ] |
| B8c | Cron path check | Production crontab `.../admin/cli/cron.php` chalay kina dekhun (5.3-eo root-e ache). Upgrade-er por Site admin-e cron warning thakle check korun | [08](08-2026-11-26-tiny-wiris-and-cron.md) | [ ] |
| B9 | Edwiser Reports-er prothom cron | Server load monitor korun | 03, 04 | [ ] |
| B10 | Browser test | Report 05-er section 7-er checklist | 05 | [ ] |
