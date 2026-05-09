# Implementation Tasks

## Overview

Ces tâches implémentent les trois fonctionnalités avancées définies dans les exigences : Progress Tracking (basé sur la complétion réelle des leçons), Rating System, et Certificate System. L'architecture MVC existante est respectée tout au long.

---

## Task 1: Database — Create certificates table

- [ ] 1.1 Add the `certificates` table creation to `setup_lessons.php` (or a new `setup_certificates.php`)
  - Columns: `id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `course_id` INT NOT NULL, `date_obtained` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  - UNIQUE KEY on `(user_id, course_id)`
  - Foreign key `course_id` → `course(idCourse)` ON DELETE CASCADE
- [ ] 1.2 Verify that the `progress` table already has `user_id`, `course_id`, `last_accessed` columns (no `progress_percent` — it is computed)
- [ ] 1.3 Verify that the `ratings` table already has `id`, `user_id`, `course_id`, `rating` columns with UNIQUE on `(user_id, course_id)`

---

## Task 2: Model — Create Certificate model class

- [ ] 2.1 Create `Model/Certificate.php` following the same pattern as `Model/Rating.php` and `Model/Progress.php`
  - Properties: `$id`, `$userId`, `$courseId`, `$dateObtained`
  - Constructor with all four parameters
  - Getters and setters for each property
  - No SQL or business logic in the model

---

## Task 3: Controller — CertificateController

- [ ] 3.1 Create `Controller/CertificateController.php`
  - Constructor injects PDO via `Config::getInstance()->getConnexion()`
  - Follow the same conventions as `CourseController` and `EnrollmentController`
- [ ] 3.2 Implement `getByUser(int $userId): array`
  - SELECT all certificates for a user, JOIN with `course` to get `titre`
  - Return array of rows with `id`, `user_id`, `course_id`, `course_titre`, `date_obtained`
  - Use prepared statements
- [ ] 3.3 Implement `getByUserAndCourse(int $userId, int $courseId): ?array`
  - Return the certificate row or null if not found
  - Use prepared statements
- [ ] 3.4 Implement `generate(int $userId, int $courseId): array`
  - Return `['success' => false, 'message' => '...']` if certificate already exists (idempotent)
  - INSERT into `certificates` with `date_obtained = NOW()`
  - Return `['success' => true, 'message' => 'Certificat généré avec succès.']`
  - Use prepared statements

---

## Task 4: Controller — Update ProgressController to trigger certificate generation

- [ ] 4.1 Add `require_once` for `CertificateController` at the top of `ProgressController.php`
- [ ] 4.2 In `markLessonComplete()`, after computing `$newPercent`:
  - If `$newPercent === 100`, call `CertificateController::generate($userId, $courseId)` (or instantiate and call)
  - Include `certificate_generated` boolean in the return array
- [ ] 4.3 Update the return array of `markLessonComplete()` to include `'certificate_generated' => bool`

---

## Task 5: View — FrontOffice course detail page (show.php) — Progress & Rating

The `show.php` already contains progress tracking and rating UI. This task hardens and completes it.

- [ ] 5.1 Ensure the progress block correctly reads `$progressData['done']` and `$progressData['total']` from `ProgressController::getProgress()`
- [ ] 5.2 When `markLessonComplete()` returns `certificate_generated = true`, display a certificate success banner:
  - "🎓 Félicitations ! Votre certificat a été généré."
  - Link to the certificate page: `View/FrontOffice/certificate/index.php?userId={id}`
- [ ] 5.3 Ensure the rating form correctly POSTs `action=submit_rating` and `rating` (1–5) to the same page
- [ ] 5.4 Ensure `htmlspecialchars()` is applied to all user-supplied output (XSS protection)

---

## Task 6: View — FrontOffice certificate page

- [ ] 6.1 Create directory `View/FrontOffice/certificate/`
- [ ] 6.2 Create `View/FrontOffice/certificate/index.php`
  - Require `CertificateController.php`
  - Set `$baseUrl`, `$pageTitle`
  - Hardcode `$currentUserId = 1` (same pattern as `show.php` — to be replaced by real session later)
  - Call `$certController->getByUser($currentUserId)` to get certificates
  - Include `View/includes/header.php` and `View/includes/footer.php`
- [ ] 6.3 Render the certificate list:
  - If empty: display "Aucun certificat obtenu pour le moment."
  - For each certificate: show course title, `date_obtained` formatted as `d/m/Y`, and a "Voir le cours" link to `show.php?id={course_id}`
- [ ] 6.4 Add a link to the certificate page in the global navigation (`View/includes/header.php`)
  - Add `<li><a href="...">Mes Certificats</a></li>` to the nav-links

---

## Task 7: View — BackOffice certificate management

- [ ] 7.1 Create `View/BackOffice/certificate/list.php`
  - List all certificates across all users (admin view)
  - Columns: ID, User ID, Course title, Date obtained
  - Use `CertificateController::getByUser()` per user, or add a `listAll()` method to `CertificateController`
- [ ] 7.2 Add `listAll(): array` method to `CertificateController`
  - SELECT all rows from `certificates` JOIN `course` ON `course_id`, ORDER BY `date_obtained DESC`
  - Return array with `id`, `user_id`, `course_id`, `course_titre`, `date_obtained`
- [ ] 7.3 Add a "Certificats" link to the BackOffice navigation in `View/includes/header.php`

---

## Task 8: Fix BackOffice dashboard broken links

- [ ] 8.1 Open `View/BackOffice/dashboard.php`
- [ ] 8.2 Replace the stale sidebar links with the correct absolute paths:
  - `courses_list.php` → `/gestioncours/View/BackOffice/course/list.php`
  - `course_add.php` → `/gestioncours/View/BackOffice/course/add.php`
  - `enrollments_list.php` → `/gestioncours/View/BackOffice/enrollment/list.php`
  - `supports_list.php` → `/gestioncours/View/BackOffice/support_course/list.php`
  - Add: `/gestioncours/View/BackOffice/certificate/list.php` for Certificats

---

## Task 9: Security & Validation hardening

- [ ] 9.1 In `RatingController::addOrUpdateRating()`, confirm input validation rejects values outside 1–5 (already present — verify and add test case)
- [ ] 9.2 In `ProgressController::markLessonComplete()`, confirm `$userId > 0` and `$lessonId > 0` guards are in place (already present — verify)
- [ ] 9.3 In `CertificateController::generate()`, validate `$userId > 0` and `$courseId > 0` before any DB operation
- [ ] 9.4 Ensure all views use `htmlspecialchars()` on every echoed variable that originates from user input or DB data
- [ ] 9.5 Ensure no raw SQL appears in any view file

---

## Task 10: Integration test — end-to-end flow

- [ ] 10.1 Run `setup_lessons.php` (or `setup_certificates.php`) to create the `certificates` table
- [ ] 10.2 Run `seed.php` to ensure at least one course with lessons exists
- [ ] 10.3 Navigate to `View/FrontOffice/course/show.php?id=1` and mark all lessons as completed one by one
  - Verify progress bar reaches 100%
  - Verify certificate banner appears after the last lesson
- [ ] 10.4 Navigate to `View/FrontOffice/certificate/index.php` and verify the certificate is listed with the correct course title and date
- [ ] 10.5 Submit a rating (1–5) on `show.php` and verify the average updates correctly
- [ ] 10.6 Submit the same rating again and verify it updates (not duplicates)
- [ ] 10.7 Navigate to `View/BackOffice/certificate/list.php` and verify the certificate appears in the admin list
- [ ] 10.8 Verify all BackOffice dashboard links resolve correctly (Task 8 fix)
