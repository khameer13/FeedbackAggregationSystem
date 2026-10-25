# Faculty Feedback System

A PHP + MySQL web app where students rate the faculty who teach their semester, faculty see the feedback they received, and administrators manage accounts and view per-question averages.

Built as a college project (2022) on top of an open-source **Phptpoint** "Online Feedback System" template, then refactored to fix the template's security problems and bugs.

## Features

| Role | What they can do |
| --- | --- |
| Student | Register with a profile picture, log in, give one 12-question feedback form per active faculty member of their semester, update profile and password |
| Faculty | Log in, see the feedback they received (anonymised, with per-question averages), update profile and password |
| Admin | Add, edit, activate/deactivate and delete faculty; manage students; read and delete all feedback and contact messages; view per-question averages and rating distribution for each faculty member |

## Tech stack

PHP 8.1+ (mysqli, prepared statements), MySQL 5.7+ / MariaDB 10.3+, Bootstrap 3, Apache (XAMPP for local development).

## Security hardening

The original template had several serious vulnerabilities. This version fixes them:

| Area | Before | After |
| --- | --- | --- |
| SQL injection | `extract($_POST)` and user input concatenated into every query | Every query uses prepared statements (`db_exec()` / `db_one()` / `db_all()`); `extract()` removed |
| Passwords | Students: unsalted MD5. Faculty and admin: plain text, shown in the admin UI and emailed | `password_hash()` / `password_verify()` for all roles, 8-character minimum, never displayed or emailed |
| Access control | Students and admins shared one session key, so a student could open the admin dashboard; delete/status endpoints had no auth at all | One session entry per role, `require_role()` on every page and action, session ID regenerated on login |
| CSRF | None; deletes were plain GET links | Every form carries a CSRF token; deletes, status changes and logout are POST-only |
| XSS | Database values echoed without escaping | All output passes through `e()` (`htmlspecialchars`) |
| File upload | Any file type, user-chosen name, folder named after the email (path traversal, PHP upload) | Type checked with `finfo` + `getimagesize`, 2 MB limit, random file name, PHP execution disabled in `images/` |
| Credentials | `root` with empty password hard-coded; demo logins in `installation.txt` | DB settings from environment variables; first admin created with a CLI script |
| Personal data | SQL dump with names, emails, phone numbers and password hashes; uploaded photos | Schema-only SQL; uploads git-ignored |

Other fixes: feedback averages were wrong (shared counters, only two questions counted) and are now computed in SQL for all 12 questions; duplicate feedback is prevented by a unique key; deactivated faculty can no longer log in; broken script paths fixed; PHP 8 compatibility.

## Setup (local, XAMPP)

1. Clone into `htdocs/`:
   ```bash
   git clone https://github.com/khameer13/FeedbackAggregationSystem.git
   ```
2. Create the database:
   ```bash
   mysql -u root < database/schema.sql
   ```
3. Optional: set database credentials (defaults are `localhost`, `root`, empty password, `feedback_system`):
   ```bash
   export DB_HOST=localhost DB_USER=feedback DB_PASS='change-me' DB_NAME=feedback_system
   ```
   With Apache you can use `SetEnv DB_USER feedback` etc. in the virtual host instead.
4. Create the first admin (you will be asked for a password):
   ```bash
   php database/create_admin.php admin@example.com
   ```
5. Open `http://localhost/FeedbackAggregationSystem/`. Log in as admin at `/admin/`, add faculty, then register as a student and submit feedback.

## Project structure

```
dbconfig.php            bootstrap: config, DB connection, session, CSRF, auth and helpers
index.php               public site (home, about, contact, student/faculty login, registration)
admin/                  admin dashboard and POST-only action endpoints
user/                   student dashboard and feedback form
faculty/                faculty dashboard
database/schema.sql     database schema (no data)
database/create_admin.php  CLI script to create an admin
```

## Credits

UI and original structure from the Phptpoint "Online Feedback System" template. Security refactor, schema redesign and bug fixes by [Khameer](https://www.linkedin.com/in/khameer13).
