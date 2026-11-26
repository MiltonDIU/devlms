# 01: `local_blcreport` plugin remove

- **Tarikh:** 2026-11-26
- **Environment:** dev (devlms.local)
- **Commit:** `2e912220` "Remove unused local_blcreport plugin" (`main`-e push kora)

## Ki kora hoyeche ar keno

`local_blcreport` (BLC Report, v1.5) plugin-ta ar dorkar nei, tai remove kora hoyeche.

Remove korar age check kora hoyeche:
- Plugin-e file chilo mattro 3ta: `version.php`, `lib.php`, `lang/en/local_blcreport.php`
- `db/` folder nei, mane er nijer kono DB table chilo na
- Project-er onno kono PHP file `blcreport` use kore na

## Change

| Dhoron | Ki | Production-e jabe? |
|---|---|---|
| Code | `local/blcreport/` folder delete | ✅ `git pull` korle jabe |
| DB | Moodle CLI diye uninstall, jate `config_plugins` theke version entry muche jay | ❌ **Haate korte hobe** |

Dev-e ja chalano hoyeche:
```bash
php admin/cli/uninstall_plugins.php --plugins=local_blcreport --run
# Output: ++ Success ++
git rm -r local/blcreport
git commit -m "Remove unused local_blcreport plugin"
git push origin main
```

## Verify

- Uninstall CLI-er output "Success"
- Push-er por `main` ar `origin/main` ek jaygay
- Commit-e shudhu author "Md. Milton"

## Production-e korte hobe

1. `git pull` (commit `2e912220`)
2. Tarpor chalan: `php admin/cli/uninstall_plugins.php --plugins=local_blcreport --run`
3. Site administration > Plugins > Plugins overview-e dekhun `local_blcreport` ar nei, "Missing from disk" warning-o nei

> Order ulte gele: code pull na kore age uninstall chalale Moodle plugin-ta abar install korte chaibe. Ar pull kore uninstall na chalale "Missing from disk" warning theke jabe.

## Rollback

```bash
git revert 2e912220
```
Tarpor Site admin > Notifications-e gele plugin-ta abar install hobe. Plugin-e kono data chilo na, tai kichu harabe na.

## Related note

`rsa_keys_blc_for_diu/private.pem` plugin-ta use korto na. Eta use hoy core file `login/index.php`-e (line 126-140), sekhane `ENC::` diye shuru hoya password decrypt kora hoy. Tai key ta **rakha hoyeche**. 5.3 upgrade-er shomoy ei logic-ta core theke sorate hobe (guideline dekhun).
