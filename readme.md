# Dental Records Backend

## Where things go

```
config/                    (existing, unchanged)
  config.php
  conn.php
api/helper/
  _api-helpers.php         (existing, unchanged - getCsrfToken/validateCsrfToken/checkRateLimit)
client/
  pages/
    dental-records.php     (existing - update to fetch from records-list.php, see below)
  backend/
    records-guard.php      (new - session check + shared helpers, requires config/conn.php)
    records-list.php        (new - GET, returns current user's records)
    records-download.php    (new - GET ?id=, streams a single file, ownership-checked)
    .htaccess                (new - blocks direct access to records-guard.php)
storage/                    (new - place this OUTSIDE your web document root)
  records/
    {user_id}/
      {random-uuid}.ext     (actual files, never served directly)
```

`storage/` must not be reachable by URL at all. The included `storage/records/.htaccess`
is a backup, not the actual protection -- the real protection is that this folder
lives outside `public_html`/webroot.

## Required: create the tables

Run `records.sql` against `clinicdb` (after `clinicdb.sql`).

## Required: set the encryption key env var

`records-guard.php` reads `RECORDS_ENC_KEY` from the environment. Set it in your
webserver/PHP config (not in a committed file):

```
SetEnv RECORDS_ENC_KEY "<32+ random bytes, base64>"
```

This key isn't used by the two endpoints included here (they assume files are
already encrypted-at-rest by whatever upload process writes into storage/records/),
but records-guard.php defines the constant so any future records-upload.php has
one canonical place to read it from.

## Required: update dental-records.php

Replace the hardcoded `$records = [...]` array with a fetch to `records-list.php`,
and point `submitDownloadAction()` at `records-download.php?id=...` instead of the
simulated `triggerFileDownload()` toast. This intentionally does not touch any
markup/CSS/Tailwind classes -- only the two JS functions and the PHP array.

## Not included here (flagged, not solved)

- **records-upload.php** (staff/admin side, for Dr. Santos's office to attach
  X-rays/notes/prescriptions to a booking) -- ask if you want this next; it needs
  file-type validation, re-encoding of images, and a role check that doesn't exist
  yet in your schema (no `role` column on `users` currently).
- **Dedicated low-privilege MySQL user** for these two tables -- config/config.php
  currently connects as `root`; recommend a separate `records_app` user with only
  SELECT/INSERT on `dental_records` and `record_access_logs`.
- **Session cookie hardening** (`httponly`, `secure`, `samesite`) -- not set in
  config/conn.php or login.php currently; affects the whole app, not just records.