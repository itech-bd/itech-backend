# Architecture Review and Migration Notes

## Existing Application Shape

The project is a Laravel 12 modular monolith using `nwidart/laravel-modules`, Spatie roles/permissions, Blade, Tailwind CSS, Alpine.js and MySQL.

Core domains:

1. **Identity and access** — users, roles, permissions and profile data.
2. **Learning catalog** — courses and pricing.
3. **Delivery** — batches, mentor assignments, student enrollments and class schedules.
4. **Commerce** — course orders and invoices.
5. **Public CMS** — frontend pages, sections, settings, news, reviews, mentors and contact messages.
6. **Presentation** — Blade public pages and role-based dashboard layouts.

The database has 33 tables and 27 declared foreign-key relationships. `users` is the central identity table. `courses -> batches -> class_schedules` is the learning-delivery chain. `batch_students` and `batch_mentors` are the enrollment/assignment pivots. `course_orders` links users, courses and optional batches.

## Problems Found Before the API Work

### 1. API authentication was referenced but not installed

Several module route files used `auth:sanctum`, but Sanctum was not a project dependency, the personal access token table was absent, and `routes/api.php` was not registered in `bootstrap/app.php`.

### 2. Scaffold API routes pointed to Blade controllers

The generated module API resources referenced controllers whose methods render HTML views or were incomplete scaffolds. Those routes were not a reliable JSON contract.

### 3. API behavior was spread across Blade controllers

Public page queries, menu definitions, dashboard queries and student authorization were embedded in controllers/views. A Next.js frontend would otherwise need to reproduce backend business rules.

### 4. Public GET requests could write to the database

The existing CMS loader used `firstOrCreate` while reading a public page. A GET request should not create records. The API implementation performs read-only lookups and returns empty sections for missing CMS pages.

### 5. Pending checkout changes could leave stale enrollments

When an existing pending order changed to another batch, the old pending `batch_students` record could remain. The API checkout flow now updates the order and enrollment atomically and removes the obsolete pending enrollment.

### 6. Pending students could receive too much class detail

The existing policy allowed pending and approved students to view a batch. The API keeps general batch visibility but returns live-class and recorded-video links only after enrollment approval.

### 7. Menu definitions were hard-coded in Blade

The public navigation and student menu were presentation-layer data. The API now returns stable menu keys, labels and Next.js paths from backend endpoints.

## Implemented API Architecture

```text
Next.js
  -> /api/v1/public/*       public site content
  -> /api/v1/auth/*         registration, login and account recovery
  -> /api/v1/checkout/*     authenticated enrollment/order flow
  -> /api/v1/student/*      protected student panel

Laravel
  -> Versioned route contract
  -> Locale middleware
  -> Sanctum authentication
  -> Role + email verification middleware
  -> Domain-focused API controllers
  -> Eloquent models and existing modular domain logic
  -> MySQL
```

The Blade website and existing admin panel remain operational. This is an incremental strangler migration: Next.js can replace public/student screens without forcing an immediate admin-panel rewrite.

## Recommended Next.js Shape

```text
src/
  app/
    (public)/
      page.tsx
      about/page.tsx
      courses/page.tsx
      courses/[slug]/page.tsx
      mentors/page.tsx
      mentors/[slug]/page.tsx
      reviews/page.tsx
      news/page.tsx
      news/[slug]/page.tsx
      contact/page.tsx
    (auth)/
      login/page.tsx
      register/page.tsx
      forgot-password/page.tsx
      reset-password/page.tsx
    student/
      layout.tsx
      page.tsx
      profile/page.tsx
      courses/page.tsx
      courses/[slug]/page.tsx
      batches/page.tsx
      batches/[id]/page.tsx
      mentors/page.tsx
      invoices/page.tsx
      invoices/[id]/page.tsx
  lib/
    api/
      server-client.ts
      browser-client.ts
      types.ts
      errors.ts
    auth/
      session.ts
      guards.ts
  middleware.ts
```

Use React Server Components for public data and initial student-page loads. Use client components only for forms, filters, modals and interactive state.

## Authentication Recommendation

Use a Next.js backend-for-frontend pattern:

1. Next.js login action calls Laravel `/api/v1/auth/login`.
2. Next.js stores the returned token in a secure, HTTP-only, same-site cookie.
3. Server Components and Route Handlers read the cookie and call Laravel.
4. Browser JavaScript never receives the raw token.
5. Logout calls Laravel and removes the cookie.

Cookie-only Sanctum SPA authentication is also supported, but cross-subdomain CSRF, CORS and cookie-domain settings must be deployed carefully.

## Further Backend Improvements

### Near term

- Run and expand the included API Pest feature tests in CI.
- Generate TypeScript clients from the checked-in OpenAPI contract and validate the contract in CI.
- Extract shared serializers into Laravel API Resource classes as the contract grows.
- Extract dashboard and checkout queries into application services.
- Add database indexes for frequent filters after measuring query plans.
- Add idempotency keys to order creation if online payments are added.
- Add payment transactions as a separate immutable ledger instead of overloading `course_orders.status`.

### Data model

- Replace free-form status strings/enums with PHP backed enums and shared validation.
- Add explicit unique constraints where business rules require them, especially batch mentor/student pivots.
- Consider a dedicated `enrollments` model instead of treating `batch_students` only as a pivot.
- Add audit columns/events for approval, invoice-status and enrollment changes.
- Move navigation to CMS tables only if admins need to reorder or edit menus.

### Performance

- Cache public bootstrap/settings and CMS pages.
- Eager-load all public/student relations; avoid lazy loading in production.
- Add response compression at the proxy/CDN.
- Use pagination everywhere; the API caps page size at 50.
- Keep invoice PDFs and private class links out of public/CDN caches.

### Security

- Serve both applications over HTTPS.
- Keep Sanctum tokens in HTTP-only cookies when using Next.js.
- Rate-limit login, registration, contact, verification and password-reset endpoints.
- Validate all uploaded files and keep the public storage link correctly configured.
- Do not distribute production SQL dumps. The provided dump contains runtime cache data with email/IP-like identifiers; clear cache/session/reset-token tables before using a copy outside the trusted environment.
- Rotate credentials and application secrets whenever a production backup is shared.

## Migration Sequence

1. Deploy this Laravel API without removing Blade routes.
2. Verify public API responses against the current Blade pages.
3. Build Next.js public layout and homepage using `/public/bootstrap` and `/public/home`.
4. Migrate public pages one route at a time.
5. Implement Next.js authentication and secure token storage.
6. Migrate the student dashboard, courses, batches, mentors, invoices and profile.
7. Add end-to-end tests comparing permissions and enrollment behavior.
8. Switch production routing to Next.js for public/student paths.
9. Keep Laravel Blade admin routes until a separate admin migration is justified.
