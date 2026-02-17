# Upgrade guide — pff2 4.1 (Security Hardening)

This release addresses several security vulnerabilities and introduces new
protective features. Some changes are **breaking** — read each section and
apply the migration steps that affect your project.

---

## 1) Template variable access — `extract()` removed (BREAKING)

### What changed

`ViewPHP::render()` and `ViewPHP::renderHtml()` no longer call `extract()`.
Template variables set via `$view->set('name', $value)` are **no longer**
available as bare `$name` variables inside templates.

### New API

| Old pattern (removed)         | New pattern                        |
|-------------------------------|------------------------------------|
| `<?= $name ?>`               | `<?= $this->get('name') ?>`       |
| `<?= $title ?>`              | `<?= $this->e($this->get('title')) ?>` |
| `isset($name)`               | `$this->has('name')`               |

**`$this->get(key, default)`** — returns the value or a default.
**`$this->has(key)`** — checks existence.
**`$this->e(value, context)`** — HTML-escapes the value (see section 2).

### Migration

Search your `app/views/` templates for bare PHP variables set via controllers
and replace them with `$this->get('varName')`:

```bash
# Find likely affected templates
grep -rn '\$\w' app/views/ --include='*.php' | grep -v '\$this->'
```

---

## 2) Output escaping helpers — new `e()` / `escape()` methods

### What changed

All view classes (`ViewPHP`, `LayoutPHP`, `ViewSmarty`, etc.) now inherit
`e()` and `escape()` from `AView`.

### Usage

```php
<?= $this->e($this->get('user_name')) ?>                    <!-- HTML context (default) -->
<input value="<?= $this->e($this->get('query'), 'attr') ?>">  <!-- attribute context -->
<script>var q = <?= $this->e($this->get('query'), 'js') ?>;</script>  <!-- JS context -->
<a href="/search?q=<?= $this->e($this->get('query'), 'url') ?>">    <!-- URL context -->
```

Supported contexts: `html` (default), `attr`, `js`, `url`.

### Migration

Audit existing templates for unescaped output and wrap dynamic values with
`$this->e(...)`. This is especially important for user-controlled data.

---

## 3) Security headers — new defaults in HTMLOut

### What changed

`HTMLOut::outputHeader()` now sends security headers with every HTML response:

| Header                     | Default value                        |
|----------------------------|--------------------------------------|
| `X-Content-Type-Options`   | `nosniff`                           |
| `X-Frame-Options`          | `DENY`                               |
| `Referrer-Policy`          | `strict-origin-when-cross-origin`    |

### Configuration

Add to `config.user.php`:

```php
$pffConfig['security_headers'] = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'SAMEORIGIN',           // allow same-origin framing
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    // 'Content-Security-Policy' => "default-src 'self'",
];
```

Set a header to `null` to suppress it entirely.

### Per-controller override

```php
$this->_output->setHeader('X-Frame-Options', 'SAMEORIGIN');
$this->_output->removeHeader('Referrer-Policy');
```

---

## 4) CSRF protection module — new opt-in module

### What changed

A new `csrf` module provides anti-CSRF token generation and validation.

### Setup

1. Add `'csrf'` to your modules list in `config.user.php`:

```php
$pffConfig['modules'] = [
    'session',
    'csrf',
    // ...
];
```

2. (Optional) Create `app/config/modules/csrf/module.conf.yaml` to customise:

```yaml
moduleConf:
  autoValidate: true
  tokenLifetime: 3600
  fieldName: _csrf_token
  headerName: X-CSRF-Token
  excludedRoutes:
    - api/webhook
```

### Usage in templates

```php
<form method="POST" action="/submit">
    <?= \pff\Core\ModuleManager::loadModule('csrf')->tokenField('my_form') ?>
    <!-- ... -->
</form>
```

### Usage in controllers (manual validation)

```php
$csrf = \pff\Core\ModuleManager::loadModule('csrf');
$csrf->validateToken('my_form', $_POST['_csrf_token']); // throws CsrfException on failure
```

### AJAX requests

Send the token via the `X-CSRF-Token` header. Retrieve it with:

```php
$csrf = \pff\Core\ModuleManager::loadModule('csrf');
$token = $csrf->getToken('my_action');
```

---

## 5) Password hashing — MD5/SHA256 now trigger deprecation warnings

### What changed

`Md5PasswordChecker` and `Sha256PasswordChecker` now:

- Use `hash_equals()` instead of `==` (fixes timing attack vulnerability).
- Emit `E_USER_DEPRECATED` warnings on every use.
- Will be **removed** in pff2 5.0.

### Migration

1. Keep legacy mode temporarily.
2. On successful login, re-hash with `password_hash()`.
3. Flip config to `passwordType: password_hash`.
4. Remove legacy hash columns from your data model.

---

## 6) Input filtering — new safe accessors on App

### What changed

`App` now provides filtered access to superglobals:

```php
$this->_app->getQuery('page', 1, FILTER_VALIDATE_INT);
$this->_app->getPost('email', '', FILTER_VALIDATE_EMAIL);
$this->_app->getServer('REQUEST_METHOD');
$this->_app->getCookie('theme', 'dark');
```

All methods accept a PHP filter constant as third argument.

### Migration

Optional but recommended: replace direct `$_GET`/`$_POST` access in
controllers with the new methods for consistent sanitization.

---

## 7) Legacy code deprecated

`App::stripSlashesDeep()` and `App::unregisterGlobals()` are now marked
`@deprecated` and will be removed in 5.0. They are no-ops since PHP 5.4.

---

## Checklist

- [ ] Update templates to use `$this->get()` instead of bare variables
- [ ] Add `$this->e()` escaping around user-controlled output
- [ ] Verify security headers in browser dev tools
- [ ] Enable CSRF module for forms
- [ ] Migrate away from MD5/SHA256 passwords
- [ ] Review `config.user.php` security settings
- [ ] Run full test suite: `vendor/bin/phpunit`
