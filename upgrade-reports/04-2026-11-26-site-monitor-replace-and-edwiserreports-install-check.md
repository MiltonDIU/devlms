# 04: Site Monitor replace ar Edwiser Reports install check

- **Tarikh:** 2026-11-26
- **Environment:** dev (Moodle 4.5.2+, `$version = 2024100702.02`)
- **User chaiyechilen:** (1) Site Monitor zip diye installed plugin replace kora (zip-ti "Moodle 5.2" label kora), (2) Edwiser Reports install kore notun feature dekha
- **Result:** **kono-tai kora hoy ni.** Karon nicher section-e. Code ba DB-te kono change nei, commit-o nei.

## 1. Site Monitor replace: dorkar nei

Zip-er `version.php` ar installed copy-r `version.php` diff kore dekha geche, duitai hubohu eki (2.1.3, `2026042200`). Mane dev-e ekhon je plugin ache, "Moodle 5.2" label kora zip-eo seta-i. Replace korle kichu bodlabe na.

5.3 upgrade-er shomoy ei zip theke `public/blocks/edwiser_site_monitor`-e bosalei hobe.

## 2. Edwiser Reports: 4.5-e install kora jay na

| | Value |
|---|---|
| Plugin chay (`requires`) | `2025041400` = **Moodle 5.0** |
| Dev-e ache | `2024100702.02` = Moodle 4.5 |

Moodle nije ei install block kore dibe ("requires a newer Moodle version"). `requires` value haate bodle jor kore install korle plugin 5.x-er API khujbe ar site bhenge jete pare. Tai **5.3 upgrade-er por install korbo.**

> CHANGES.txt onujayi v1.8.0 chilo 4.5-compatible. Kintu sheta install korle porer upgrade-e abar 2.0.2-e jete hobe. Tai shudhu feature dekhar jonno eta install kora worth na.

## Edwiser Reports 2.0.2-er feature (README ar CHANGES.txt theke)

Dashboard-e ja ja block ache:

| # | Block/Report | Ki dekhay |
|---|---|---|
| 1 | Active Users / Enrolment / Completion | Shomoy onujayi active user, enrolment ar completion rate. Date-wise detail page-o ache |
| 2 | Course Progress | Course-wise progress pie chart |
| 3 | Popular Courses | Enrolment, visit ar completion diye shobcheye active course. Course Progress ar Course Engagement report shoho |
| 4 | Certificates Stats | Certificate issue holo kina. **`mod_customcert` lage, amader site-e nei** |
| 5 | Live Users | Ekhon ke ke login kore ache |
| 6 | Site Access | Shoptaher prottek ghontay site-er average usage |
| 7 | Daily Activities | Prottek diner activity |
| 8 | Inactive Users | Inactive user-der list |
| 9 | Course Completion | Prottek course-e user-wise completion |
| 10 | Student Engagement (Teacher-der jonno) | Site ar course-e student-ra koto shomoy kataise, ar koto visit |
| 11 | Learner Block (Student-der jonno) | Nijer progress ar timespent |
| + | Custom Reports | Nije field bachai kore report banano |
| + | Time spent, course analytics, grade, course access | `classes/blocks/` folder theke pawa |

Version history: 1.8.0 Moodle 4.5-er jonno, 1.9.0 5.0-er jonno, 2.0.0 5.1.1+-er jonno, **2.0.1-e 5.3 support add hoyeche**, ar zip-e ache 2.0.2.

## Joruri note (220GB DB)

Plugin-ta user time track kore, ar cron diye log theke data process kore. Production-e prothombar chalanor shomoy `logstore_standard_log`-er moto boro table scan korte pare. Staging rehearsal-er shomoy er cron task-er run time-o mepe nite hobe.

## Production-e korte hobe

Kichu na.

## Rollback

Kono change kora hoy ni, tai rollback-er dorkar nei.
