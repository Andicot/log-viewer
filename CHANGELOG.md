# Changelog

## 13.0.0 — 2026-11-15

First release of the `andicot/log-viewer` fork, based on ARCANEDEV/LogViewer 11.0.1.

- Support Laravel 13 and PHP 8.3 or newer.
- Replace `arcanedev/support` with Laravel's native service providers, container, routing and console APIs.
- Preserve the original interface, clickable severity counters, themes, configuration, route names, helper functions and resource publishing tags.
- Fix the Bootstrap 5 mobile navigation toggle.
- Recognize daily logging drivers inside stacks, including nested stacks and custom channel names, in `log-viewer:check`.
- Update the test suite to PHPUnit 12 and add compatibility regression tests.

The original in-memory parser is unchanged. This package cannot be installed alongside `arcanedev/log-viewer`, as both expose the same PHP classes and helper functions.
