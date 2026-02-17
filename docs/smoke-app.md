# Smoke app guide

This guide provides a complete, repeatable workflow to create and validate a pff2 smoke application from scratch, using a local framework checkout.

## Goals

- Validate that framework changes are consumable by a brand-new app.
- Verify scaffold scripts (`vendor/bin/init`, `vendor/bin/update`).
- Verify Docker development stack startup.
- Verify runtime basics (routing/controller loading).
- Verify mail module delivery with Mailcatcher.

## Prerequisites

- PHP 8.1+
- Composer
- Docker + Docker Compose plugin
- Local framework checkout (example path used in this guide):
  - `/home/stonedz/work/pff2`

## 1) Create the smoke app

```bash
mkdir -p ~/work/pff2-smoke
cd ~/work/pff2-smoke
```

Create `composer.json`:

```json
{
  "name": "stonedz/pff2-smoke",
  "require": {
    "stonedz/pff2": "4.0.x-dev"
  },
  "repositories": [
    {
      "name": "pff2",
      "type": "path",
      "url": "/home/stonedz/work/pff2",
      "options": {
        "symlink": false
      }
    }
  ],
  "autoload": {
    "psr-4": {
      "pff\\controllers\\": "app/controllers",
      "pff\\models\\": "app/models",
      "pff\\services\\": "app/services"
    }
  }
}
```

Install dependencies:

```bash
composer update
composer dump-autoload -o
```

Why `4.0.x-dev` and not `dev-4.0`?

- Composer resolves this branch as `4.0.x-dev`.

Why `"symlink": false`?

- Ensures `vendor/stonedz/pff2` is copied, not symlinked.
- Avoids host-path symlink issues in Docker containers.

## 2) Scaffold the app files

```bash
vendor/bin/init
```

Expected generated files/folders include:

- `app/`
- `index.php`
- `development/compose.yaml`
- `development/nginx.conf`
- `development/PHP.Dockerfile`
- `tmp/`

## 3) Keep scaffolded files in sync with framework changes

When you modify the framework checkout and want to refresh generated files in the smoke app:

```bash
composer update stonedz/pff2 --prefer-source
vendor/bin/update
composer dump-autoload -o
```

Notes:

- `vendor/bin/update` refreshes generated app entrypoint and Docker dev files.
- Init/update scripts tolerate missing optional files and skip cleanly.

## 4) Start the Docker stack (safe sequence)

From smoke app root:

```bash
docker compose -f development/compose.yaml down --remove-orphans
docker compose -f development/compose.yaml up -d --build --force-recreate --remove-orphans
docker compose -f development/compose.yaml ps
```

View logs if needed:

```bash
docker compose -f development/compose.yaml logs --tail=100
```

Endpoints:

- App: `http://localhost:8081`
- Mailcatcher: `http://localhost:1080`

## 5) Mail module smoke test

Add `mail` to modules list in `app/config/config.user.php` if not already present.

Create `app/controllers/MailTest_Controller.php`:

```php
<?php

namespace pff\controllers;

use pff\Abs\AController;
use pff\Core\ModuleManager;

class MailTest_Controller extends AController
{
    public function index()
    {
        echo 'Use /mailtest/send to send a test email';
    }

    public function send()
    {
        $mailer = ModuleManager::loadModule('mail');

        $ok = $mailer->sendMail(
            'you@example.com',
            'noreply@example.com',
            'PFF2 Smoke Test',
            'Mail test from pff2',
            '<h1>It works</h1><p>Mail module test from smoke app.</p>'
        );

        echo $ok ? 'Email sent successfully' : 'Email send failed';
    }
}
```

Then run:

```bash
composer dump-autoload -o
docker compose -f development/compose.yaml restart php web
```

Test URLs:

- `http://localhost:8081/mailtest`
- `http://localhost:8081/mailtest/send`

Check message delivery in Mailcatcher:

- `http://localhost:1080`

## 6) Common issues and fixes

### A) `unable to evaluate symlinks in Dockerfile path ... development/PHP.Dockerfile`

Cause:

- `development/PHP.Dockerfile` missing in smoke app.

Fix:

```bash
vendor/bin/update
```

If needed, manual fallback:

```bash
cp vendor/stonedz/pff2/development/PHP.Dockerfile development/PHP.Dockerfile
```

### B) `Failed opening required '/app/../../bootstrap.php'`

Cause:

- Outdated generated `index.php` in smoke app.

Fix:

```bash
vendor/bin/update
```

If needed, force-copy latest site skeleton:

```bash
cp vendor/stonedz/pff2/resources/site_skeleton/index.php ./index.php
```

### C) `Class "\\pff\\controllers\\Index_Controller" not found`

Cause:

- Smoke app lacks root autoload mapping for app namespaces.

Fix:

- Add `autoload.psr-4` mappings in smoke app `composer.json`:
  - `pff\\controllers\\` -> `app/controllers`
  - `pff\\models\\` -> `app/models`
  - `pff\\services\\` -> `app/services`

Then run:

```bash
composer dump-autoload -o
```

### D) Docker error: `network ... not found`

Cause:

- Stale/broken Docker network state.

Fix:

```bash
docker compose -f development/compose.yaml down --remove-orphans
docker network prune -f
docker compose -f development/compose.yaml up -d --build --force-recreate --remove-orphans
```

If still broken, restart Docker daemon and retry.

## 7) Verification checklist

- Smoke app loads at `http://localhost:8081`.
- No bootstrap path fatal error.
- Controllers resolve correctly (for example `/mailtest`).
- Mail send endpoint works and message appears in Mailcatcher.
- `vendor/bin/update` refreshes generated files without noisy copy errors.
