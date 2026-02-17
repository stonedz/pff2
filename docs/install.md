# Installation guide

This guide documents the supported setup paths for pff2.

## Requirements

- PHP 8.1+
- Composer
- A web server (Apache/Nginx) or Docker

## Option A: Composer-based setup

1. Create project and install dependencies.

```bash
composer install
```

2. Scaffold application skeleton and framework-managed files.

```bash
vendor/bin/init
```

3. Confirm generated files/directories:

- `app/` (application skeleton)
- `index.php` (site skeleton front controller)
- `development/compose.yaml`
- `development/nginx.conf`
- `development/PHP.Dockerfile`
- `tmp/`

4. For updates in existing projects:

```bash
vendor/bin/update
```

## Option B: Docker dev setup

Start local development stack:

```bash
docker compose -f development/compose.yaml up --build
```

Default endpoints:

- Web: `http://localhost:8081`
- Mailcatcher: `http://localhost:1080`
- MariaDB: `localhost:33061`

## Front controller and entrypoint

The canonical generated project entrypoint is based on:

- `resources/site_skeleton/index.php`

## Notes

- Composer no longer auto-creates symlinks for app/index during install.
- Run `vendor/bin/init` explicitly after install.
- `vendor/bin/update` refreshes generated `development/` Docker files (`compose.yaml`, `nginx.conf`, `PHP.Dockerfile`).
- Init/update scripts tolerate missing optional source files and skip them cleanly.
- Scripts default writable folders to `775` instead of `777`.

## Related

- For an end-to-end local validation workflow (path repository, Docker troubleshooting, mail test), see `docs/smoke-app.md`.
