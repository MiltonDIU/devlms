# 03: Edwiser Site Monitor ar Edwiser Reports zip audit

- **Tarikh:** 2026-11-26
- **Environment:** dev
- **Location:** `/home/milton/Projects/Edwiser-RemUI-5.3.1/` (user download kore diyechen)
- **Ki kora hoyeche:** shudhu audit. **Kichu install ba change kora hoy ni.**

## Notun file

| File | Ki |
|---|---|
| `edwiser_site_monitor.zip` | block_edwiser_site_monitor |
| `moodle-local_edwiserreports-free-main.zip` | local_edwiserreports (free version, GitHub "main" branch-er zip) |
| `Edwiser-Site-Monitor-Documentation.pdf` | Documentation |
| `Edwiser-Reports-Documentation-link.pdf` | Shudhu link: https://edwiser.org/documentation/edwiser-reports/ |

## Result

| Plugin | Installed (dev) | Zip-e | Note |
|---|---|---|---|
| block_edwiser_site_monitor | 2.1.3 (2026042200) | 2.1.3 (2026042200) | Zip-er `version.php` ar installed copy-r `version.php` **hubohu eki** (diff kore dekha). Mane etai latest, notun kichu nei |
| local_edwiserreports | **install kora nei** | 2.0.2 (2026100900), requires 2025041400 (Moodle 5.0+) | **Notun plugin.** 4.5-e install hobe na, shudhu 5.3-e |

## Joruri note

1. **Site monitor:** amra je version use korchi seta-i latest. 5.3-e chole kina seta zip dekhe bola jay na, dev upgrade-er por test kore dekhte hobe. `version.php`-e `$plugin->cron = 300` ache. Eta purono legacy cron setting, tai 5.3-e debug warning dite pare, kintu install atkabe na. Kaj na korle remove kora jete pare, eta monitoring block, site-er main feature na.
2. **Edwiser Reports:**
   - Zip-er vitorer folder-er nam `moodle-local_edwiserreports-free-main`. Install-er shomoy folder-tar nam **`edwiserreports`** korte hobe: `public/local/edwiserreports`. Nam na bodlale Moodle plugin-ta chinte parbe na.
   - Eta current site-e nei, tai eta notun feature add kora. Kono data migrate korte hobe na, shudhu notun table toiri hobe.
   - 220GB DB-te report plugin prothombar cron-e onek data process korte pare. Production-e install korar por prothom cron run-e load monitor korun.

## Production-e korte hobe

Ekhon kichu na. 5.3 upgrade-er shomoy (guideline Step 5):
- `public/blocks/edwiser_site_monitor`: same version, kono change nei
- `public/local/edwiserreports`: notun (jodi add korar shiddhanto thake)

## Rollback

Kono change kora hoy ni, tai rollback-er dorkar nei.
