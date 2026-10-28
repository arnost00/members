These standalone PHP checks are for development and CI only and must not be
deployed into the production web root. They require PHP but no running database,
ORIS service, or browser.

Run from the repository root:

```sh
php tests/php/oris-entry-exists.php
```

The check uses `require_once` to load the dependency-free runtime helper from
`www/lib/oris_entry_lookup.php` without bootstrapping the legacy application. It verifies confirmed presence and absence, lookup
failures, associative entry lists, and malformed responses that must preserve
the local registration.
