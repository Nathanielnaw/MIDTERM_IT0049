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
