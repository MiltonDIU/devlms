# 10: Upgrade commit (local), push atkano hoyeche

- **Tarikh:** 2026-11-26
- **Branch:** `upgrade/moodle-5.3`
- **Commit:** `51702b0d` "Upgrade Moodle 4.5.2+ to 5.3.1+ (Build: 20261124)", author Md. Milton.
- **Push:** prothome atkano hoyechilo (nicher karon). Pore user shiddhanto dilen: **repo public thakbe** (Edwiser license repo-te deya hoy ni), ar **`main`-e push kora hobe**. Report 11 dekhun.

## Keno push kora hoy ni

`https://api.github.com/repos/MiltonDIU/devlms` login chara `200` dey, mane **repo ekhono public**. Commit-e Edwiser-er paid code ache (theme_remui, format_remuiformat, page builder, advanced block, rating review, pbf filter, site importer, edwiserreports). Public repo-te push korle eta license bhongo hobe, ar code GitHub-e cache ba fork hoye theke jete pare.

## Push-er age check

| Check | Result |
|---|---|
| `config.php` (DB password) | gitignore-e ache, commit-e nei ✅ |
| `rsa_keys_blc_for_diu/private.pem` | gitignore-e ache, commit-e nei ✅ (`public.pem` age theke tracked) |
| `devlms_db_backup.sql` | gitignore-e ache ✅ |
| `upgrade-reports/` | gitignore-e ache ✅ |
| 20MB-er beshi file | Nei ✅ |
| `vendor/` (73MB) | **Commit-e ache** (Option A, report 06) |
| Commit size | 90,460ta file change. Beshirbhag file root theke `public/`-e sorano |

## Push korar step (repo private korar por)

1. GitHub > `MiltonDIU/devlms` > Settings > General > Danger Zone > **Change visibility > Private**
2. Check korun: `curl -s -o /dev/null -w "%{http_code}" https://api.github.com/repos/MiltonDIU/devlms` dile `404` ashbe
3. Push:
   ```bash
   git push -u origin upgrade/moodle-5.3
   ```
   `main`-e merge kora hoy ni. Production-e deploy korar age merge ba PR-er shiddhanto apnar.

## Production-e korte hobe

Push ba merge-er por PRODUCTION-CHECKLIST onujayi.

## Rollback (local commit)

```bash
git reset --soft HEAD~1   # commit undo, change-gulo staged obosthay thakbe
```
