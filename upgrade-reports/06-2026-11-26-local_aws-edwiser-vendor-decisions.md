# 06: local_aws, Edwiser in git, vendor: shiddhanto

- **Tarikh:** 2026-11-26
- **Environment:** dev (5.3.1+, branch `upgrade/moodle-5.3`)
- **Ki kora hoyeche:** shudhu check ar shiddhanto. **Kono code ba DB change nei, commit-o nei.**

## 1. local_aws: **rakha hobe**

User janiyechen, local_aws age AWS-er sathe connect korar jonno lagto, ar live site AWS (S3) use kore.

### 5.3-e ja dekha geche

| Check | Result |
|---|---|
| Kon code local_aws use kore? | **Keu na.** Shudhu objectfs-er ekta comment-e GitHub link ache. 4.5 (`main`)-eo eki obostha |
| objectfs ar s3bucket kon SDK use kore? | `\Aws\S3\S3Client`, ja 5.3-e load hoy **core `vendor/aws/aws-sdk-php`** theke (v3.356.22) |
| local_aws-er nijer SDK | v3.342.30, `public/local/aws/sdk/`. Kono code eta-r autoloader load kore na |
| Duita SDK-te conflict? | Na. Test-e `S3Client` core vendor theke load holo, kono error nei |
| Dev-e objectfs config | `filesystem=\tool_objectfs\s3_file_system`, region `ap-south-1`, `s3_usesdkcreds=1` (IAM role), bucket set kora. `$CFG->alternative_file_system_class` dev `config.php`-e nei |

### Shiddhanto
- **local_aws thakbe.** Eta rakhle kono khoti nei, ar live site-e kichu bhangar jhuki-o nei.
- Tobe 5.3-e AWS connection ashole core-er `vendor/aws` diye hoy. Tai **`vendor/` na thakle S3/objectfs kaj korbe na** (nicher section 3 dekhun).
- Production-e 5.3 stable hobar por chaile local_aws remove kora jete pare. Tobe tar age production-e objectfs ar s3bucket test kore nite hobe.

### Production-e joruri
- Production `config.php`-e `$CFG->alternative_file_system_class = '\tool_objectfs\s3_file_system';` ache kina dekhun. Thakle shob file S3-e, tai objectfs bhangle **site-er shob file (image, course file) bhangbe**.
- Staging rehearsal-e (checklist A6) file upload ar download obosshoi test korun.

## 2. Edwiser plugin git-e: **rakha hobe**

Repo private kora hobe, tai Edwiser-er paid code (`public/theme/remui` ityadi) git-e commit kora jabe. Eta nie ar kichu korar nei.

- **Mone rakhun:** `MiltonDIU/devlms` private na kore Edwiser code push korben na.

## 3. `vendor/`: rakhle ar na rakhle ki hoy

Moodle 5.x-e core-er onek third-party library (Slim router, Guzzle, AWS SDK, Monolog, PHP-DI ityadi) ar `lib/`-e thake na. Egulo thake `vendor/`-e, ar `composer.lock` theke exact version-e install hoy. **`vendor/` chara Moodle 5.3 chalu-i hobe na**, prothom request-ei class-not-found error dibe.

### Option A: `vendor/` git-e commit kora (recommended)

```
git pull  →  php admin/cli/upgrade.php  →  done
```
- ✅ Production server-e composer lage na, internet (packagist) access-o lage na
- ✅ Dev-e ja test kora hoyeche, production-e hubohu sheta jabe
- ✅ Bortoman deploy process (`git pull`) bodlate hobe na
- ❌ Repo boro hobe, ~73MB
- ❌ Moodle minor update-er por `composer install --no-dev` chaliye vendor abar commit korte hobe

### Option B: `vendor/` git-e na rakha

```
git pull  →  composer install --no-dev --no-interaction  →  php admin/cli/upgrade.php  →  done
```
- Production server-e **composer install** kora thakte hobe (PHP 8.3+ CLI shoho)
- Server theke **packagist.org ar github.com-e internet access** lagbe
- `composer.lock` git-e ache, tai version hubohu eki thakbe. Shudhu download hobe server-e
- `.gitignore`-e `/vendor/` add korte hobe
- ❌ Prottek deploy-e ekta extra step, ar step-ta bhule gele site down
- ❌ Packagist ba GitHub down thakle deploy atke jabe

### Recommendation
**Option A.** Karon:
1. Site live, ar deploy simple `git pull`. Extra step-e bhuler jhuki kom thake.
2. Production server-e composer ar internet access ache kina ekhono jana nei.
3. Repo private hobe, tai size kono boro shomossa na.

> Ekhon `.gitignore`-e `/vendor/` **nei**, tai `git add -A` korle vendor commit hoye jabe. Option B nile commit-er age `/vendor/` add korte hobe.

## Production-e korte hobe

- Checklist A4-e: production `config.php`-e `alternative_file_system_class` ache kina check kora
- Checklist A6-e (rehearsal): S3 file upload ar download test
- Checklist B2: vendor-er shiddhanto onujayi deploy

## Rollback

Kono change kora hoy ni, tai rollback-er dorkar nei.
