# devlms upgrade reports

Moodle 4.5.2+ theke **5.3 LTS**-e upgrade korar shomoy ja ja kaj hoy, tar shob report ei folder-e thakbe.

- **Dev:** WSL `/home/milton/Projects/devlms`, URL http://devlms.local
- **Production:** codebase eki, kintu database alada (220GB+)

## File-gulo

| File | Ki ache |
|---|---|
| [00-upgrade-guideline-moodle-5.3.md](00-upgrade-guideline-moodle-5.3.md) | Pura upgrade plan ar step-gulo |
| [PRODUCTION-CHECKLIST.md](PRODUCTION-CHECKLIST.md) | Production-e haate ja korte hobe tar running list (DB ba server change, ja git diye jay na) |
| [01-2026-11-26-remove-local_blcreport.md](01-2026-11-26-remove-local_blcreport.md) | local_blcreport remove |
| [02-2026-11-26-edwiser-5.3.1-package-audit.md](02-2026-11-26-edwiser-5.3.1-package-audit.md) | Edwiser RemUI 5.3.1 package audit |
| [03-2026-11-26-edwiser-site-monitor-and-reports-audit.md](03-2026-11-26-edwiser-site-monitor-and-reports-audit.md) | Site Monitor ar Edwiser Reports audit |
| [04-2026-11-26-site-monitor-replace-and-edwiserreports-install-check.md](04-2026-11-26-site-monitor-replace-and-edwiserreports-install-check.md) | Site Monitor replace ar Edwiser Reports install check, ar feature list |
| [05-2026-11-26-moodle-5.3-upgrade-dev.md](05-2026-11-26-moodle-5.3-upgrade-dev.md) | ⭐ Dev-e Moodle 5.3.1+ upgrade: backup, plugin, custom code port, rollback |
| [06-2026-11-26-local_aws-edwiser-vendor-decisions.md](06-2026-11-26-local_aws-edwiser-vendor-decisions.md) | local_aws rakha, Edwiser git-e, vendor-er option |
| [07-2026-11-26-remove-local_aws.md](07-2026-11-26-remove-local_aws.md) | local_aws remove |
| [08-2026-11-26-tiny-wiris-and-cron.md](08-2026-11-26-tiny-wiris-and-cron.md) | MathType for TinyMCE install ar dev cron setup |
| [09-2026-11-26-external-plugins-status.md](09-2026-11-26-external-plugins-status.md) | External plugin-er list: installed, active ar inactive (24ta) |
| [10-2026-11-26-commit-upgrade-push-blocked.md](10-2026-11-26-commit-upgrade-push-blocked.md) | Upgrade commit (local). Repo public, tai push atkano |
| [11-2026-11-26-merge-and-push-main.md](11-2026-11-26-merge-and-push-main.md) | `main`-e merge ar push |
| `NN-YYYY-MM-DD-<kaj>.md` | Prottek kajer alada report |

## Prottek report-e ja thakbe

1. Ki kora hoyeche ar keno
2. Kon file, DB ba config bodlano hoyeche
3. Kivabe verify kora hoyeche
4. **Production-e ki korte hobe.** Git-e jay emon change ar haate korte hoy emon change alada kore lekha thakbe
5. Rollback-er upay

## Note

5.3-e web root `public/`, tai ei folder browser theke dekha jay na. Ekhon eta git-e commit kora hoy.
