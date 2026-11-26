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
