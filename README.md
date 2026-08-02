# WorkshopHub — SDD Demo App

The working companion app for the **SDD Masterclass** and the **Spec Kit Hands-On** videos on [Prompt Vidya AI](https://www.youtube.com/@PromptVidyaAI). Everything walked through in the course — the setup wizard, the availability engine, the public booking flow, the studio dashboard, and the Spec Kit–built **equipment reservation** feature — runs in this repo.

## Stack

- Laravel monolith, server-rendered Blade pages
- MySQL-ready (`docker-compose.yml`), SQLite for the local quickstart
- Native JavaScript — no frontend frameworks, no CDN dependencies
- Five theme CSS files in `public/assets/css/themes/`: **Studio · Garden · Chalk · Night · Paper**

## Setup — step by step (Windows, macOS, Linux)

You need **PHP 8.3+** (with the SQLite extension) and **Composer**. No Docker, no Node needed.

### 1 · Get the code

```bash
git clone https://github.com/viveksingh98/workshophub-sdd.git
cd workshophub-sdd
```

(or use the green **Code → Download ZIP** button on GitHub and unzip it)

### 2 · Install PHP + Composer

**Windows**

1. Install [Scoop](https://scoop.sh) (open PowerShell and run the command from their homepage)
2. `scoop install php composer`
3. Enable SQLite: open the `php.ini` in your PHP folder and make sure these lines are uncommented (no leading `;`): `extension=pdo_sqlite` and `extension=sqlite3`

**macOS**

1. Install [Homebrew](https://brew.sh) if you don't have it
2. `brew install php composer` (SQLite is already included)

**Linux (Ubuntu/Debian)**

```bash
sudo apt update
sudo apt install php php-cli php-sqlite3 php-xml php-mbstring php-curl composer unzip
```

Check both work: `php -v` and `composer -V`.

### 3 · Install and run the app

Same commands on every OS (Windows users: run them in PowerShell; use `copy` instead of `cp`):

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Open http://127.0.0.1:8000 — done. The seeded demo data makes every screen presentable on first boot.

**Demo owner login** (at `/studio-access` — all three fields must match):

| Field | Value |
|---|---|
| Email | `hello@workshophub.local` |
| Phone | `9876543210` |
| Password | `workshop123` |

Want the full **setup wizard** experience instead? Run `php artisan migrate:fresh` (without `--seed`) and open the site — the wizard takes you from empty database to live site: owner account → public details → class types & pricing → theme.

## Run the tests

```bash
php artisan test
```

## What's inside — the finished walkthrough

**Public site**
- Homepage: hero, class cards with category filter, meet-the-studio, FAQ accordion, footer WhatsApp / call / email buttons
- **Booking flow**: mode selector (in-studio / online) → the calendar only offers days the owner opened → free slots computed from durations + break-between-sessions → name + phone (cleaned automatically, used as the student identifier) → security question + rate limiting → confirmation with an **Add to Google Calendar** button
- **Blog**: paginated list with category filter and full article pages
- **Equipment reservation** (the Spec Kit videos' feature): time-window reservations with a transaction + row-lock overlap guard, creator-only cancellation

**Studio dashboard** (`/studio-dashboard`, every URL behind auth; login at `/studio-access`)
- Home: upcoming bookings, students, articles, session records + today's schedule
- **Availability**: durations (in-studio / online), break between sessions, advance-days window, day start/end, the weekly grid, **holiday mode**, and **holiday periods** that block public dates instantly
- **Bookings**: filterable list, status change, reschedule, one-tap **WhatsApp reminder**, manual booking with **student search-autofill**, optional Gmail email notification
- **Calendar**: monthly / weekly / daily views + personal events that never consume booking slots
- **Students**: auto-created profiles (phone as identifier), archive, session records with rich text + PDF/photo uploads, **filled waiver PDF** per student
- **Blog**: WYSIWYG editor (native), cover image, category, draft/publish
- **FAQs** management (renders as the public accordion)
- **Web management**: theme preview + activate (5 themes), image overrides (hero / logo / favicon), public phrases, contact + social links, Gmail notification setup, waiver template with variables
- Quality of life: **light/dark toggle** (persists), **global search** across students / bookings / posts / records / FAQs, and a **Help** section

## The SDD artifacts

This repo is spec-driven — the artifacts the videos walk through live here:

- `.specify/memory/constitution.md` — project-wide non-negotiables
- `specs/001-equipment-reservation/` — `spec.md`, `plan.md`, `tasks.md`, `data-model.md`, `contracts/`, `research.md`

Changes enter through the spec: update `spec.md` first, then regenerate the plan, tasks, and code.

## Follow along

- English channel: https://www.youtube.com/@PromptVidyaAI
- Hindi channel: https://www.youtube.com/@PromptVidyaAiHindi
