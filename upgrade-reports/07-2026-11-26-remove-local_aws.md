# 07: `local_aws` remove

- **Tarikh:** 2026-11-26
- **Environment:** dev (5.3.1+, branch `upgrade/moodle-5.3`)
- **Commit:** nei (user manually check korben)

## Ki kora hoyeche ar keno

Report 06-e dekha geche 5.3-e kono code `local_aws` use kore na. objectfs ar s3bucket AWS SDK pay core-er `vendor/aws/aws-sdk-php` theke. Tai user-er kotha moto plugin-ta remove kora hoyeche.

```bash
php admin/cli/uninstall_plugins.php --plugins=local_aws --run   # ++ Success ++
rm -rf public/local/aws
php admin/cli/purge_caches.php
```

## Change

| Dhoron | Ki | Production-e jabe? |
|---|---|---|
| Code | `public/local/aws/` delete. Eta untracked build-er ongsho chilo, tai git-e alada kono diff nei. Branch-e commit hole `main`-er `local/aws/` "deleted" hishebe dekhabe | ✅ Deploy-er shathe jabe |
| DB | `local_aws` uninstall (dev DB) | ❌ **Haate korte hobe** |

## Verify

- Uninstall output "Success", ar DB-te `local_aws` version "gone"
- `\Aws\S3\S3Client` toiri hoy, SDK 3.356.22 (core vendor theke)
- Shob plugin status up to date, missing nei
- **Asol S3 connection test kora hoy ni.** Dev-e S3 credential nei (IAM role production-e)

## Production-e korte hobe

5.3 code deploy ar `upgrade.php` chalanor **por**:
```bash
php admin/cli/uninstall_plugins.php --plugins=local_aws --run
```
Na chalale Plugins overview-e "Missing from disk" dekhabe. Site-e kono khoti hobe na, kintu warning theke jabe.

Tarpor S3 file upload ar download test korun (checklist A6/B10).

## Rollback

Plugin-er code `main` branch-e ache (`local/aws/`):
```bash
git archive main local/aws | tar -x -C /tmp && mv /tmp/local/aws public/local/aws
php admin/cli/upgrade.php --non-interactive
```
Plugin-e kono user data chilo na, tai kichu harabe na.
