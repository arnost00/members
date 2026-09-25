# Race registration and service deadlines

## Deployment

Deploy the PHP runtime changes and run the existing administrator database upgrade
`www/_SQL/zmeny_3.5.2.675.sql.php` once before opening registration pages. The upgrade
adds `transport_do` immediately after `transport` and `ubytovani_do` immediately
after `ubytovani`. Both
development SQL seeds include these fields. The upgrade was applied to the local
`members_dev` database during development.

All stored registration deadlines are used directly, without an old/new distinction
or time adjustment. Existing timestamps are not rewritten or automatically refreshed.
Creating or updating a race from ORIS stores its full deadline timestamps. An explicit
ORIS refresh leaves locally defined terms 4/5 unchanged.
Automatic transport/accommodation follows the race registration, regardless of service
deadlines or legacy participant flags. The migration does not rewrite participant rows.
Only selectable services have independent deadlines and can block cancellation after
their deadline. Automatic services do not offer separate D/U actions or deadline indicators.

Production requires PHP and the database only. `tests/`, Node, Playwright, and mock
services are development/test tools and must remain outside the deployed web root.

## Behavior

ORIS times with an offset preserve it; offset-free ORIS times are interpreted in
`Europe/Prague`. Forms show Czech local time with seconds. Server comparisons use
absolute timestamps, and the exact deadline is closed (`now >= deadline`). Explicit
registration deadlines can fall on the race day. Without a registration deadline,
the existing race-day cutoff applies. Date-only form submissions remain accepted as
23:59:59 Czech time. Invalid dates and nonexistent spring-transition times are rejected.
Saving an unchanged repeated autumn time preserves its original instant.

A blank service deadline always inherits the local first registration deadline
(`prihlasky1`), including when it is edited after importing from ORIS. Setting a custom timestamp overrides
inheritance; clearing it restores inheritance. Each enabled service whose deadline
differs from the first registration deadline receives D/U in the deadline column,
struck through when closed, with its exact timestamp in a tooltip.

Members can change open services on an existing entry even when race registration or
their original registration term is closed. Closed fields are preserved. Service-only
updates do not change race details or ORIS synchronization state. Cancelling a race
entry with a closed service booking requires the registrator. Authorized staff retain
the management forms; finance API writes require an authenticated staff role.

## Verification

Run from the repository root:

```sh
php -d short_open_tag=1 tests/php/race-deadlines.php
npx playwright test tests/playwright/race-deadlines.spec.js --project=chromium --no-deps --workers=1
```

Browser tests require the initialized local application, test accounts, PHP CLI with
its database extension, and the database upgrade. `PLAYWRIGHT_BASE_URL` can override
the configured app URL. The fixture uses the local PHP database configuration, refuses
non-dev/test database names, owns only race IDs 6200/6201 and their exact test names,
and cleans up its races and entries. It never changes member accounts or ORIS mock
settings. Each time-zone group runs serially; different groups have separate IDs.

The PHP tests cover boundary instants, four server time zones, daylight saving,
inheritance, ORIS refresh mapping, unchanged timestamp preservation, service overrides, automatic
bookings, and indicators. Browser tests cover Los Angeles/Tokyo clients, new and edited
forms, stale requests, independent editing, cancellation, staff overrides, and the
finance API authorization check. The broader existing workflows need the separate
`/api/user.php` service, which is unavailable in this development environment; no live
ORIS synchronization was exercised by these focused tests.

Registration deadline summaries show only the Czech date for 23:59:59. Other times
show the previous Czech calendar day followed by ◷; the tooltip contains the exact
deadline and CET/CEST zone. Editing fields retain the full date and time.

## Development database time zone

The base, development, and autotest Compose database services set `TZ=Europe/Prague`
and `--default-time-zone=SYSTEM`. New database sessions therefore use Czech local
time, switching between CET and CEST according to the date. The autotest observation
overlay inherits this setting. This is a container/server setting, not a per-database
setting, and applies both to new databases and reused database volumes.

For an existing development stack, recreate the database container from the host:

```sh
docker compose -f docker-compose.dev.yml up -d --force-recreate db
```

Use the same Compose files and project name as when starting your stack. Reconnect
phpMyAdmin/application sessions afterward. Do not delete the database volume.

Verify in a fresh SQL session:

```sql
SELECT @@global.time_zone, @@session.time_zone, @@system_time_zone;
SELECT FROM_UNIXTIME(1767268800) AS winter_time,
       FROM_UNIXTIME(1782907200) AS summer_time;
```

The default/global session zone should be `SYSTEM`; system zone is CET or CEST.
The sample times should be `2026-01-01 13:00:00` and `2026-07-01 14:00:00`.
The SQL dumps retain their explicit UTC import session so existing TIMESTAMP data
is restored consistently. This does not change the default for later connections
or rewrite integer race timestamps. These Docker settings are for development/test
only; production LAMP configuration is managed separately.
