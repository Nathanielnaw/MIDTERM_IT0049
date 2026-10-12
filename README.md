# IT0049 Midterm — The Complete Point-of-Sale System

Developer: **Alfred Nathaniel Embuscado**.

The POS lives at `/pos`, alongside the preserved TSA1/TFA4/TSA2 task application.
The workspace contained the task application, not a prior POS implementation.
Existing task pages and their routes remain available. All POS management pages
require an active staff account; there is no public registration.

## Quick start

Requirements: PHP 8.2+, Composer, `intl`, `mbstring`, `fileinfo`, and `mysqli`
(MySQL/MariaDB) or `sqlite3` (SQLite). Enable **GD** for optional image uploads.
Use MySQL/MariaDB with InnoDB tables for transactional sales and foreign keys.
Serve the repository's `public/` directory; never expose the project root.

1. Run `composer install` from this root.
2. For a new installation only, copy `.env.example` to `.env`. Preserve an
   existing `.env`. Configure the database and `app.baseURL`.
3. Set a private **POS_DEMO_PASSWORD** of 12–72 characters in `.env`.
4. Run the following commands:

```powershell
php spark migrate
php spark db:seed PosSeeder
php spark serve
```

Open <http://localhost:8080/pos> and log in as **pos.admin** with the password
configured locally. The seed creates this account only if missing, three sample
products and one sample customer if those tables are empty. Repeated seeding
preserves edits, sales, stock and existing passwords. No sales are fabricated.
Change staff passwords through Staff → Edit; blank passwords retain the hash.
The older task demo account and `TFA4_DEMO_PASSWORD` workflow remain independent.

For XAMPP with SQLite/GD installed but disabled, configure
`database.default.DBDriver = SQLite3` and
`database.default.database = tasks-today.sqlite` in `.env`, then use:

```powershell
php -d extension=sqlite3 -d extension=gd spark migrate
php -d extension=sqlite3 -d extension=gd spark db:seed PosSeeder
php -d extension=sqlite3 -d extension=gd -S localhost:8080 -t public system/rewrite.php
```

Use these flags only for extensions not already enabled in php.ini. For MySQL,
create the configured database first, as described in the retained setup below.
Do not commit `.env`, uploaded images, database backups or credentials.

## POS routes

| Method | Route | Purpose |
| --- | --- | --- |
| GET/POST | `/login` | Staff login (public) |
| POST | `/logout` | Clear and destroy session |
| GET | `/pos` | Record Sale form |
| POST | `/pos/sales` | Atomic sale and stock deduction |
| GET | `/pos/sales` | Joined sales history, newest date/ID first |
| GET | `/pos/products`, `/pos/customers`, `/pos/staff` | Active records |
| GET | `/pos/{resource}/new`, `/pos/{resource}/{id}/edit` | Management forms |
| POST | `/pos/{resource}/create`, `/pos/{resource}/{id}/update` | Save records |
| POST | `/pos/{resource}/{id}/archive` | Archive record |

Here `{resource}` is products, customers or staff. Every `/pos` route uses the
`staff` authentication filter, which checks that the session's database user is
still active. Archived staff cannot log in or retain access to the POS. Staff
cannot archive their own signed-in account. All active staff have management
permissions, as requested; separate administrative roles are not implemented.

Products, customers and staff use soft archiving to preserve sales references.
Archived records are excluded from sale selectors. Missing/archived edit IDs
return 404. The existing users table is extended with avatar and archive fields;
its legacy email column and nullable password column are retained for compatibility.
All staff created through the POS or its seeder require a nonempty password hash;
legacy rows without a hash cannot authenticate. Existing passwords are preserved.

## Sales and validation

Prices must be positive with at most two decimal places, up to 99,999,999.99.
Stock and sale quantities are limited to 1,000,000, with zero allowed only for
stock. Customer names/emails are required, phone is optional. Staff usernames
are unique (including archived accounts) and allow letters, digits, dots,
underscores and hyphens. New/changed passwords require 12–72 characters.

The server selects the current product price and multiplies integer cents by
quantity. Client totals and staff IDs are ignored. A database transaction wraps
the conditional stock deduction and sale insert. The stock update requires an
active product, unchanged price and sufficient stock, so competing sales cannot
subtract stock below zero. Failed writes roll back the transaction; a changed
price or insufficient stock requires a fresh submission. SQLite contention may
reject a competing request; the user can retry. History joins products, optional
customers and staff; unassigned customers display as walk-ins. Amounts use PHP
peso labels and are stored to two decimal places.

## Images

Product images and staff avatars accept JPEG/PNG only, up to 2 MB, 4000 pixels
per side and 8 megapixels. The server decodes and re-encodes images as JPEG,
resizes them to at most 800 pixels and generates random filenames. Source
metadata and executable payloads are not copied. GD is required for uploads;
without it users can still save records without images. Missing files use a
local placeholder. The `public/uploads/pos/` directory must be writable by PHP;
its Apache configuration disables directory listing and executable file types.
Configure equivalent rules on other servers. Blank upload fields preserve the
existing image. Replaced image files remain on disk; back up and manage this
ignored directory separately from Git.

## Verification and submission

```powershell
composer validate --strict
php -d extension=sqlite3 -d extension=gd vendor/bin/phpunit --no-coverage
php -d extension=sqlite3 spark routes
php -d extension=sqlite3 spark migrate:status
```

The tests cover prior task behavior plus POS route protection, active accounts,
CRUD, hashes and blank-password edits, validation, server totals, optional
customers, stock limits, rollback on insert failure, archived history, repeatable
seeding, CSRF rejection and safe image resizing. Database tests run in isolated
in-memory SQLite. Image tests mock the PHP upload-origin check and exercise the
actual decoder/resizer; they do not simulate a browser multipart upload.

Clean SQLite migration/seeding and upgrade of the local SQLite database were
checked. MySQL and multi-process contention have not been tested in this workspace.
GD must be enabled in the PHP process serving the application. Local POS login
requires setting your own POS_DEMO_PASSWORD and running PosSeeder.

Repository: https://github.com/Nathanielnaw/MIDTERM_IT0049

Hosting is not deployed. For submission, configure production PHP hosting, HTTPS,
private environment settings, the database, writable/upload permissions, and run
migrations/seeding. Submit the repository URL and hosted URL if required by the
assessment, with credentials shared privately. Export instructions and retained
task-app documentation follow below.

---
# TSA2 - Tasks for Today Management System

IT0049 Technical Summative Assessment 2: Full CRUD and Authentication.
Developer: **Alfred Nathaniel Embuscado**. Continues TSA1 and TFA4.

The runnable application is at the repository root using the bundled CodeIgniter
4.7.4 framework in `system/`. The earlier `tasks-for-today/` draft is preserved;
do not run setup from that subdirectory.

## Prerequisites and setup

Use PHP 8.2+, Composer, `intl`, `mbstring`, and `mysqli` (MySQL/MariaDB) or
`sqlite3` (SQLite). Tests additionally need SQLite, DOM, and XML extensions.
Run commands from the repository root:

```powershell
composer install
Copy-Item .env.example .env
```

Only copy the example for a new installation; preserve an existing `.env`.
Set your local `.env` values, including `app.baseURL`, `app.appTimezone`,
database credentials, and a private `TFA4_DEMO_PASSWORD`. The legacy variable
name remains compatible with TFA4. The example deliberately contains no password.
The login username is `demo.student`. Never commit `.env` or actual credentials.

For MySQL/MariaDB, start the database service and create the database:

```sql
CREATE DATABASE tasks_today CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

Set `database.default.DBDriver = MySQLi` and the database name and credentials
in `.env`, then run:

```powershell
php spark migrate
php spark db:seed TasksTodaySeeder
php spark serve
```

Open <http://localhost:8080>. For SQLite instead, set
`database.default.DBDriver = SQLite3` and
`database.default.database = tasks-today.sqlite` in `.env`. With XAMPP's SQLite
extension installed but disabled, run:

```powershell
php -d extension=sqlite3 spark migrate
php -d extension=sqlite3 spark db:seed TasksTodaySeeder
php -d extension=sqlite3 -S localhost:8080 -t public system/rewrite.php
```

The relative SQLite database is stored in `writable/`. Omit `-d extension=sqlite3`
when already enabled. The default application timezone is Asia/Manila.
For Apache or hosting, serve **public/** as the document root, enable URL
rewriting, and configure the actual base URL with a trailing slash. Set
`CI_ENVIRONMENT = production` on a host and enable HTTPS.

## Passwords and preserving existing data

Migrations add `users.password` and `tasks.is_archived` to earlier installations.
Existing tasks remain active. The seeder inserts eight sample tasks only when
the task table is empty, creates the missing demo account, and fills only the
demo account's missing password hash. It preserves existing tasks, profiles,
other accounts, and existing passwords. Sample dates are relative to the first
seeding date and are not moved on subsequent runs.

Passwords are generated with PHP `password_hash()` and checked with
`password_verify()`. No password hashes are selected for the public profile.
To change the demo password, change `TFA4_DEMO_PASSWORD` locally and run:

```powershell
php spark demo:password
# If SQLite is not enabled globally:
php -d extension=sqlite3 spark demo:password
```

Reseeding alone intentionally does not rotate an existing password. Do not put
real passwords in command arguments, source files, screenshots, or submissions.
Login regenerates the session ID and stores only the user's ID, username and
name. Invalid credentials receive a generic error. POST logout clears and
destroys the session and redirects to login.

## Routes and behavior

| Method | Route | Access / behavior |
| --- | --- | --- |
| GET | `/` | Public; today's non-archived tasks |
| GET | `/tasks` | Public; non-archived tasks ordered by date, then ID |
| GET | `/profile` | Public; demo user profile only |
| GET | `/about` | Public; static project information |
| GET, POST | `/login` | Public; demo login |
| POST | `/logout` | Authenticated; end session |
| GET | `/tasks/new` | Authenticated; new task form |
| POST | `/tasks/create` | Authenticated; insert task |
| GET | `/tasks/{id}/edit` | Authenticated; prefilled form |
| POST | `/tasks/{id}/update` | Authenticated; update task |
| POST | `/tasks/{id}/archive` | Authenticated; set `is_archived = 1` |

Management controls appear only while logged in. Both forms require a title
(maximum 150 characters) and a valid date; status is pending or completed.
Validation errors retain entered values. Missing or archived tasks return 404.
Archiving retains the database row and removes it from both public lists;
completed tasks remain visible until archived. Controllers use Models and Query
Builder; views escape values and do not query the database. Automatic routing
is disabled, all forms include CSRF tokens, and all mutations use explicit POST
routes (no method spoofing is needed). Requests with invalid CSRF tokens are
rejected before route authentication; valid logged-out management requests
redirect to login.

## Verification

```powershell
composer validate --strict
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage
php -d extension=sqlite3 spark routes
php -d extension=sqlite3 spark migrate:status
```

Tests use an isolated in-memory SQLite database. They cover public pages,
protected management routes, valid/invalid login, logout, creation, prefilled
editing, required fields, invalid dates/statuses, missing IDs, CSRF rejection,
POST-only mutations, soft archive row retention and visibility, stable ordering,
timezone boundaries, escaped output, and repeatable migrations/seeding.

## Database export and submission

Migrations plus the environment-driven seeder are the repeatable database setup;
no live database or credential hash is committed. For a private MySQL export:

```powershell
mysqldump -u YOUR_DB_USER -p --single-transaction --result-file=build/tasks_today.sql tasks_today
```

The command prompts for the database password. Alternatively use phpMyAdmin's
Export tab. For SQLite, stop the application and copy the database into a private
backup directory. Keep exports private: they contain password hashes. `build/`
and local SQLite databases are ignored by Git. For a public schema-only MySQL
export, add `--no-data` and use the seeder to create the demo account locally.

Repository submission: https://github.com/Nathanielnaw/TSA2

The student still needs a hosted application URL. Configure PHP hosting and its
database, install production dependencies (`composer install --no-dev`), set
private environment values, migrate/seed, and verify login and CRUD over HTTPS.
Submit the repository and hosted URL through the assessment portal. Hosting has
not been deployed by this implementation; no hosting destination or credentials
were supplied. Share assessment login credentials only through a private channel.

---
# CodeIgniter 4 Framework

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds the distributable version of the framework.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Contributing

We welcome contributions from the community.

Please read the [*Contributing to CodeIgniter*](https://github.com/codeigniter4/CodeIgniter4/blob/develop/CONTRIBUTING.md) section in the development repository.

## Server Requirements

PHP version 8.2 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - The end of life date for PHP 8.1 was December 31, 2025.
> - If you are still using below PHP 8.2, you should upgrade immediately.
> - The end of life date for PHP 8.2 will be December 31, 2026.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library
