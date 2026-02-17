# Architecture overview

This document summarizes pff2 core runtime flow and responsibilities.

## Runtime flow

1. Front controller (`index.php` generated from site skeleton) defines core paths and URL.
2. `bootstrap.php` loads Composer autoload + shared bootstrap.
3. `shared.php` initializes DI/services and loads configured modules.
4. `App::run()` resolves route/controller/action and executes hooks.
5. Controller builds views/layouts and response is rendered.

## Core components

- `src/App.php`
  - Routing resolution
  - Controller/action execution
  - Hook lifecycle integration
- `src/Config.php`
  - Loads `app/config/config.user.php`
- `src/Core/ServiceContainer.php`
  - Pimple-backed service container
- `src/Core/ModuleManager.php`
  - Module load and dependency resolution
- `src/Core/HookManager.php`
  - Before/after/system/view hook orchestration

## Routing model

- URL is read from `$_GET['url']`.
- Supports:
  - static routes (`addStaticRoute`)
  - explicit MVC routes (`addRoute`)
  - convention-based controller/action fallback

## View/layout model

- Controllers extend `AController` and queue renderable views.
- Views are typically created via `FView`; layouts via `FLayout`.
- PHP views are the recommended default path.

## Modules

- Modules are loaded from config key `$pffConfig['modules']`.
- Module metadata lives in `module.yaml`.
- Modules can register hooks and expose helper methods to controllers.

## Security-sensitive areas

- Error/exception rendering
- Session and cookie handling
- Auth password checking strategy
- Template output escaping

See also:

- `docs/security.md`
- `docs/install.md`
- `docs/upgrade-4.x.md`
