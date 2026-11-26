# 11: `upgrade/moodle-5.3` merge kore `main`-e push

- **Tarikh:** 2026-11-26
- **User-er shiddhanto:**
  - Repo public thakbe. Edwiser-er license key ba file repo-te nei, tai somossa nei.
  - `main` branch-e push kora hobe.
  - `.gitignore` theke `/upgrade-reports/` user nije muche diyechen, tai report-gulo-o git-e jabe. Ekhon web root `public/`, tai `upgrade-reports/` browser theke dekha jay na.

## Steps

1. Report-gulo commit kora (`upgrade-reports/`)
2. `main`-e `upgrade/moodle-5.3` fast-forward merge
3. `git push origin main`

## Result

- `origin/main`: `2e912220c..33d391057`
  - `51702b0d` Upgrade Moodle 4.5.2+ to 5.3.1+ (Build: 20261124)
  - `33d39105` Add Moodle 5.3 upgrade reports
- Author shudhu Md. Milton
- Local `main` ar `origin/main` ek jaygay. `upgrade/moodle-5.3` branch-o same commit-e (push kora hoy ni, local)

### Majhe ekta gondogol hoyechilo (thik kora hoyeche)
Push script-er majhe `main` checkout hoye giyechilo, tai report-er commit prothome 4.5-er `main`-er upor hoye jay (`5d41848c`), ar dev-er working tree kichukkhon 4.5 code dekhacchilo (DB tokhono 5.3). Thik kora hoyeche evabe: commit-ta `upgrade/moodle-5.3`-er upor cherry-pick (`33d39105`), `main`-ke shei commit-e anা, tarpor working tree 5.3-e ferot. Cache purge kore push kora hoyeche. `5d41848c` kokhono push hoy ni.

## Staging/production deploy

User janiyechen, staging server-e `main` **haate pull** kora hoy, kono automation nei. Tai ei push-e live-e kichu hoy ni. Pull korar shomoy PRODUCTION-CHECKLIST-er A ar B section kromanushare follow korun.

## ⚠️ Production-er jonno shotorkota

- Production jodi `main` theke **auto-pull ba auto-deploy** kore, tahole 5.3 code DB upgrade chara live-e chole jabe, ar site down hobe. Production-e `git pull` korar age **PRODUCTION-CHECKLIST-er A ar B section** follow korun: maintenance mode on, pull, `upgrade.php`, Nginx `public/`, router config.
- Moodle core-er sathe `.github/workflows/` eseche. Repo-te GitHub Actions on thakle push-er por Moodle-er CI job cholte pare. Eta deploy na, shudhu test. Dorkar na hole GitHub > Settings > Actions theke disable korun.

## Rollback

```bash
git checkout main
git reset --hard 2e912220          # 4.5-er shesh commit
git push --force-with-lease origin main
```
(force push-er age team-ke janan)
