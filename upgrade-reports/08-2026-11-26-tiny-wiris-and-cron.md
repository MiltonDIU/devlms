# 08: MathType for TinyMCE (tiny_wiris) install ar dev cron setup

- **Tarikh:** 2026-11-26
- **Environment:** dev (5.3.1+, branch `upgrade/moodle-5.3`)
- **Commit:** nei

## Shomossa

Site administration-e duita warning dekhachhilo:
1. "MathType for TinyMCE are not installed"
2. "The admin/cli/cron.php script has not been run for 13 mins..."

## 1. MathType for TinyMCE

**Karon:** 4.5-e MathType chilo Atto editor-er plugin (`atto_wiris`). 5.x-e Atto remove hoye geche, tai `filter_wiris` ache kintu editor-er plugin nei. TinyMCE-er jonno alada plugin `tiny_wiris` lage.

**Ja kora hoyeche:**
- moodle.org theke `tiny_wiris` **8.12.0** (2026110900) nea hoyeche. Eta 5.3-supported, ar `filter_wiris` 8.12.0 (2026110900) chay, ja amader ache.
- Bosano hoyeche `public/lib/editor/tiny/plugins/wiris`-e
- `php admin/cli/upgrade.php --non-interactive`: Success (0.34s)
- Cache purge kora hoyeche
- DB-te `tiny_wiris` version 2026110900

**Browser-e test korte hobe:** kono activity-te TinyMCE editor-e MathType (√) button dekhay kina, ar equation insert kore save korle dekhay kina.

## 2. Cron (shudhu dev)

**Karon:** dev-e kono crontab chilo na. Cron daemon cholchilo, kintu Moodle-er cron kew chalacchilo na.

**Ja kora hoyeche:** `milton` user-er crontab-e ei line add kora hoyeche:
```
* * * * * /usr/bin/php /home/milton/Projects/devlms/admin/cli/cron.php > /tmp/devlms_cron.log 2>&1
```
- Verify: 14:16:31-e cron nijei cholte shuru koreche (`/tmp/devlms_cron.log`)
- Shesh run-er output `/tmp/devlms_cron.log`-e thake, prottek run-e overwrite hoy

**Note:**
- Cron `milton` user hishebe chole, `www-data` hishebe na. sudo password chara www-data-r crontab-e line add kora jay na. moodledata-r folder-gulo 777, tai shomossa howar kotha na. Tobe permission error dekhle `sudo crontab -u www-data -e`-e line-ta sorie nin.
- Dev-e mail server nei, tai cron log-e email error dekhabe. Eta expected.
- Cron off korte: `crontab -e`, tarpor line-ta muche din.

## Production-e korte hobe

| Kaj | Kivabe |
|---|---|
| tiny_wiris | Code deploy-er sathe chole jabe (`public/lib/editor/tiny/plugins/wiris`). `upgrade.php`-e install hobe |
| Cron | Production-e cron age thekei thakar kotha. Path bodlay ni, `admin/cli/cron.php` root-ei ache. Shudhu dekhe nin crontab-er path `.../admin/cli/cron.php` ache kina |

## Rollback

- tiny_wiris: `php admin/cli/uninstall_plugins.php --plugins=tiny_wiris --run && rm -rf public/lib/editor/tiny/plugins/wiris`
- Cron: `crontab -e`, tarpor line-ta muche din
