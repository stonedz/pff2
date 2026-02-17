# Security baseline

This document defines recommended defaults for pff2 applications.

## Environment and error handling

For production:

```php
$pffConfig['development_environment'] = false;
$pffConfig['show_all_errors'] = false;
$pffConfig['show_exception_details'] = false;
```

Why:

- Prevents stack traces and sensitive internals from being shown to end users.
- Keeps logs server-side.

## Session and cookie defaults

Recommended:

```php
$pffConfig['security_cookie_httponly'] = true;
$pffConfig['security_cookie_samesite'] = 'Lax';
$pffConfig['security_cookie_secure'] = null; // auto-detect HTTPS/proxy headers
$pffConfig['security_session_strict_mode'] = true;
```

Behavior:

- Session module enforces cookie-based sessions and strict mode.
- Cookies and session cookies use secure attributes based on config and HTTPS detection.

## Password hashing

Recommended auth setting:

```yaml
passwordType: password_hash
```

Guidance:

- Use `password_hash()` for new passwords.
- Use `password_verify()` for validation.
- Treat `md5` / `sha256` as temporary compatibility modes only.

## Template output safety

- Always escape dynamic output in views (PHP or Smarty) with contextual escaping.
- Default framework error templates now escape exception messages.

## File permissions

- Use least privilege for writable directories.
- Framework scripts now target `775` by default for generated writable directories.
- Avoid `777` in production.

## Operational checks

Before production deploy:

- Verify HTTPS termination and proxy headers.
- Verify exception pages do not leak stack traces.
- Verify session cookie flags in browser dev tools.
- Verify auth login and rehash paths in staging.
