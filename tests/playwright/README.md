# Playwright tests

This directory contains browser end-to-end tests for local development, CI, and debugging.
These files are not part of the PHP runtime and must not be deployed to the productive web root.

## Local usage

1. Start the development stack so the application is reachable, for example on `http://web:10100/members`.
2. Install test dependencies:
   ```bash
   npm install
   ```
3. Run all regular tests:
   ```bash
   npm run test:e2e
   ```
4. Run alternate configuration suites:
   ```bash
   npm run test:e2e:no-oris
   npm run test:e2e:no-oris-key
   npm run test:e2e:no-race-services
   ```
   These command-line-only suites send a test header understood only by the committed Docker autotest configuration. The ORIS suites repeat non-ORIS workflows with ORIS disabled or its club key omitted, temporarily select the required mock mode, and restore its previous mode, status code, and delay after the run. The no-race-services suite runs dedicated checks with transport and accommodation disabled and does not change mock settings.
5. Run the manual-only bank connector error suite:
   ```bash
   npm run test:e2e:bank-errors
   npm run test:e2e:oris-errors
   ```
   These suites are intentionally excluded from the default `npm run test:e2e` run because they toggle global mock failure modes and can interfere with other workflows.
6. Write new specs using shared constants instead of hardcoded usernames:
   ```js
   const { TEST_USERS } = require('./constants/users');
   const user = TEST_USERS.member;
   ```

## Configuration

- `PLAYWRIGHT_BASE_URL` overrides the application URL. Default: `http://web:10100/members/`
- `MEMBERS_E2E_SUITE=no-oris` disables all ORIS configuration for application requests and makes mock `/API` return HTTP 503
- `MEMBERS_E2E_SUITE=no-oris-key` leaves ORIS enabled but omits `$g_oris_club_key` for application requests
- `MEMBERS_E2E_SUITE=no-race-services` disables transport and accommodation through the autotest request header, without changing ORIS or mock settings. It runs only the dedicated service-suppression spec (plus the shared user setup), covering single-day/multistage creation and editing, member registration, and race listings. Ordinary registration deadlines remain available. Fixtures own race IDs 24000–24004 from the 24000–25000 test range and suite-specific created race names, and clean up before and after each test. Run the command twice to check repeatability. This configuration and all fixtures are dev/test/CI-only and must not be deployed into the productive web root.
- `race-deadline-workflow.spec.js` covers the full local-race deadline progression for members, small managers, managers, and registrars. It reserves race ID 24010, creates the race through the registrar UI, verifies inherited and explicit service deadlines across three registration terms, and cleans up its race and entries after each run.
- `oris-deadline-workflow.spec.js` covers local updates to an ORIS-linked entry after registration closes. It reserves race ID 24011 and runs only with the normal ORIS configuration.
- The reusable login helper lives in `tests/playwright/components/login.js`
- Shared auth constants live in `tests/playwright/constants/auth.js`
  - `DEFAULT_PASSWORD` = `54321`
- Shared seeded test users live in `tests/playwright/constants/users.js`
  - `TEST_USERS.administrator` = `admin`
  - `TEST_USERS.registrar` = `tnov_1`
  - `TEST_USERS.manager` = `tnov_2`
  - `TEST_USERS.clubAdmin` = `tnov_3`
  - `TEST_USERS.smallManager` = `tnov_4`
  - `TEST_USERS.member` = `tnov_5`
  - `TEST_USERS.accountant` = `tnov_6`
- Shared reusable member fixtures keyed by registration id live in `tests/playwright/constants/members.js`
- The test-only `member-7203-setup` project ensures the shared `7203` fixture before parallel workflow projects start and fails if that registration is already duplicated
- Shared group IDs, route maps, and per-role login expectations live in `tests/playwright/constants/routes.js`
- Prefer importing `TEST_USERS` in specs instead of hardcoding seeded usernames
- Reusable workflow helpers live under `tests/playwright/helpers/`
- Shared multi-step workflow specs can live under `tests/playwright/workflows/`
- `workflows/oris-relay-race-flow.spec.js` covers imported relay races, whose registrations stay local and do not create individual ORIS entries
- Manual-only suites can use a dedicated Playwright config when they must stay out of the default parallel run
  - Bank connector error checks: `npm run test:e2e:bank-errors`
  - Config: `playwright.bank-errors.config.js`
  - ORIS connector error checks: `npm run test:e2e:oris-errors`
  - Config: `playwright.oris-errors.config.js`
