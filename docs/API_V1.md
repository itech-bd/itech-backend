# API v1 Contract

The Laravel application now exposes a versioned API for the Next.js public website and student panel.

## Base URL

```text
http://localhost:8000/api/v1
```

All requests should send:

```http
Accept: application/json
Content-Type: application/json
X-Locale: en
```

`X-Locale` supports `en` and `bn`. A `?locale=bn` query parameter is also accepted.

Machine-readable contract: [`openapi.json`](openapi.json). Import [`itech-api-v1.postman_collection.json`](itech-api-v1.postman_collection.json) into Postman for manual testing.

## Installation

The API uses Laravel Sanctum.

```bash
composer install
php artisan migrate
php artisan storage:link
php artisan optimize:clear
```

Configure the Next.js origin:

```dotenv
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
FRONTEND_URLS=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DOMAIN=
```

For production, use HTTPS and replace the localhost values with the real frontend and API domains.

## Authentication Modes

### Bearer token mode

`POST /auth/login` returns a Sanctum token by default. Send it in subsequent requests:

```http
Authorization: Bearer <token>
```

For a Next.js application, the safer approach is to store this token in a secure, HTTP-only cookie managed by a Next.js Route Handler or Server Action. Do not expose the token to browser JavaScript or localStorage.

### First-party SPA cookie mode

Laravel's stateful Sanctum middleware is enabled. A same-site Next.js frontend may use cookie authentication after configuring CORS, `SANCTUM_STATEFUL_DOMAINS`, and CSRF handling. Send `issue_token: false` to the login endpoint when using cookie-only mode.

## Response Envelope

Successful JSON response:

```json
{
  "success": true,
  "message": null,
  "data": {}
}
```

Error response:

```json
{
  "success": false,
  "message": "The provided data is invalid.",
  "code": "VALIDATION_ERROR",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

Paginated endpoints place `items` and `pagination` inside `data`.

## Public Website Endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/public/bootstrap` | Site settings, header menu, footer menu, locale and auth configuration |
| GET | `/public/home` | Homepage CMS sections, stats, popular courses, course tracks, batches, mentors, reviews and news |
| GET | `/public/pages/{slug}` | CMS content for about, solutions, privacy, terms, contact and other public pages |
| GET | `/public/courses` | Active courses with search, track and pagination filters |
| GET | `/public/courses/{course}` | Public course details, available batches and related courses |
| GET | `/public/mentors` | Active mentor list |
| GET | `/public/mentors/{mentor}` | Mentor profile and related courses |
| GET | `/public/profiles/{publicUrl}` | Public student/mentor profile by configured slug |
| GET | `/public/reviews` | Approved student reviews |
| GET | `/public/news` | Published news list |
| GET | `/public/news/{newsUpdate}` | Published news details and related news |
| POST | `/public/contact` | Submit the public contact form |

Supported page slugs:

```text
home
about
courses
mentors
reviews
news
software-solutions
it-solutions
web-hosting-solutions
privacy
terms
contact
```

List endpoints support `page` and `per_page`. `per_page` is capped at 50.

Course filters:

```text
GET /api/v1/public/courses?search=marketing&track=Digital%20Marketing&page=1
```

## Authentication Endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/auth/register` | Create a student account and send email verification |
| POST | `/auth/login` | Login and optionally issue a Sanctum token |
| POST | `/auth/resend-verification` | Resend the verification email |
| POST | `/auth/forgot-password` | Send a password reset link |
| POST | `/auth/reset-password` | Reset password using the emailed token |
| GET | `/auth/me` | Current authenticated user, roles and permissions |
| POST | `/auth/logout` | Revoke the current token/session |
| POST | `/auth/logout-all` | Revoke all user tokens and the current session |

Login request:

```json
{
  "email": "student@example.com",
  "password": "password",
  "device_name": "nextjs-web",
  "issue_token": true
}
```

## Checkout Endpoints

Authentication and verified email are required.

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/checkout/courses/{course}` | Course checkout preview, batch options and existing enrollments |
| POST | `/checkout/courses/{course}` | Create or update the user's pending course order |
| GET | `/checkout/orders/{order}` | Read the authenticated user's order summary |

Checkout request:

```json
{
  "batch_id": 15,
  "batch_type": "online"
}
```

When a pending order switches batches, the API removes the obsolete pending enrollment and creates the new one inside a database transaction.

## Student Panel Endpoints

All endpoints require `auth:sanctum`, a verified email and the `student` role.

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/student/dashboard` | Student menu, stats, upcoming classes, recent batches and orders |
| GET | `/student/courses` | Enrolled course list |
| GET | `/student/courses/{course}` | Enrolled course and its student batches |
| GET | `/student/batches` | Pending and approved batch list |
| GET | `/student/batches/{batch}` | Batch details; class links are returned only for approved enrollment |
| GET | `/student/mentors` | Mentors assigned to approved student batches |
| GET | `/student/invoices` | Student invoices with optional status filter |
| GET | `/student/invoices/{order}` | Invoice details |
| GET | `/student/invoices/{order}/download` | Authenticated PDF invoice download |

Student batch filter:

```text
GET /api/v1/student/batches?status=approved
```

Invoice filter:

```text
GET /api/v1/student/invoices?status=paid
```

## Student Profile Endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/student/profile` | Complete profile, address, education, experience and skills |
| POST | `/student/profile` | Update name, email and profile image using multipart form data |
| PATCH | `/student/profile/details` | Update personal details |
| PATCH | `/student/profile/public-url` | Update public profile slug |
| PUT | `/student/profile/address` | Create or update address |
| PUT | `/student/profile/password` | Change password and revoke all tokens |
| POST | `/student/profile/educations` | Add education |
| PATCH | `/student/profile/educations/{education}` | Update owned education |
| DELETE | `/student/profile/educations/{education}` | Delete owned education |
| POST | `/student/profile/experiences` | Add experience |
| PATCH | `/student/profile/experiences/{experience}` | Update owned experience |
| DELETE | `/student/profile/experiences/{experience}` | Delete owned experience |
| POST | `/student/profile/skills` | Attach a skill |
| PATCH | `/student/profile/skills/{skill}` | Update proficiency |
| DELETE | `/student/profile/skills/{skill}` | Detach a skill |

Use `POST /student/profile` for profile-image uploads because multipart `PATCH` parsing is inconsistent across PHP deployments.

## Next.js Server-Side Request Example

```ts
const response = await fetch(`${process.env.LARAVEL_API_URL}/api/v1/student/dashboard`, {
  headers: {
    Accept: "application/json",
    Authorization: `Bearer ${token}`,
    "X-Locale": "en",
  },
  cache: "no-store",
});

if (!response.ok) {
  const error = await response.json();
  throw new Error(error.message ?? "API request failed");
}

const payload = await response.json();
```

## Cache Recommendations

- Cache `/public/bootstrap` for 5–15 minutes.
- Cache mostly static CMS pages with tag-based invalidation after admin edits.
- Keep checkout and all student endpoints uncached/private.
- Use Next.js `revalidateTag` after CMS changes once the admin panel is integrated with Next.js.
