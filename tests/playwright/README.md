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
4. Repeat the non-ORIS workflows with ORIS fully disabled or with only its club key omitted:
   ```bash
   npm run test:e2e:no-oris
   npm run test:e2e:no-oris-key
   ```
   These command-line-only suites send a test header understood only by the committed Docker autotest configuration. They keep the ORIS mock process and health endpoint running, temporarily select the required mock mode, and restore its exact previous mode, status code, and delay after the run.
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
- Manual-only suites can use a dedicated Playwright config when they must stay out of the default parallel run
  - Bank connector error checks: `npm run test:e2e:bank-errors`
  - Config: `playwright.bank-errors.config.js`
  - ORIS connector error checks: `npm run test:e2e:oris-errors`
  - Config: `playwright.oris-errors.config.js`
