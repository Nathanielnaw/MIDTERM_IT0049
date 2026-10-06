# Tasks for Today Management System

IT0049 Technical Summative Assessment 1. Developer: **Alfred Nathaniel Embuscado**
(the name retained from the existing project draft).

The runnable application is at this workspace root. The earlier `tasks-for-today/`
draft is preserved; run the commands below from the root, not that subdirectory.
This project uses the existing bundled CodeIgniter 4.7.4 framework in `system/`.

## Application setup

Requires PHP 8.2+, Composer, `intl`, `mbstring`, and either `mysqli` or `sqlite3`.
Install the locked dependencies with `composer install`.

### MySQL / MariaDB

1. Start MySQL (for example, in XAMPP) and create an empty database:
   `CREATE DATABASE tasks_today CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;`
2. Copy `.env.example` to `.env` and set your database credentials.
3. Run `php spark migrate`.
4. Run `php spark db:seed TasksTodaySeeder`.
5. Run `php spark serve` and open <http://localhost:8080>.

### SQLite local setup

The local `.env` is configured for SQLite because MySQL was not running during
setup. To reproduce it, copy `.env.example` to `.env`, set
`database.default.DBDriver = SQLite3` and
`database.default.database = tasks-today.sqlite`, then run:

```powershell
php -d extension=sqlite3 spark migrate
php -d extension=sqlite3 spark db:seed TasksTodaySeeder
php -d extension=sqlite3 -S localhost:8080 -t public system/rewrite.php
```

Open <http://localhost:8080>. The database file is `writable/tasks-today.sqlite`.
The `-d` flag enables XAMPP's installed SQLite extension for that process without
editing the global PHP configuration. Omit the flag if SQLite is already enabled.
If SQLite is enabled in php.ini, `php spark serve` also works.

For Apache, point the document root to **`public/`**, enable URL rewriting, and
set `app.baseURL` in `.env` to the actual URL (with a trailing slash). Enable the
chosen database extension in Apache's PHP configuration. Do not serve the project
root, which contains private application files.

## Pages and data flow

| Route | Controller | Data |
| --- | --- | --- |
| `/` | `Home` | `TaskModel::forDate()` for today in `app.appTimezone` |
| `/tasks` | `Tasks` | All tasks, ordered by `task_date ASC, id ASC` |
| `/profile` | `Profile` | One database user with username `demo.student` |
| `/about` | `About` | Static student/developer and assessment information |

The default timezone is **Asia/Manila** and can be overridden in `.env`.
Controllers call models; views render escaped values and never query the database.
The migration creates `tasks` and `users` with the required fields, lengths,
non-null columns, pending status default, primary keys, and unique username.
It supports MySQL/MariaDB and SQLite through CodeIgniter's database forge.

The seeder creates one demo user (`demo.student`, Demo Student,
`student@example.com`) and eight tasks relative to the date when first seeded:
four today, two tomorrow, and two the following day. It fills an empty task table
and creates a missing demo account independently, without overwriting existing
records or duplicating data on reruns. Seeded dates stay fixed afterward; the
Welcome page always follows the actual current day, so it may later be empty.
No authentication or task editing is required by this four-page assessment.

## Verification

```powershell
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage
php spark routes
```

Tests use an isolated in-memory SQLite database and cover the Manila midnight
boundary, stable ordering, the single demo profile, repeatable seeding, empty
states, HTML escaping, and the About page. They do not modify the local database.

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
