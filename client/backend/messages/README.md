# Messaging System — Stage 1 (schema done, submit + status live)

## Where things go

```
client/
  backend/
    messages/
      messages-guard.php      (session/role helper — login optional, unlike records-guard.php)
      messages-notify.php     (PHPMailer wrapper + category → email routing)
      message-submit.php      (POST — create a message, guest or logged-in)
      message-status.php      (GET ?case=MSG-XXXX-XX — guest-safe status/thread lookup)
      .htaccess
```

## Required: install PHPMailer

From your project root (where `composer.json` should live, alongside `config/`):

```
composer require phpmailer/phpmailer
```

This creates `vendor/autoload.php`, which `messages-notify.php` requires.
If you don't have Composer on this machine yet, install it first from
getcomposer.org — no account needed, it's a CLI tool.

## Required: SMTP environment variables

Set these in your Apache vhost or `php.ini` (XAMPP: `httpd-vhosts.conf` `SetEnv`
lines work fine for local dev):

```
SetEnv SMTP_HOST "smtp.gmail.com"
SetEnv SMTP_USERNAME "your-gmail@gmail.com"
SetEnv SMTP_PASSWORD "16-character app password, not your real password"
SetEnv SMTP_PORT "587"
SetEnv SMTP_FROM "no-reply@dentalcarepro.example"
```

To get a Gmail App Password: enable 2-Step Verification on the Gmail
account, then generate one at myaccount.google.com/apppasswords. Use a
throwaway/demo Gmail account for this, not a personal one.

## Required: update the category → email map

Edit `CATEGORY_NOTIFY_EMAIL` in `messages-notify.php` — right now it points
at placeholder `@example.com` addresses. Since this is a portfolio project,
pointing all four at the same real inbox you check is fine; the point is
demonstrating the routing logic exists, not running four real departments.

## Required: seed staff/admin accounts for the demo

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-demo-admin@example.com';
UPDATE users SET role = 'staff' WHERE email = 'your-demo-staff@example.com';
```
(Create these two accounts first via your normal registration flow if they
don't exist, then promote them with the above.)

## Not built yet (next stages)

- **Client-side thread view** — a page where a logged-in client sees their
  own message history and can reply (case-code guests would use
  message-status.php's data instead, no login needed).
- **Staff/admin dashboard** — list messages by category/status, reply,
  reassign, mark resolved. This is the biggest remaining chunk.
- **message-reply.php** — endpoint for adding a reply to `message_replies`,
  needed by both the client thread view and the staff dashboard. Trivial to
  add once we know what the reply UI looks like.