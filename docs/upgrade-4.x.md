# Upgrade guide (4.x hardening and setup refresh)

This guide covers migration steps for projects upgrading to the current pff2 core changes.

## Summary of changes

- Mail module migrated from SwiftMailer to Symfony Mailer.
- Auth module default password checker is now `password_hash` / `password_verify`.
- Session and cookie handling now default to safer settings.
- Exception handler now hides details by default in production unless explicitly enabled.
- Project scaffolding (`vendor/bin/init`, `vendor/bin/update`) uses safer permission defaults and updated dev files.

## 1) Mail module migration

### What changed

- Dependency changed from `swiftmailer/swiftmailer` to `symfony/mailer`.
- `Mail::sendMail(...)` remains available with the same signature.
- Existing config keys continue to work:
  - `Type` (`smtp`, `sendmail`, `mail`)
  - `Host`, `Port`, `Username`, `Password`, `Encryption`

### What you need to do

1. Update dependencies:

```bash
composer update
```

2. Verify mail config in `app/config/modules/mail/module.conf.yaml`:

```yaml
moduleConf:
  Type: smtp
  Host: mailcatcher
  Port: 1025
  Encryption:
  Username:
  Password:
```

3. Run a smoke test using your app path that triggers an email send.

## 2) Auth hash migration

### What changed

- Default `passwordType` is now `password_hash`.
- Legacy `md5`/`sha256` paths still work for backward compatibility, but are deprecated.

### Recommended migration strategy

1. Keep current legacy mode temporarily (`md5` or `sha256`) to avoid lockouts.
2. On successful login, re-hash user passwords with `password_hash($plainPassword, PASSWORD_DEFAULT)` and store the new hash.
3. Flip config to:

```yaml
passwordType: password_hash
```

4. After all active users have migrated, remove legacy hashes from your data model/workflow.

## 3) Exception and security config

Verify `app/config/config.user.php` contains explicit values suitable for your environment:

```php
$pffConfig['development_environment'] = false;
$pffConfig['show_all_errors'] = false;
$pffConfig['show_exception_details'] = false;

$pffConfig['security_cookie_httponly'] = true;
$pffConfig['security_cookie_samesite'] = 'Lax';
$pffConfig['security_cookie_secure'] = null; // auto-detect https/proxy
$pffConfig['security_session_strict_mode'] = true;
```

## 4) Scaffolding behavior update

- Use explicit scaffolding instead of implicit composer symlink side-effects:

```bash
vendor/bin/init
```

- To refresh generated framework files in existing projects:

```bash
vendor/bin/update
```

- Generated/updated Docker development files are:
  - `development/compose.yaml`
  - `development/nginx.conf`
  - `development/PHP.Dockerfile`

- Init/update scripts now skip optional missing source files gracefully (no hard failure/noisy `cp: cannot stat` output).

## 5) Deployment checklist

- Ensure TLS is enabled in production.
- Ensure app is configured with `development_environment=false` and `show_exception_details=false`.
- Verify writable folders are group-writable only as needed (`775` by default from scripts).
- Validate login and email delivery in staging before production rollout.
