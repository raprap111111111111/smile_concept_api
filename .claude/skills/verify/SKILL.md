---
name: verify
description: Build/launch/drive recipe for verifying smile_concept_api changes at the HTTP surface.
---

# Verifying smile_concept_api

Laravel 11+, PHP 8.4, MySQL (`smileconcept` on localhost, root/no password), Passport auth, queue = database.

## Launch

```bash
php artisan serve --port=8099   # run in background
```

## Auth handle

No known seeded passwords. Mint Passport tokens directly:

```bash
php artisan tinker --execute="echo \App\Models\User::find(ID)->createToken('verify')->accessToken;"
```

Known users (dev DB): patient role → `User::role('patient')->first()` (id 7); super-admin → id 22. Roles `admin`/`receptionist` have 0 users — notifications targeting only `admin` role go nowhere; booking flow notifies `['admin','super-admin','owner']`.

## Drive

Base URL `http://127.0.0.1:8099/api/v1`, headers `Authorization: Bearer <token>`, `Accept: application/json`.

- Book: `POST /appointments` `{doctor_id, branch_id, start_time: "Y-m-d H:i:s", end_time, reason_for_visit}` (doctor 2 / branch 1 exist)
- Reschedule: `PUT /appointments/{id}` with new `start_time`/`end_time`
- Status: `PATCH /appointments/{id}/status`

## Gotchas

- Notifications implementing `ShouldQueue` land in `jobs` table — drain with `php artisan queue:work --stop-when-empty` before asserting on `notifications` table.
- Multi-line tinker `--execute` with `\\` in strings breaks on Windows — write a script file and run `php artisan tinker script.php`.
- Inspect state via tinker: `AppointmentReminder::where('appointment_id', X)`, `User::find(Y)->notifications()`.
- `MAIL_MAILER=log` — email output goes to `storage/logs/laravel.log`.
- Clean up test rows after: appointments cascade-delete their reminders.
