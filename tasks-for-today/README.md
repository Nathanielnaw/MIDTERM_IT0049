# Tasks for Today Management System

CodeIgniter 4 implementation for IT0049 Technical Summative Assessment 1.

## Requirements

- PHP 8.1 or later
- Composer
- MySQL or MariaDB

## Setup

1. Create a MySQL database named `tasks_today`.
2. Copy `.env.example` to `.env` and update the database credentials.
3. Run `composer install`.
4. Run `php spark migrate`.
5. Run `php spark db:seed TasksTodaySeeder`.
6. Start the application with `php spark serve`.
7. Open `http://localhost:8080`.

## Routes

- `/` — Welcome page showing only tasks scheduled for today
- `/tasks` — Full task list ordered by date
- `/profile` — The single demo user's profile
- `/about` — Static developer and project information

The seeded task data uses 30 September 2026 as today's date for this submission and includes eight tasks across three dates.
