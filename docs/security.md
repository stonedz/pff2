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

## Security headers

`HTMLOut` sends the following headers by default on every HTML response:

| Header                   | Default                              |
|--------------------------|--------------------------------------|
| X-Content-Type-Options   | nosniff                              |
| X-Frame-Options          | DENY                                 |
| Referrer-Policy          | strict-origin-when-cross-origin      |

Override via config:

```php
$pffConfig['security_headers'] = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'SAMEORIGIN',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Content-Security-Policy' => "default-src 'self'",
];
```

Set a header to `null` to suppress it. Override per-controller:

```php
$this->_output->setHeader('X-Frame-Options', 'SAMEORIGIN');
$this->_output->removeHeader('Referrer-Policy');
```

## CSRF protection

Enable the `csrf` module in your config:

```php
$pffConfig['modules'] = ['session', 'csrf', ...];
```

When `autoValidate` is true (default), the module automatically validates
CSRF tokens on POST/PUT/PATCH/DELETE requests. Tokens are single-use.

In forms:

```php
<?= \pff\Core\ModuleManager::loadModule('csrf')->tokenField('my_form') ?>
```

For AJAX, send the token via the `X-CSRF-Token` header.

Configure excluded routes and token lifetime in `module.conf.yaml`.

## Password hashing

Recommended auth setting:

```yaml
passwordType: password_hash
```

Guidance:

- Use `password_hash()` for new passwords.
- Use `password_verify()` for validation.
- `md5` and `sha256` modes now trigger `E_USER_DEPRECATED` warnings.
- They use `hash_equals()` internally to prevent timing attacks.
- Both will be **removed** in pff2 5.0.

## Template output safety

- Use `$this->e($value)` to escape dynamic output in templates:
  - `html` (default): `htmlspecialchars` with `ENT_QUOTES | ENT_HTML5`
  - `attr`: same as html
  - `js`: `json_encode` with hex escaping
  - `url`: `rawurlencode`
- Access template data via `$this->get('key')` — `extract()` has been removed.
- Default framework error templates escape exception messages automatically.

## Input filtering

Use the safe input accessors on `App` instead of accessing superglobals directly:

```php
$this->_app->getQuery('search', '', FILTER_SANITIZE_SPECIAL_CHARS);
$this->_app->getPost('email', '', FILTER_VALIDATE_EMAIL);
$this->_app->getServer('REQUEST_METHOD');
$this->_app->getCookie('theme', 'light');
```

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
- Verify security headers with `curl -I`.
- Verify CSRF tokens are present in all state-changing forms.
