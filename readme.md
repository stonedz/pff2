# Pff2 MVC PHP framework

![PHP Composer](https://github.com/stonedz/pff2/actions/workflows/php.yml/badge.svg?branch=master)
[![Latest Stable Version](https://poser.pugx.org/stonedz/pff2/v/stable.svg)](https://packagist.org/packages/stonedz/pff2)
[![License](https://poser.pugx.org/stonedz/pff2/license.svg)](https://packagist.org/packages/stonedz/pff2)

Pff2 is a simple PHP MVC framework (core in `src/`) and includes an application/site skeleton under `resources/`.

## Requirements

- PHP >= 8.1
- Composer (v2 recommended)

## Create a new project (consumer app)

1. Create a new directory and add a `composer.json` similar to:

```json
{
    "name": "company/project-name",
    "description": "",
    "license": "proprietary",
    "require": {
        "stonedz/pff2": "^2"
    },
    "autoload": {
        "psr-4": {
            "pff\\models\\": "app/models",
            "pff\\controllers\\": "app/controllers",
            "pff\\services\\": "app/services"
        }
    }
}
```

2. Install dependencies:

```bash
composer install
```

3. Scaffold the app skeleton:

```bash
./vendor/bin/init
```

This will create/copy (among other things): `app/`, `tmp/`, `app/proxies/`, `.htaccess`, `index.php`, `cli-config.php`, and a `docker-compose.yml` from the shipped skeleton.

### Scaffolding / overwrites

- `./vendor/bin/init` and `./vendor/bin/update` both support `-f` to overwrite files they scaffold/copy.
- Without `-f`, existing files are kept and will be skipped.

### CLI

The framework ships a Symfony Console CLI:

```bash
./vendor/bin/pff list
```

It includes built-in commands (see `src/Commands`) and can also load commands from modules that expose a `commands/` directory.

### Optional: Doctrine integration

Doctrine is provided as a separate module package: `stonedz/pff2-doctrine` (see `modules/pff2-doctrine/`).

If you add it to your project, it requires the Composer plugin `stonedz/pff2-installers` (because the module package declares it as a dependency and must be allowed by Composer).

In your app `composer.json`, you’ll typically need something like:

```json
{
    "require": {
        "stonedz/pff2-doctrine": "^4",
        "stonedz/pff2-installers": "*"
    },
    "config": {
        "allow-plugins": {
            "stonedz/pff2-installers": true
        }
    }
}
```

## Routing (overview)

- Default MVC routing maps `/foo/bar` to controller `Foo_Controller` and action `bar` (see `pff\App::run()` in `src/App.php`).
- You can define custom MVC routes via `addRoute($request, $destinationController)`.
- You can map static routes to files under `app/pages/` via `addStaticRoute($request, $destinationPage)`.

## Working on this repository (framework development)

This repo includes a `post-install-cmd` that creates symlinks:

- `app` → `resources/app_skeleton`
- `index.php` → `resources/site_skeleton/index.php`

Because `index.php` is already present in this repository, running Composer scripts can fail on a fresh clone.

Recommended install for contributors:

```bash
composer install --no-interaction --no-progress --no-scripts
```

Run tests:

```bash
./vendor/bin/phpunit
```

### Docker (repo development)

The repo includes a dev compose file at `development/compose.yaml`:

```bash
docker compose -f development/compose.yaml up --build
```

- App: http://localhost:8081/
- Mailcatcher: http://localhost:1080/
- MariaDB: `127.0.0.1:33061` (default creds in the compose file)

## Docs

Wiki: https://github.com/stonedz/pff2/wiki
