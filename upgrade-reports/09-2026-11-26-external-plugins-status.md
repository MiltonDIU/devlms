# 09: External (third-party) plugin status report

- **Tarikh:** 2026-11-26
- **Environment:** dev (Moodle 5.3.1+, branch `upgrade/moodle-5.3`)
- **Source:** `core_plugin_manager`. Moodle core-er na emon shob plugin (subplugin shoho). Usage count dev DB theke.
- **Ki kora hoyeche:** shudhu report. **Kono change nei.**

## Summary

| | Shonkha |
|---|---|
| **Mot external plugin install kora** | **24** |
| ✅ Active | 18 |
| ⚪ Inactive | 1 (`filter_wiris`) |
| 🔵 Always on (local/quizaccess, enable ba disable kora jay na) | 5 |
| Status "up to date" | 24/24 ✅ (missing ba upgrade-pending nei) |

> "Always on" mane Moodle-e ei type-er plugin (local_*, quizaccess_*) enable ba disable korar global switch nei. Install thaklei kaj kore. quizaccess shudhu shei quiz-e kaj kore jekhane setting-ta on kora.

## Pura list

| # | Plugin | Type | Nam | Version | Status | Dev-e use | Source |
|---|---|---|---|---|---|---|---|
| 1 | theme_remui | theme | Edwiser RemUI | 5.3.1 | ✅ Active | Site theme | Edwiser |
| 2 | format_remuiformat | course format | Edwiser course formats | 4.1.23 | ✅ Active | 4ta course | Edwiser |
| 3 | local_edwiserpagebuilder | local | Edwiser Page Builder | 5.0.2 | 🔵 Always on | – | Edwiser |
| 4 | local_edwisersiteimporter | local | Edwiser Site Importer | 4.1.5 | 🔵 Always on | – | Edwiser |
| 5 | local_edwiserreports | local | Edwiser Reports Free | 2.0.2 | 🔵 Always on | – | Edwiser (🆕 5.3-e notun) |
| 6 | block_edwiseradvancedblock | block | Edwiser Advanced Block | v4.2.9 | ✅ Active | 26ta instance | Edwiser |
| 7 | block_edwiserratingreview | block | Edwiser Rating and Review | 1.0.14 | ✅ Active | 5ta instance | Edwiser |
| 8 | block_edwiser_site_monitor | block | Edwiser Site Monitor | 2.1.4 | ✅ Active | 17ta instance | moodle.org |
| 9 | filter_edwiserpbf | filter | Edwiser Page Builder Filter | 4.2.14 | ✅ Active (On) | – | Edwiser |
| 10 | mod_questionnaire | activity | Questionnaire | 5.3.2 | ✅ Active | 7ta activity | moodle.org |
| 11 | mod_vpl | activity | Virtual programming lab | 5.0.2 | ✅ Active | 1ta activity | moodle.org |
| 12 | vplevaluator_biotes | VPL subplugin | BIOTES evaluator | (2025091709) | ✅ Active | – | mod_vpl-er sathe ase |
| 13 | vplevaluator_giotes | VPL subplugin | GIOTES evaluator | (2026080712) | ✅ Active | – | mod_vpl-er sathe ase |
| 14 | mod_attendance | activity | Attendance | 2026111201 | ✅ Active | 0ta activity | moodle.org |
| 15 | mod_googlemeet | activity | Google Meet for Moodle | 2.1.1 | ✅ Active | 0ta activity | ⚠️ Purono (2023), 5.3 release nei |
| 16 | block_xp | block | Level Up XP | 21.0 | ✅ Active | 1ta instance | moodle.org |
| 17 | availability_xp | availability | Level Up XP Availability | 2.2.3 | ✅ Active | 0ta restriction | moodle.org |
| 18 | block_course_contents | block | Course contents | 6.0.0 | ✅ Active | 0ta instance | ⚠️ Purono (2021), 5.3 release nei |
| 19 | filter_wiris | filter | MathType by WIRIS | 8.12.0 | ⚪ **Inactive** | Global setting nei | moodle.org |
| 20 | tiny_wiris | TinyMCE plugin | MathType by WIRIS | 8.12.0 | ✅ Active | – | moodle.org (report 08) |
| 21 | tool_objectfs | admin tool | Object storage file system | 2026102101 | ✅ Active | – | moodle.org |
| 22 | repository_s3bucket | repository | Amazon S3 bucket | v5.3.1 | ✅ Active | 0ta instance | moodle.org |
| 23 | local_agreement | local | BLC_USER_POLICY | v1.0.0 | 🔵 Always on | DB-te 7ta row | ⚠️ Custom (DIU) |
| 24 | quizaccess_proctoringlms | quiz access rule | Proctoring | 1.0.2 + 5.3 port | 🔵 Always on | 0ta quiz-e enable | ⚠️ Custom, 5.3-er jonno port kora (report 05) |

## Lokkho korar bishoy

1. **`filter_wiris` Inactive.** Backup DB-te (upgrade-er age) dekha geche, 4.5-eo eta enable chilo na. `filter_active`-e shudhu `mathjaxloader` ar `edwiserpbf` chilo. Tai **eta upgrade-er karone hoy ni**, ar change kora hoy ni.
   - MathType equation (`tiny_wiris` diye insert kora) student-der dekhate chaile: *Site administration > Plugins > Filters > Manage filters*-e "MathType by WIRIS" **On** korte hobe.
   - Production-e eta on ache kina alada kore dekhun. Production DB alada, setting-o alada hote pare.
2. **Dev-e use hoy na (0):** mod_attendance, mod_googlemeet, block_course_contents, availability_xp, repository_s3bucket instance, proctoringlms. **Production-e usage alada hote pare.** Checklist A4-e production-er count check korar kotha ache. Production-eo use na hole egulo remove kora jete pare, bishesh kore googlemeet ar course_contents, karon egulo puronoi ar 5.3 release nei.
3. **Remove kora hoyeche (ekhon ar nei):** `local_blcreport` (report 01), `local_aws` (report 07). `atto_wiris` Atto-r sathe core theke chole geche.

## Production-e korte hobe

- `filter_wiris` production-e On ache kina dekhun, ar 5.3-e-o eki rakhun
- Checklist A4-e: upore list kora plugin-gulor production usage count

## Rollback

Kono change kora hoy ni, tai rollback-er dorkar nei.
