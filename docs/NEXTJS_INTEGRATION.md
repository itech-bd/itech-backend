# Next.js Integration Blueprint

## Environment

```dotenv
LARAVEL_API_URL=http://localhost:8000
NEXT_PUBLIC_SITE_URL=http://localhost:3000
AUTH_COOKIE_NAME=itech_student_token
```

`LARAVEL_API_URL` must remain server-only. Do not prefix it with `NEXT_PUBLIC_` unless direct browser calls are intentionally required.

## Server API Client

```ts
// src/lib/api/server-client.ts
import { cookies } from "next/headers";

const API_URL = process.env.LARAVEL_API_URL!;
const COOKIE_NAME = process.env.AUTH_COOKIE_NAME ?? "itech_student_token";

export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
    public code?: string,
    public errors?: Record<string, string[]>,
  ) {
    super(message);
  }
}

export async function apiFetch<T>(
  path: string,
  init: RequestInit = {},
): Promise<T> {
  const token = (await cookies()).get(COOKIE_NAME)?.value;
  const headers = new Headers(init.headers);
  headers.set("Accept", "application/json");
  headers.set("X-Locale", "en");

  if (token) {
    headers.set("Authorization", `Bearer ${token}`);
  }

  const response = await fetch(`${API_URL}/api/v1${path}`, {
    ...init,
    headers,
    cache: init.cache ?? "no-store",
  });

  const body = await response.json();

  if (!response.ok || body.success === false) {
    throw new ApiError(
      body.message ?? "API request failed",
      response.status,
      body.code,
      body.errors,
    );
  }

  return body.data as T;
}
```

## Login Server Action

```ts
"use server";

import { cookies } from "next/headers";
import { redirect } from "next/navigation";

export async function loginAction(formData: FormData) {
  const response = await fetch(
    `${process.env.LARAVEL_API_URL}/api/v1/auth/login`,
    {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        email: formData.get("email"),
        password: formData.get("password"),
        device_name: "nextjs-web",
        issue_token: true,
      }),
      cache: "no-store",
    },
  );

  const body = await response.json();
  if (!response.ok) {
    return { ok: false, message: body.message, errors: body.errors };
  }

  (await cookies()).set(
    process.env.AUTH_COOKIE_NAME ?? "itech_student_token",
    body.data.access_token,
    {
      httpOnly: true,
      secure: process.env.NODE_ENV === "production",
      sameSite: "lax",
      path: "/",
      maxAge: 60 * 60 * 24 * 30,
    },
  );

  redirect("/student");
}
```

## Student Route Guard

Use Next.js middleware only for a fast cookie-presence check. The Laravel API remains the authorization source of truth.

```ts
// src/middleware.ts
import { NextRequest, NextResponse } from "next/server";

export function middleware(request: NextRequest) {
  const cookieName = process.env.AUTH_COOKIE_NAME ?? "itech_student_token";
  const token = request.cookies.get(cookieName)?.value;

  if (!token) {
    const login = new URL("/login", request.url);
    login.searchParams.set("next", request.nextUrl.pathname);
    return NextResponse.redirect(login);
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/student/:path*"],
};
```

A missing cookie can redirect early, but role, verification and ownership checks must always be enforced by Laravel.

## Rendering Strategy

- Public homepage: server-render and revalidate every few minutes.
- Course/news detail: server-render by slug for SEO.
- Public lists: use URL search parameters for search, filter and pagination.
- Student pages: dynamic server rendering with `cache: "no-store"`.
- Forms: Server Actions or Route Handlers that proxy to Laravel.
- Invoice PDF: proxy the authenticated binary response through a Next.js Route Handler so the browser never handles the Laravel token.
