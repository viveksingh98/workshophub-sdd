# WorkshopHub — SDD Demo App

The working companion app for the **SDD Masterclass** and the **Spec Kit Hands-On** videos on [Prompt Vidya AI](https://www.youtube.com/@PromptVidyaAI). Everything shown in the videos — the booking flow, the owner dashboard, and the Spec Kit–built **equipment reservation** feature — runs in this repo.

## Stack

- Laravel monolith, server-rendered Blade pages
- MySQL-ready (`docker-compose.yml`), SQLite for the local quickstart
- Native JavaScript in `public/assets/js/workshophub.js` — no frontend frameworks
- Theme CSS folders in `public/assets/css/themes`

## Quickstart (SQLite — no Docker needed)

Requires PHP 8.3+ and Composer.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Open http://127.0.0.1:8000 — the seeded demo data makes every view presentable on first boot.

## Run with MySQL

```bash
docker compose up -d mysql
cp .env.example .env   # set the DB_* values to the MySQL container
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

## Run the tests

```bash
php artisan test
```

## What's inside

- **Public site** — class discovery, instructors, FAQ, blog, contact.
- **Booking flow** — validation, capacity check, automatic student profiles.
- **Equipment reservation** *(the Spec Kit videos' feature)* — time-window
  reservations with an overlap guard inside a locked database transaction,
  creator-only cancellation, and equipment/day filtering. Open the
  **Equipment** tab.
- **Owner workspace** — metrics, booking status, classes, students, notes,
  content, themes, settings.
- **Setup wizard** and a downloadable waiver note.

## The SDD artifacts

This repo is spec-driven — the artifacts the videos walk through live here:

- `.specify/memory/constitution.md` — project-wide non-negotiables
- `specs/001-equipment-reservation/` — `spec.md`, `plan.md`, `tasks.md`,
  `data-model.md`, `contracts/`, `research.md`

Changes enter through the spec: update `spec.md` first, then regenerate the
plan, tasks, and code.

## Follow along

- English channel: https://www.youtube.com/@PromptVidyaAI
- Hindi channel: https://www.youtube.com/@PromptVidyaAiHindi
