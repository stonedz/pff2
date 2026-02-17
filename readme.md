**Pff2 MVC PHP framework**
==============================

[![Build Status](https://app.travis-ci.com/stonedz/pff2.svg?branch=master)](https://travis-ci.org/stonedz/pff2)
[![Coverage Status](https://img.shields.io/coveralls/stonedz/pff2.svg)](https://coveralls.io/r/stonedz/pff2?branch=master)
[![Latest Stable Version](https://poser.pugx.org/stonedz/pff2/v/stable.svg)](https://packagist.org/packages/stonedz/pff2)
[![License](https://poser.pugx.org/stonedz/pff2/license.svg)](https://packagist.org/packages/stonedz/pff2)

Pff2 is a lightweight MVC framework for PHP 8.1+.

## Quick start (Composer)

1. Create a new project directory and add `stonedz/pff2` to `composer.json`.
2. Install dependencies:

```bash
composer install
```

3. Scaffold app files:

```bash
vendor/bin/init
```

4. Your project entry point is generated from `resources/site_skeleton/index.php`.

Generated development files include:

- `development/compose.yaml`
- `development/nginx.conf`
- `development/PHP.Dockerfile`

## Quick start (Docker)

The canonical development compose file is:

`development/compose.yaml`

Run:

```bash
docker compose -f development/compose.yaml up --build
```

Default service endpoints from this setup:

- Web: `http://localhost:8081`
- Mailcatcher UI: `http://localhost:1080`
- MariaDB: `localhost:33061`

## Installation notes

- Composer install no longer auto-creates app symlinks; run `vendor/bin/init` explicitly.
- `vendor/bin/update` refreshes framework-controlled generated files (for existing projects), including Docker dev files in `development/`.
- Generated writable folders now default to `775` permissions (instead of `777`).
- `vendor/bin/init` and `vendor/bin/update` skip optional copies when source files are not present, avoiding noisy `cp: cannot stat` errors.

## Security defaults

Pff2 now uses safer defaults in the app skeleton:

- Exception details are configurable via `show_exception_details` and should be disabled in production.
- Session/cookie hardening options are available in `app/config/config.user.php`:
  - `security_cookie_httponly`
  - `security_cookie_samesite`
  - `security_cookie_secure`
  - `security_session_strict_mode`
- Auth module default hashing is `password_hash` / `password_verify` (legacy `md5`/`sha256` remain deprecated compatibility paths).

## Upgrade notes

- Mail module has migrated from SwiftMailer to Symfony Mailer.
  - `swiftmailer/swiftmailer` is removed from dependencies.
  - Existing `mail/module.conf.yaml` keys (`Type`, `Host`, `Port`, `Username`, `Password`, `Encryption`) continue to work.
  - `sendMail(...)` API remains available and returns `true` on success, `false` on transport failure.
- If your app still uses auth `passwordType: md5` or `sha256`, plan migration to `password_hash`.

## Notes on templates

PHP views are the recommended template path for new apps. Smarty templates are considered legacy/best-effort support.

## Documentation

- [Installation guide](docs/install.md)
- [Smoke app guide](docs/smoke-app.md)
- [Security baseline](docs/security.md)
- [Architecture overview](docs/architecture.md)
- [Upgrade guide](docs/upgrade-4.x.md)

**Additional details:** [Wiki](https://github.com/stonedz/pff2/wiki)
