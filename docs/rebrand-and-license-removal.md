# Infinri 1.0.0: rebrand, license removal and cleanup

Infinri 1.0.0 is the first release under the Infinri name. It removes the license activation system, renames every public identifier to its Infinri form without compatibility aliases, removes repository links and version history, and deletes all deprecated and backward-compatibility code paths.

This document records the decisions behind the release, the breaking changes for existing sites and plugins, and the maintenance steps for the repository. It only uses the current names.

## Version

- `composer.json` declares version `1.0.0`, and `App::version()` returns `1.0.0`.
- The Panel asset hash (`versionHash()`), the update status cache key and the version shown in the System view follow the new version, so these caches are rebuilt once after the upgrade.
- Docblocks and comments carry no version history: there are no `@since` tags, no versions on removed APIs and no version-bound TODO notes.

## Decisions

### No license activation

The project is distributed under the MIT license declared in `composer.json`, so all activation logic is removed: the license classes, the activation and registration dialogs, the System view license status, the plugin license hook and the plugin license display.

- **Alternative considered:** keep a license status that always reports "valid". This was rejected because it would keep dead vocabulary, i18n keys and UI around without any function.
- **Consequence for the update check:** without a license there is nothing that restricts major upgrades, so the update status always offers the newest available major version. When the update data has no entry for a newer major, the status falls back to `upgrade`.

### Renames without compatibility aliases

Every public identifier was renamed in one step, and no alias or shim maps a previous name to the new one.

- **Alternative considered:** keep aliases for one release. This was rejected because the aliases would have to carry the previous names in code, which the release removes completely.
- **Trade-off:** upgrading is a one-time breaking change for templates, controllers, plugins, configuration and stored sessions (see Breaking changes).

### Stored block location value

Image and video blocks store where their source comes from in the `location` content field. A local file is stored as `location: infinri`. Content saved with any other location value is treated as an external URL. Sites with existing content must update the stored value to `infinri` for local images and videos, either by re-saving the blocks in the Panel or by replacing the value in the content files.

### Separate Infinri test hosts

Test fixtures that need two distinct configuration hosts use `infinri.com` and `tryinfinri.com`. Keeping two hosts preserves the coverage of host-specific configuration files (`config.infinri.com.php`, `config.tryinfinri.com.php`).

### Update host

The update check reads its data from `https://infinri.com`. The update JSON must use the `infinri` constraint key in its messages.

### Translations are maintained in the repository

The translation service configuration is removed. A pull from the previous translation project would re-import upstream wording and keys that this release deletes. The 34 translation files in `i18n/translations` are now the source of truth and are edited directly.

### Gist embeds are opt-in

`Html::$gistDomains` is an allowlist of hosts for the `gist()` helper and the `(gist: ...)` tag. It now defaults to an empty array, so no third-party host is trusted by default. A site enables gist embeds by adding its host, for example in a plugin or config file:

```php
Infinri\Toolkit\Html::$gistDomains[] = 'gist.example.com';
```

### No repository links

Code, configuration and the Panel carry no repository URLs. Copyright and license notices of included third-party code are kept, with the library named instead of linked.

## Breaking changes

Every item below affects existing sites, plugins or deployments on upgrade.

### Site code and configuration

1. **Templates, snippets and controllers** receive `$infinri`, call `infinri()` or `->infinri()`, and use the global `Infinri` class alias. Controllers must declare an `$infinri` argument, because controller arguments are resolved by name.
2. **Blueprint queries** use `infinri.*`, for example `infinri.users` or `{{ infinri.url }}`.
3. **Text helpers and field methods** are `infinritext()`, `infinritextinline()`, `infinritags()` and `infinritag()`, plus the `infinritext` and `infinritextinline` field methods. The short `kt()` and `kti()` aliases are unchanged.
4. **Hooks** are `infinritext:before`, `infinritext:after`, `infinritags:before` and `infinritags:after`. A hook registered under any other name for these events never fires.
5. **Options** are `infinritext.image.*`, `infinritext.video.options`, `panel.infinritext`, the drag text callbacks `panel.infinritext.fileDragText`, `panel.infinritext.pageDragText` and `panel.infinritext.userDragText`, and `updates.infinri`. Options under any previous name are silently ignored, so site configuration must be renamed.
6. **Plugin tag classes** are `Infinri\Text\InfinriTag` and `Infinri\Text\InfinriTags`.
7. **Constants and environment variables** are `INFINRI_RENDER`, `INFINRI_HELPER_*` and `INFINRI_PHP_VERSION_CHECK`.
8. **Roots:** a custom core root in `index.php` uses the `infinri` key.
9. **Impersonation:** `impersonate('infinri')` impersonates the virtual admin user. Any other id that matches no user throws a `NotFoundException`.
10. **Site preview:** the site preview permission is read from `site.preview` only, and the Panel no longer also checks the home page preview permission.

### Sessions and Panel state

11. **One-time logout on deploy:** the session cookie is `infinri_session`, session keys use the `infinri.*` prefix, and the default cookie key is `InfinriHttpCookieKey`. Pending email challenges and password reset links also become invalid.
12. **Panel preferences reset once:** menu state, theme, preview scroll position, pagination and block expansion state are stored under new keys.

### Plugins

13. **Plugin license:** `App::plugin(..., license: ...)` and `new Plugin(license: ...)` throw an unknown named parameter error. `Plugin::toArray()` has no `license` key, and the System view shows no plugin license or license status. The raw `license` entry of a plugin's `composer.json` is still available through `Plugin::info()` but is never displayed.
14. **Plugin entry file:** the Panel always loads a plugin's `index.js`. A development entry file is no longer loaded, and `window.panel.plugins` no longer exposes the component resolver helpers.
15. **Plugin media URLs** without the asset hash are no longer served.

### Removed APIs and behavior

16. **Deprecated PHP APIs are deleted**, including:
    - the props array constructor of `Form` and the props form of `Form::for()`, plus the removed `Form`, `Field` and `Value` methods;
    - the removed `ModelWithContent` members (`readContent()`, `saveContent()`, `saveTranslation()`, `writeContent()` and others) and the compatibility methods of `Content\Translation`;
    - `Dir::inventory()` and the `$ignore = null` argument form of `Dir` and `Router`;
    - the removed `Url` and `Responder` methods and `Auth::ipHash()`;
    - the `Plugin` root and info compatibility, `InfinriTag::option()` and the `$options` argument of `InfinriTags::parse()`;
    - the removed members of `Email`, `Body`, the UUID classes, the Panel model classes, `Plugin\Asset`, `FileBlueprint`, `UserRules` and `PageActions`; the `$args` parameter of exceptions; the removed `Option` and `Options` parameters; the removed `LazyCollection` and iterator members;
    - the deprecation mechanism itself: `Helpers::deprecated()`, `Helpers::$deprecations` and the `deprecated()` helper.
17. **Configuration shapes:** the color field accepts only the current option format, favicons use only the current configuration format, and sections no longer accept the `headline` prop (use `label`).
18. **Permissions:** `Permissions::for(null)` is a `TypeError`, and undefined permission categories and actions are ignored.
19. **Namespaced class aliases are removed.** Use the real class names, for example `Infinri\Content\Field`, `Infinri\Content\Content`, `Infinri\Filesystem\Asset`, `Infinri\Filesystem\Dir`, `Infinri\Filesystem\F`, `Infinri\Filesystem\File`, `Infinri\Filesystem\Filename`, `Infinri\Filesystem\IsFile`, `Infinri\Filesystem\Mime`, `Infinri\Form\Form`, `Infinri\Option\Options`, `Infinri\Option\OptionsApi`, `Infinri\Option\OptionsQuery`, `Infinri\Plugin\Plugin`, `Infinri\Plugin\Asset`, `Infinri\Plugin\Assets`, `Infinri\Query\Query`, `Infinri\Template\Template` and `Infinri\Text\InfinriTag(s)`. The short global aliases (`page`, `field`, `str` and the others in `config/aliases.php`) are unchanged.
20. **Query:** `DefaultRunner` is the default query runner, and the `query.runner` option must name a `Runner` subclass. The previous expression classes `Infinri\Query\Expression`, `Segment`, `Segments`, `Argument` and `Arguments` are deleted.
21. **Content locks:** lock files in the previous format are ignored and no longer cleaned up, and `Lock::toArray()` reports only the current lock data.
22. **Blocks:** only the current block format is read. Older builder, editor and YAML block formats are no longer converted, and `BlockConverter` is deleted. Block snippets receive `$block`, `$content`, `$id`, `$prev` and `$next`; the `$data` variable is gone.
23. **Apply hooks** must return the model they modify. A change made without returning the model is not picked up.
24. **Media:** thumbnail folders from the previous naming scheme are no longer removed when a page is unpublished.
25. **Gist embeds** return an empty string until a host is added to `Html::$gistDomains` (see Decisions).
26. **System view:** the version row only links to the update information when the update check succeeds. The Lab has no source code buttons, and the Lab docs no longer carry a source link prop.
27. **Composer metadata:** `composer.json` lists only the support email.

### Panel

28. **Prototype shortcuts are removed.** Use the Panel object instead: `$panel.t()` (and `window.panel.t()` outside components), `$panel.api`, `$panel.dialog.open()`, `$panel.drawer.open()`, `$panel.dropdown.openAsync()`, `$panel.events`, `$panel.view.open()`, `$panel.reload()` and `$panel.url()`.
29. **Dialogs and drawers** must be opened through `$panel.dialog.open()` and `$panel.drawer.open()`. Placing dialog or drawer components directly in a template is not supported.
30. **Dialog submit button:** the dialog `theme`, `icon` and `disabled` props are removed. Configure the submit button through `submitButton`, either as a string for the text or as an object such as `{ text, icon, theme, disabled }`. Server-side dialogs use the same `submitButton` array. `k-remove-dialog` keeps the trash icon and negative theme as defaults for its submit button.
31. **Model views** no longer receive a nested `model` prop. Use the top-level view props instead, for example `$panel.view.props.uuid`, `id`, `link`, `title` and, for files, `filename`, `extension`, `mime`, `type` and `url`. The Panel model classes no longer override `dropdownOption()`, and `Panel\Model::content()` is removed (use the `versions` prop).
32. **Removed components:** `k-writer` (use `k-writer-input`), `k-calendar` (use `k-calendar-input`), `k-times` (use `k-timeoptions-input`), `k-bubble`, `k-bubbles` and `k-bubbles-field-preview`.
33. **Other Panel removals:** deprecated component props and methods, deprecated CSS custom properties, the previous alignment utility classes and `window.panel.deprecated()`.
34. **Directory resolution:** `Dir::realpath($path, $parent)` now throws when `$path` resolves to `$parent` itself; only paths strictly inside the parent are accepted.

## Maintenance

### Autoloader and namespace check

After adding, removing or renaming PHP classes, regenerate the optimized autoloader and run the namespace check:

```sh
composer dump-autoload --optimize --no-plugins --no-scripts
php scripts/check-namespace.php
```

The check loads every application class from the classmap, exercises the short global aliases and renders a content field. It prints "Namespace check passed" on success. Deploy the application together with its regenerated autoloader, and restart PHP-FPM after switching releases if OPcache does not revalidate timestamps.

### Panel build

The committed `panel/dist` must be rebuilt after any change to `panel/src`, `panel/lab` or `panel/public`:

```sh
cd panel
npm ci
npm run test:unit
npm run lint
npm run build
```

`npm run build` also regenerates the component documentation in `panel/dist/ui`; `npm run build:docs` regenerates only the documentation. The documentation generator does not remove files, so delete the JSON file of any deleted component from `panel/dist/ui` by hand.

### Known test environment results

- Two locale-dependent tests expect the `de_DE.UTF-8` locale and fail where it is not installed.
- Some tests attempt to create directories beneath the `/dev/null` test root and emit a warning.
- The database tests need the `pdo_sqlite` extension, and the image, cache and session tests for Imagick, Redis and Memcached need those extensions or services. Without them, these tests fail for environment reasons only.
