# GitHub Copilot instructions for this repo (pff2 framework)

## High-level architecture
- This project is a PHP micro-framework called **pff2**, structured around classic MVC.
- The reusable framework lives under `src/` (namespace `pff\...`); an application using it lives under `app/` (see `resources/app_skeleton/`).
- Entry points are `index.php` at the project root and `public/index.php` (for deployed apps). They bootstrap the framework, build a `pff\App`, and call `App::run()`.
- `pff\App` handles routing, loads configuration, modules, helpers, and delegates to controllers and views.
- Controllers live in `app/controllers`, models in `app/models`, views/templates in `app/views`, modules in `app/modules` or `modules/`, helpers in `app/helpers`.

## Namespaces and autoloading
- Framework code uses the `pff` namespace, e.g. `pff\App`, `pff\Config`, `pff\controllers\My_Controller`.
- Application code is normally PSR-4 autoloaded with:
  - `pff\models\` -> `app/models`
  - `pff\controllers\` -> `app/controllers`
  - `pff\services\` -> `app/services`
- When generating new app code, **always place it in the `app/` folder**, not in `src/`, and use the `pff\...` namespaces consistent with these mappings.

## Configuration
- Global config is handled by `pff\Config` ([src/Config.php](src/Config.php)) and loaded from `app/config`.
- Main app config file: `app/config/config.user.php`, which defines a `$pffConfig` array.
- In controllers or services, always retrieve the config object from the service container, e.g. `\pff\Core\ServiceContainer::get('config')`, rather than instantiating `Config` directly.
- The Doctrine Entity Manager is also retrieved from the service container via `\pff\Core\ServiceContainer::get('dm')` (this is what `AController::initORM()` uses).
- Key config entries:
  - `databaseConfig` (DB connection, Doctrine)
  - `modules` (autoloaded module names)
  - `development_environment`, `show_all_errors`, etc.
- Application-specific routes override automatic routing in `app/config/routes.php` using `App::addRoute()` and `App::addStaticRoute()`.

## Routing and controllers
- `pff\App::run()` parses the `url` parameter and resolves it to controllers/actions.
- Default automatic routing:
  - URL `/` -> controller `Index_Controller`, action `index`.
  - URL `/foo` -> controller `Foo_Controller`, action `index`.
  - URL `/foo/bar` -> controller `Foo_Controller`, action `bar`.
  - Extra segments and GET params become parameters available via `$this->getParam(...)` in controllers.
- Custom routes:
  - `App::addStaticRoute($request, $destinationPage)` maps a URL to a static PHP page under `app/pages/`.
  - `App::addRoute($request, $destinationController)` maps a URL to a specific `Controller/Action` pair.
- Controllers must extend `pff\Abs\AController` ([src/Abs/AController.php](src/Abs/AController.php)) and implement `pff\Iface\IController`.
- Controller class naming convention:
  - Namespace: `pff\controllers`.
  - File: `app/controllers/Foo_Controller.php`.
  - Class: `class Foo_Controller extends \pff\Abs\AController`.

### Controller responsibilities
- Constructor is inherited from `AController` and receives:
  - `$controllerName` (string without `_Controller` suffix)
  - `pff\App $app`
  - `$action` (default `index`)
  - `$params` (array of routing/GET parameters)
- Controllers typically:
  - Override `initController()` to do per-controller initialization.
  - Implement public action methods like `index()`, `show()`, `create()`, etc.
  - Use `$this->getParam($indexOrName, $errorMessage?, $errorCode?)` to read GET/route parameters.
  - Use helpers and modules via `$this->_app->getHelperManager()` and `$this->_app->getModuleManager()`.
  - Build one or more views and add them to the render queue with `$this->addView($view)`.
- `beforeAction()` and `afterAction()` hooks on `AController` can be overridden to run code around each action.

## Views, layouts, and templates
- All views extend `pff\Abs\AView` ([src/Abs/AView.php](src/Abs/AView.php)) and implement `pff\Iface\IRenderable`.
- Use factories to create them:
  - `\pff\Factory\FView::create('my_view.php', $app)` for a simple view.
  - `\pff\Factory\FLayout::create('layout.php', $app)` for a layout view.
- Template files live in `app/views/`:
  - Preferred: plain PHP/HTML templates: `app/views/*.php` (or subdirectories like `app/views/foo/index.php`).
  - Legacy/less preferred: Smarty templates under `app/views/smarty/templates/*.tpl` (existing ones still work, but new templates should generally be plain PHP/HTML).
- Common pattern in a controller action:
  - Create a view: `$view = \pff\Factory\FView::create('foo/index.php', $this->_app);`
  - Set template variables: `$view->set('varName', $value);`
  - Add to render queue: `$this->addView($view);`
- **Template variable access** (since 4.1):
  - Access variables via `$this->get('varName')` or `$this->get('varName', 'default')`.
  - Check existence with `$this->has('varName')`.
  - **Never use bare `$variable` in templates** — `extract()` has been removed for security.
- **Output escaping** (since 4.1):
  - Always escape dynamic output: `<?= $this->e($this->get('user_name')) ?>`.
  - Available contexts: `html` (default), `attr`, `js`, `url`.
  - Example: `<input value="<?= $this->e($this->get('query'), 'attr') ?>">`.
- Layouts:
  - Layouts are special views that aggregate one or more content views.
  - In controller code, create a layout (`FLayout::create`), add content views via `$layout->addContent($view)`, then `$this->addView($layout)`.
  - In layout templates, insert child views using `$this->content($index)` in PHP or `{content index=0}` in Smarty.
- Within templates it is possible to render another controller/action via `$this->renderAction('controller', 'action', [param0, param1])` or the equivalent Smarty tag.
- Static assets live under `app/public/` and subfolders (`css`, `img`, `js`, `files`). `AView` exposes absolute paths via `$this->get('pff_path_public')`, `$this->get('pff_path_css')`, etc.

## Modules
- Modules extend `pff\Abs\AModule` ([src/Abs/AModule.php](src/Abs/AModule.php)).
- Module configuration lives in a YAML file named `module.yaml` located in one of:
  - `app/modules/<moduleName>/module.yaml` (app-local)
  - `modules/<moduleName>/module.yaml` (Composer-installed)
  - `src/modules/<moduleName>/module.yaml` (framework built-in)
- `pff\Core\ModuleManager` ([src/Core/ModuleManager.php](src/Core/ModuleManager.php)) reads `app/config/config.user.php` entry `$pffConfig['modules']` and autoloads those modules via `ModuleManager::initModules()`.
- The YAML `module.yaml` typically contains:
  - `name`, `version`, `class` (fully qualified module class under `pff\modules\...`).
  - Optional `requires` (other modules), `requires_php_extension`, and hook-related options.
- When generating new modules:
  - Create a class under `src/modules/<ModuleName>/<ModuleClass>.php` extending `AModule`.
  - Provide an accompanying `module.yaml`.
  - Expose clear public methods to be called from controllers.

## Hooks and helpers
- Hooks are handled by `pff\Core\HookManager`; modules can implement `pff\Iface\IHookProvider` to register hook callbacks.
- Hooks include `beforeSystem`, `before`, `after`, `beforeView`, `afterView` and are used inside `App::run()` and controllers/views lifecycle.
- Helpers are simple function collections under `app/helpers`. They are loaded via `HelperManager` (e.g. `$this->_app->getHelperManager()->load('my_helper');`).
- When adding new helpers, put them in `app/helpers`, keep pure functions, and avoid side effects on framework internals.

## Error handling and environment
- `pff\App::setErrorReporting()` configures PHP error reporting based on `development_environment` and `show_all_errors` in config.

## Security

### CSRF protection
- Enable the `csrf` module in `$pffConfig['modules']` (requires `session`).
- In forms, output a hidden token field: `<?= \pff\Core\ModuleManager::loadModule('csrf')->tokenField('action_name') ?>`.
- With `autoValidate: true` (default), POST/PUT/PATCH/DELETE requests are validated automatically.
- For AJAX, send the token via the `X-CSRF-Token` header.
- Excluded routes can be configured in `module.conf.yaml`.

### Security headers
- `HTMLOut` sends `X-Content-Type-Options`, `X-Frame-Options`, and `Referrer-Policy` by default.
- Configure via `$pffConfig['security_headers']` array. Set to `null` to suppress.
- Per-controller: `$this->_output->setHeader('X-Frame-Options', 'SAMEORIGIN')`.

### Input filtering
- Use `$this->_app->getQuery()`, `$this->_app->getPost()`, `$this->_app->getServer()`, `$this->_app->getCookie()` instead of direct superglobal access.
- All methods accept an optional PHP filter constant (e.g. `FILTER_VALIDATE_EMAIL`).

### Output escaping
- Always use `$this->e($value)` in templates for user-controlled output.
- Contexts: `html` (default), `attr`, `js`, `url`.
- In production, errors are logged to `tmp/logs/error.log`.
- Some module-based error handling is provided (`exception_handler` module).

## Guidelines for AI-generated changes
- **Do NOT modify framework core in `src/`** unless explicitly requested; prefer changes in the app-level `app/` tree.
- When creating controllers:
  - Place them in `app/controllers`.
  - Use the `pff\controllers` namespace and extend `\pff\Abs\AController`.
  - Follow the `Foo_Controller` naming pattern and map URLs accordingly.
- When creating models:
  - Place them in `app/models` under namespace `pff\models`.
  - If using Doctrine ORM, rely on the configured EntityManager (`$this->_em` in controllers).
- When creating views or layouts:
  - Use factories `\pff\Factory\FView` and `\pff\Factory\FLayout`.
  - Store template files in `app/views` (or `app/views/smarty/templates` for Smarty).
- When working with configuration:
  - Only modify `app/config/config.user.php` or files under `app/config/modules/`.
  - Keep `$pffConfig` a valid PHP array and avoid syntax errors.
- When working with modules:
  - Add new module names to `$pffConfig['modules']` in `config.user.php` if they should autoload.
  - Use YAML `module.yaml` and `AModule` subclasses as described above.
- Prefer using existing factory classes (`FView`, `FLayout`), service container (`ServiceContainer::get(...)`), and managers (`ModuleManager`, `HelperManager`, `HookManager`) instead of introducing new global patterns.
- Keep generated code PSR-2/PSR-12-ish and compatible with existing namespaces and naming conventions.

## How to ask Copilot for changes
- When requesting changes from Copilot/AI, refer to these patterns explicitly, e.g.:
  - "Create a new controller `User_Controller` in `app/controllers` extending `\pff\Abs\AController` with actions `index` and `show`. Use `FView` to render `app/views/user/index.php`."
  - "Add a new Smarty template under `app/views/smarty/templates/` and wire it through a layout created with `FLayout`."
  - "Add a module configuration file under `app/config/modules/my_module/module.yaml` and register the module in `$pffConfig['modules']`."
- Mention full namespaces (`pff\controllers`, `pff\models`, `pff\modules`) so the AI keeps class names and locations consistent with the framework.
