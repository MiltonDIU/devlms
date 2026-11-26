# Upgrade guideline: Moodle 4.5.2+ theke 5.3 LTS

> **Status (2026-11-26):** dev upgrade shesh, ekhon 5.3.1+. Branch `upgrade/moodle-5.3`, commit kora hoy ni. Final plugin list ar ja shikhlam ta report [05](05-2026-11-26-moodle-5.3-upgrade-dev.md)-e ache. Nicher table purono plan, final obostha report 05-e dekhun.

- **Toiri:** 2026-11-26
- **Current:** Moodle 4.5.2+ (Build 20250221), `$version = 2024100702.02`
- **Target:** Moodle 5.3 LTS (release October 2026, security support October 2029 porjonto)
- **Upgrade path:** 4.5 theke direct 5.3 jaoa jay, majhe 5.0, 5.1 ba 5.2 lagbe na

## Environment, 5.3-er requirement-er sathe (dev)

| | Dev | 5.3 chay | Status |
|---|---|---|---|
| PHP | 8.4 (FPM + CLI) | 8.3 minimum, 8.4 supported | ✅ |
| MySQL | 8.4.13 | 8.4 minimum (official page-e confirm korte hobe) | ✅ |
| Extensions | intl, sodium, zip, exif, soap, redis | standard | ✅ |
| Nginx root | `/home/milton/Projects/devlms` | `.../devlms/public` | ❌ bodlate hobe |

> Production-er PHP, MySQL ar web server-o alada kore check korte hobe.

## Third-party plugin (20ta, blcreport remove korar por)

| Group | Plugin | 5.3 status |
|---|---|---|
| Edwiser (paid) | theme_remui, format_remuiformat, local_edwiserpagebuilder, local_edwisersiteimporter, block_edwiseradvancedblock, block_edwiserratingreview, filter_edwiserpbf | ✅ 5.3.1 package pawa geche (`/home/milton/Projects/Edwiser-RemUI-5.3.1`), report [02](02-2026-11-26-edwiser-5.3.1-package-audit.md) dekhun |
| Edwiser (free) | block_edwiser_site_monitor | 🟡 latest-i installed (2.1.3), 5.3-e test kore dekhte hobe (report [03](03-2026-11-26-edwiser-site-monitor-and-reports-audit.md)) |
| Edwiser (notun) | local_edwiserreports 2.0.2 | 🆕 notun add hobe shudhu 5.3-e. Folder-er nam `edwiserreports` korte hobe (report 03) |
| Storage/AWS | tool_objectfs, repository_s3bucket, local_aws | ⏳ |
| Activity | mod_attendance, mod_questionnaire, mod_vpl, mod_googlemeet | ⏳ |
| Others | block_xp, availability_xp, block_course_contents, filter_wiris, local_agreement | ⏳ |

Core theke remove hoyeche: `mod_chat` ar `mod_survey` (5.0). Ei duita-te data thakle age export korun.

## Je file-gulo rakhte hobe

- `config.php`
- `rsa_keys_blc_for_diu/private.pem`: `login/index.php`-er RSA password decrypt-e use hoy
- `diuinformxyz1230.php`: phpinfo check-er jonno (5.3-e browser-e chalate chaile `public/`-e rakhte hobe)
- `devlms_db_backup.sql`: shudhu backup

## Boro jhuki

1. **Edwiser/RemUI:** ~~5.3 version na thakle site-er UI bhenge jabe~~. RemUI 5.3.1 package pawa geche, tai eta ar blocker na (report 02). Install korte hobe 5.3 core-er sathe ek sathe, **theme age**, tarpor baki plugin.
2. **`login/index.php` core hack (line 126-140):** 5.3-er core file diye replace korle RSA logic muche jabe. Tachara relative path `'../rsa_keys_blc_for_diu/private.pem'` `public/login/` theke ar kaj korbe na. Logic-ta custom auth plugin ba hook-e sorate hobe.
3. **220GB production DB:** 5.3 upgrade-e boro table alter hoy, tai upgrade-e koyek ghonta lagte pare. Staging-e production DB-er copy diye upgrade-er shomoy mepe nite hobe.
4. **`public/` structure (5.1 theke):** web root ar plugin-er location bodlabe, Nginx config-o update korte hobe.

## Step-gulo

### Step 1: Plugin compatibility audit
Uporer table-er prottek plugin-er 5.3-compatible version khuje ber korun. Na pele: replace, remove, na hoy wait.

### Step 2: Production DB analysis
```sql
SELECT table_name, ROUND((data_length+index_length)/1024/1024/1024,2) AS gb, table_rows
FROM information_schema.tables WHERE table_schema='<prod_db>'
ORDER BY (data_length+index_length) DESC LIMIT 20;
```
`mdl_logstore_standard_log` boro hole upgrade-er age purono log clean ba archive korar plan korun.

### Step 3: `login/index.php`-er RSA logic core theke sorano

### Step 4: Backup
```bash
mysqldump --single-transaction -u <user> -p <dbname> > ~/backup/devlms_pre53.sql
cp -a /home/milton/datadrive/moodledata ~/backup/moodledata_pre53
git tag pre-upgrade-4.5.2
```

### Step 5: Dev-e upgrade
```bash
git checkout -b upgrade/moodle-5.3
php admin/cli/maintenance.php --enable
```
1. `MOODLE_503_STABLE` code alada folder-e nin
2. Core file replace korun, kintu purono folder-er upor copy korben na. `config.php`, RSA key ar 5.3-compatible plugin-gulo (`public/<type>/`-e) bosan
3. `composer install --no-dev` (lagbe kina release notes-e confirm korun)
4. Nginx: `root .../devlms/public;` ar `/r.php` router fallback (5.3 install docs dekhun)
5. Upgrade ar cache purge:
   ```bash
   php public/admin/cli/upgrade.php --non-interactive
   php public/admin/cli/purge_caches.php
   php public/admin/cli/maintenance.php --disable
   ```
   (CLI-er exact path notun code-e dekhe nin)

### Step 6: Staging rehearsal
Production DB-er ekta full copy restore kore pura upgrade chalan, ar **upgrade-e koto shomoy lage ta note korun**.

### Step 7: Testing
- Site admin-er Notifications, Environment ar Plugins overview
- Login (normal ar `ENC::` encrypted password)
- RemUI theme, page builder, course format
- Quiz, assignment, VPL, questionnaire, attendance, Google Meet, XP
- S3/ObjectFS-e file upload ar download
- Cron ar scheduled task
- Debug mode-e deprecation warning

### Step 8: Production
Downtime announce korun, backup nin, rehearsal-er step-gulo chalan, `PRODUCTION-CHECKLIST.md`-er item-gulo shesh korun, tarpor tag din.

## Sources
- https://moodledev.io/general/releases/5.3
- https://docs.moodle.org/503/en/PHP
- https://docs.moodle.org/503/en/Upgrade
- https://endoflife.date/moodle
