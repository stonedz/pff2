# GitHub Copilot instructions (pff2)

This repository contains the **pff2** PHP MVC framework **and** an application skeleton used by downstream projects.

## What you are editing (framework vs app)
- **Framework core:** `src/` (namespace `pff\...`). This is the framework shipped to consumers.
- **Built-in modules:** `src/modules/*` (autoloaded as `pff\modules\...`).
- **Doctrine integration module:** `modules/pff2-doctrine/` is a separate package/module.
- **App skeleton:** `resources/app_skeleton/` (often surfaced as an `app/` symlink in consuming projects).
- **Site skeleton:** `resources/site_skeleton/` (front controller + docker-compose for consumers).

If a request is ambiguous, first confirm whether the change should go into the **framework** (`src/`) or the **app skeleton** (`resources/app_skeleton/`).

## Setup & key commands
### Install dependencies
- `composer install --no-interaction --no-progress --no-scripts`

Why `--no-scripts`: `composer.json` defines a `post-install-cmd` that runs `ln -s resources/site_skeleton/index.php index.php`. In this repo `index.php` already exists (it is committed as a symlink), so that script fails on a fresh clone.

If you need the app skeleton locally (for manual testing/running the app), create it manually:
- `ln -s resources/app_skeleton app` (only if `app/` does not already exist)

In downstream/consumer projects (where `index.php` does not exist yet), running Composer scripts is fine and will scaffold/symlink the skeleton files.

### Run tests (CI uses this)
- `./vendor/bin/phpunit`

CI config: `.github/workflows/php.yml` (PHP 8.3). It currently runs `composer install` without `--no-scripts`; if you hit symlink script failures locally, use `--no-scripts` as described above.

### Refactors
- `./vendor/bin/rector process` (see `rector.php`)

Caveat: `rector.php` currently uses `->withPhpSets(php70: true)` even though the package requires PHP >= 8.1. Don’t introduce syntax that breaks PHP 8.1.

## Local development (Docker)
Use the dev compose file:
- `docker compose -f development/compose.yaml up --build`

Services include nginx (port 8081), php-fpm, mariadb (33061), and mailcatcher (1080).

## Core framework conventions
### Routing
- Automatic MVC routing: `/foo/bar` → controller `Foo_Controller`, action `bar`.
- Static routing maps URLs to PHP pages under `app/pages/`.

Routing relies on filesystem checks:
- `pff\App::addRoute()` checks `CONTROLLERS/..._Controller.php`
- `pff\App::addStaticRoute()` checks `PAGES/...`

When writing tests for routing, define `CONTROLLERS` and `PAGES` constants to point at fixtures (see `tests/bootstrap.php`).

### Controllers
- Controllers extend `pff\Abs\AController`.
- Use `ServiceContainer::get('config')` / `ServiceContainer::get('dm')` (Doctrine EntityManager) rather than instantiating globally.

### Views/layouts
- Prefer plain PHP templates in `app/views/`.
- Create views/layouts via factories (`pff\Factory\FView`, `pff\Factory\FLayout`) instead of new patterns.

### Modules
- Modules extend `pff\Abs\AModule` and are described by a `module.yaml`.
- Module locations: `app/modules/<name>/`, `modules/<name>/`, `src/modules/<name>/`.

## Change hygiene
- Keep changes minimal and consistent with existing patterns (class naming like `Foo_Controller`).
- Don’t redesign the framework or introduce new global abstractions unless requested.
- Prefer adding/adjusting tests under `tests/` when you change behavior in `src/`.

## More detailed conventions
A longer, app-oriented write-up exists in the repo root: `copilot-instructions.md`.
