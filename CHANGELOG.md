# Changelog

## 13.1.0 — 2026-11-15

- Cache dashboard/list counters per file using content fingerprints, including same-size rewrite invalidation. Cache contains no log messages.
- Add opt-in read-only mode across the standard HTTP, PHP and console deletion paths; keep reading and downloads available.
- Add accessible copy-details actions with plain-text output, duplicate-click protection and selectable text when clipboard access fails.
- Bundle the existing frontend resources for all three themes, with local font URLs, MIME types, conditional HTTP caching, an asset allowlist and third-party licenses.
- Preserve severity links, filters, stack highlighting and existing route/configuration names.

Published custom views/translations require manual integration to receive frontend changes; see README. The original detail parser remains in memory.

## 13.0.0 — 2026-11-15

First release of the `andicot/log-viewer` fork, based on ARCANEDEV/LogViewer 11.0.1.

- Support Laravel 13 and PHP 8.3 or newer.
- Replace `arcanedev/support` with Laravel's native service providers, container, routing and console APIs.
- Preserve the original interface, clickable severity counters, themes, configuration, route names, helper functions and resource publishing tags.
- Fix the Bootstrap 5 mobile navigation toggle.
- Recognize daily logging drivers inside stacks, including nested stacks and custom channel names, in `log-viewer:check`.
- Update the test suite to PHPUnit 12 and add compatibility regression tests.

The original in-memory parser is unchanged. This package cannot be installed alongside `arcanedev/log-viewer`, as both expose the same PHP classes and helper functions.
