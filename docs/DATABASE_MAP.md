# Database Map

Tables: **33**  

Declared foreign keys: **27**

## Domain Groups

- Identity/access: `users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.
- Student profile: `user_profiles`, `addresses`, `educations`, `experiences`, `skills`, `user_skills`.
- Learning: `courses`, `batches`, `batch_students`, `batch_mentors`, `class_schedules`.
- Commerce: `course_orders`.
- Public CMS/content: `frontend_pages`, `frontend_sections`, `frontend_settings`, `mentors`, `reviews`, `news_updates`, `contact_messages`.
- Laravel infrastructure: `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`, `migrations`.

## Tables

### `addresses`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `user_id` | `bigint UNSIGNED NOT NULL` |
| `house_number` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `street` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `city` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `post_office` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `zip_code` | `varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `country` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Bangladesh'` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `batches`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `course_id` | `bigint UNSIGNED NOT NULL` |
| `name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `start_date` | `date NOT NULL` |
| `end_date` | `date NOT NULL` |
| `class_days` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `class_time` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `live_class_link` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `status` | `enum('upcoming','running','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'upcoming'` |
| `created_by` | `bigint UNSIGNED NOT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `batch_mentors`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `batch_id` | `bigint UNSIGNED NOT NULL` |
| `mentor_id` | `bigint UNSIGNED NOT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `batch_students`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `batch_id` | `bigint UNSIGNED NOT NULL` |
| `student_id` | `bigint UNSIGNED NOT NULL` |
| `status` | `enum('pending','approved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approved'` |
| `batch_type` | `enum('online','offline') COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `approved_at` | `timestamp NULL DEFAULT NULL` |
| `approved_by` | `bigint UNSIGNED DEFAULT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `cache`

| Column | Definition |
|---|---|
| `key` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `value` | `mediumtext COLLATE utf8mb4_unicode_ci NOT NULL` |
| `expiration` | `int NOT NULL` |

### `cache_locks`

| Column | Definition |
|---|---|
| `key` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `owner` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `expiration` | `int NOT NULL` |

### `class_schedules`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `batch_id` | `bigint UNSIGNED NOT NULL` |
| `class_date` | `date NOT NULL` |
| `topic` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `live_class_link` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `recorded_video_link` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `created_by` | `bigint UNSIGNED NOT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `contact_messages`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `user_id` | `bigint UNSIGNED DEFAULT NULL` |
| `name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `email` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `phone` | `varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `subject` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `message` | `text COLLATE utf8mb4_unicode_ci NOT NULL` |
| `ip_address` | `varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `user_agent` | `text COLLATE utf8mb4_unicode_ci` |
| `read_at` | `timestamp NULL DEFAULT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `courses`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `title` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `slug` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `description` | `mediumtext COLLATE utf8mb4_unicode_ci NOT NULL` |
| `old_price` | `decimal(10,2) DEFAULT NULL` |
| `discount_price` | `decimal(10,2) DEFAULT NULL` |
| `online_old_price` | `decimal(10,2) DEFAULT NULL` |
| `online_discount_price` | `decimal(10,2) DEFAULT NULL` |
| `offline_old_price` | `decimal(10,2) DEFAULT NULL` |
| `offline_discount_price` | `decimal(10,2) DEFAULT NULL` |
| `thumbnail` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `status` | `enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active'` |
| `created_by` | `bigint UNSIGNED NOT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `course_orders`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `user_id` | `bigint UNSIGNED NOT NULL` |
| `course_id` | `bigint UNSIGNED NOT NULL` |
| `batch_id` | `bigint UNSIGNED DEFAULT NULL` |
| `batch_type` | `enum('online','offline') COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `amount` | `decimal(10,2) NOT NULL` |
| `currency` | `varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BDT'` |
| `status` | `enum('pending','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending'` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `educations`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `user_id` | `bigint UNSIGNED NOT NULL` |
| `degree_name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `institute_name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `board_or_university` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `start_year` | `smallint UNSIGNED DEFAULT NULL` |
| `end_year` | `smallint UNSIGNED DEFAULT NULL` |
| `result_or_grade` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `experiences`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `user_id` | `bigint UNSIGNED NOT NULL` |
| `company_name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `job_title` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `start_date` | `date NOT NULL` |
| `end_date` | `date DEFAULT NULL` |
| `description` | `text COLLATE utf8mb4_unicode_ci` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `failed_jobs`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `uuid` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `connection` | `text COLLATE utf8mb4_unicode_ci NOT NULL` |
| `queue` | `text COLLATE utf8mb4_unicode_ci NOT NULL` |
| `payload` | `longtext COLLATE utf8mb4_unicode_ci NOT NULL` |
| `exception` | `longtext COLLATE utf8mb4_unicode_ci NOT NULL` |
| `failed_at` | `timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP` |

### `frontend_pages`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `slug` | `varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `frontend_sections`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `frontend_page_id` | `bigint UNSIGNED NOT NULL` |
| `section_key` | `varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `title_en` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `title_bn` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `content_en` | `longtext COLLATE utf8mb4_unicode_ci` |
| `content_bn` | `longtext COLLATE utf8mb4_unicode_ci` |
| `image_path` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `icon` | `varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `button_text_en` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `button_text_bn` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `button_link` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `status` | `enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active'` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `frontend_settings`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `key` | `varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `value_en` | `text COLLATE utf8mb4_unicode_ci` |
| `value_bn` | `text COLLATE utf8mb4_unicode_ci` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `jobs`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `queue` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `payload` | `longtext COLLATE utf8mb4_unicode_ci NOT NULL` |
| `attempts` | `tinyint UNSIGNED NOT NULL` |
| `reserved_at` | `int UNSIGNED DEFAULT NULL` |
| `available_at` | `int UNSIGNED NOT NULL` |
| `created_at` | `int UNSIGNED NOT NULL` |

### `job_batches`

| Column | Definition |
|---|---|
| `id` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `total_jobs` | `int NOT NULL` |
| `pending_jobs` | `int NOT NULL` |
| `failed_jobs` | `int NOT NULL` |
| `failed_job_ids` | `longtext COLLATE utf8mb4_unicode_ci NOT NULL` |
| `options` | `mediumtext COLLATE utf8mb4_unicode_ci` |
| `cancelled_at` | `int DEFAULT NULL` |
| `created_at` | `int NOT NULL` |
| `finished_at` | `int DEFAULT NULL` |

### `mentors`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `user_id` | `bigint UNSIGNED DEFAULT NULL` |
| `name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `slug` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `topic` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `bio` | `mediumtext COLLATE utf8mb4_unicode_ci` |
| `is_active` | `tinyint(1) NOT NULL DEFAULT '1'` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `migrations`

| Column | Definition |
|---|---|
| `id` | `int UNSIGNED NOT NULL` |
| `migration` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `batch` | `int NOT NULL` |

### `model_has_permissions`

| Column | Definition |
|---|---|
| `permission_id` | `bigint UNSIGNED NOT NULL` |
| `model_type` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `model_id` | `bigint UNSIGNED NOT NULL` |

### `model_has_roles`

| Column | Definition |
|---|---|
| `role_id` | `bigint UNSIGNED NOT NULL` |
| `model_type` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `model_id` | `bigint UNSIGNED NOT NULL` |

### `news_updates`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `title` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `slug` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `excerpt` | `text COLLATE utf8mb4_unicode_ci` |
| `body` | `longtext COLLATE utf8mb4_unicode_ci NOT NULL` |
| `image_path` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `status` | `varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published'` |
| `published_at` | `timestamp NULL DEFAULT NULL` |
| `author_id` | `bigint UNSIGNED DEFAULT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |
| `deleted_at` | `timestamp NULL DEFAULT NULL` |

### `password_reset_tokens`

| Column | Definition |
|---|---|
| `email` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `token` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |

### `permissions`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `guard_name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `reviews`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `designation` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `quote` | `text COLLATE utf8mb4_unicode_ci NOT NULL` |
| `rating` | `tinyint UNSIGNED NOT NULL DEFAULT '5'` |
| `status` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active'` |
| `sort_order` | `int UNSIGNED NOT NULL DEFAULT '0'` |
| `created_by` | `bigint UNSIGNED DEFAULT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `roles`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `guard_name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `role_has_permissions`

| Column | Definition |
|---|---|
| `permission_id` | `bigint UNSIGNED NOT NULL` |
| `role_id` | `bigint UNSIGNED NOT NULL` |

### `sessions`

| Column | Definition |
|---|---|
| `id` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `user_id` | `bigint UNSIGNED DEFAULT NULL` |
| `ip_address` | `varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `user_agent` | `text COLLATE utf8mb4_unicode_ci` |
| `payload` | `longtext COLLATE utf8mb4_unicode_ci NOT NULL` |
| `last_activity` | `int NOT NULL` |

### `skills`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |

### `users`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `name` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `email` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `email_verified_at` | `timestamp NULL DEFAULT NULL` |
| `profile_image` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `password` | `varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL` |
| `must_change_password` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `remember_token` | `varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `user_profiles`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `user_id` | `bigint UNSIGNED NOT NULL` |
| `gender` | `enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `date_of_birth` | `date DEFAULT NULL` |
| `mobile_number` | `varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `father_name` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `father_mobile` | `varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `mother_name` | `varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `mother_mobile` | `varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `bio` | `text COLLATE utf8mb4_unicode_ci` |
| `public_url` | `varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `created_at` | `timestamp NULL DEFAULT NULL` |
| `updated_at` | `timestamp NULL DEFAULT NULL` |

### `user_skills`

| Column | Definition |
|---|---|
| `id` | `bigint UNSIGNED NOT NULL` |
| `user_id` | `bigint UNSIGNED NOT NULL` |
| `skill_id` | `bigint UNSIGNED NOT NULL` |
| `proficiency_level` | `enum('beginner','intermediate','expert') COLLATE utf8mb4_unicode_ci NOT NULL` |

## Foreign Keys

| From | To | Action |
|---|---|---|
| `addresses.user_id` | `users.id` | `ON DELETE CASCADE` |
| `batches.course_id` | `courses.id` | `ON DELETE CASCADE` |
| `batches.created_by` | `users.id` | `RESTRICT/default` |
| `batch_mentors.batch_id` | `batches.id` | `ON DELETE CASCADE` |
| `batch_mentors.mentor_id` | `users.id` | `ON DELETE CASCADE` |
| `batch_students.approved_by` | `users.id` | `ON DELETE SET NULL` |
| `batch_students.batch_id` | `batches.id` | `ON DELETE CASCADE` |
| `batch_students.student_id` | `users.id` | `ON DELETE CASCADE` |
| `class_schedules.batch_id` | `batches.id` | `ON DELETE CASCADE` |
| `class_schedules.created_by` | `users.id` | `RESTRICT/default` |
| `contact_messages.user_id` | `users.id` | `ON DELETE SET NULL` |
| `courses.created_by` | `users.id` | `RESTRICT/default` |
| `course_orders.batch_id` | `batches.id` | `ON DELETE SET NULL` |
| `course_orders.course_id` | `courses.id` | `ON DELETE CASCADE` |
| `course_orders.user_id` | `users.id` | `ON DELETE CASCADE` |
| `educations.user_id` | `users.id` | `ON DELETE CASCADE` |
| `experiences.user_id` | `users.id` | `ON DELETE CASCADE` |
| `frontend_sections.frontend_page_id` | `frontend_pages.id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `mentors.user_id` | `users.id` | `ON DELETE SET NULL` |
| `model_has_permissions.permission_id` | `permissions.id` | `ON DELETE CASCADE` |
| `model_has_roles.role_id` | `roles.id` | `ON DELETE CASCADE` |
| `reviews.created_by` | `users.id` | `ON DELETE SET NULL` |
| `role_has_permissions.permission_id` | `permissions.id` | `ON DELETE CASCADE` |
| `role_has_permissions.role_id` | `roles.id` | `ON DELETE CASCADE` |
| `user_profiles.user_id` | `users.id` | `ON DELETE CASCADE` |
| `user_skills.skill_id` | `skills.id` | `ON DELETE CASCADE` |
| `user_skills.user_id` | `users.id` | `ON DELETE CASCADE` |

## Core Relationship Flow

```text
users
  ├─ user_profiles / addresses / educations / experiences / user_skills
  ├─ model_has_roles / model_has_permissions
  ├─ course_orders ──> courses
  ├─ batch_students ──> batches ──> courses
  └─ batch_mentors  ──> batches

courses
  └─ batches
       ├─ class_schedules
       ├─ batch_students
       └─ batch_mentors

frontend_pages
  └─ frontend_sections
```
