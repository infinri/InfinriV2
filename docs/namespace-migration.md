# PHP namespace migration

The application namespace is now `Infinri\`. This change is a technical refactor of the existing implementation, not an independent replacement of it or a licensing change.

## Changed

- Application namespace declarations, imports, type references and class-name strings.
- Configured API models, controllers, sections, templates and runtime aliases.
- Test namespaces, fixtures, mocks, the test autoloader and PHPUnit extension configuration.
- Namespaced class examples in the Panel sources.
- Tracked Composer autoload output, regenerated with plugins and scripts disabled.

The root PSR-4 mapping already used `Infinri\` before this change. The source now matches it.

## Compatibility

This is a breaking namespace change. External plugins, templates and host application entry points importing `Kirby\...` must change those imports to `Infinri\...`. There is no automatic old-namespace compatibility bridge. Short global helpers such as `kirby()`, the `kirby` class alias, option names, filesystem roots and class basenames such as `KirbyTag` are intentionally unchanged in this step. Renaming those APIs needs a separate compatibility review.

Third-party package identifiers and namespaces, including the Composer installer dependency's metadata, remain unchanged. Existing licensing metadata, activation behavior, author/copyright notices and branding are not changed by this PR. This migration does not establish permission to publish the inherited implementation under another license.

## Verification

Run from the repository root:

```sh
composer dump-autoload --optimize --no-plugins --no-scripts
php scripts/check-namespace.php
php /path/to/phpunit.phar tests/Cms/App tests/Panel tests/Content tests/Uuid
```

Deploy the application and its regenerated autoloader together. Restart PHP-FPM after switching releases if OPcache does not revalidate timestamps. Test installed plugins and host entry points in staging before production. This repository currently contains the CMS engine, not the production Infinri website or its content.

### Results for this migration

- Local PHP 8.3.6 and PHPUnit 11.5.57.
- 1,214 changed/new PHP files passed syntax checks.
- All 412 application classes in the generated classmap loaded; dynamic aliases and content fields passed the integration check.
- 80 focused application/Panel tests passed with 278 assertions.
- 1,624 broader application, Panel, content and UUID tests produced 4,613 assertions. Two locale-dependent failures and one warning category reproduce exactly on the original commit. The locale failures expect `de_DE.UTF-8`, which is unavailable locally; the warning concerns attempts to create directories beneath the existing `/dev/null` test root. No new failure/warning diagnostics were introduced.
- Composer strict PSR autoload generation passed. Composer validation has no lock mismatch after refreshing lock metadata; its existing exact-version recommendations remain. Locked dependency versions did not change.
- No browser session, production deployment or PHP 8.4 validation was performed.

## Local branding follow-up

The initial and fallback admin titles now use Infinri Admin. Browser/PHP error-page presentation and non-license interface/login-email branding in 34 translations use Infinri. The Panel lockfile's root package name now matches the existing package manifest (`infinri`). Translation keys and substitution placeholders are unchanged. No JavaScript or CSS bundle rebuilding is required for these changes.

This does not remove every upstream name. The remaining public helpers, method/option names, file paths and class basenames need separate coordinated API migrations. Upstream credits, license messages, documentation/support URLs and third-party dependency identifiers are deliberately not rewritten as Infinri-owned material.
