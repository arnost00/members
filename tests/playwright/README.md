# Playwright tests

This directory contains browser end-to-end tests for local development, CI, and debugging.
These files are not part of the PHP runtime and must not be deployed to the productive web root.

## Local usage

1. Start the development stack so the application is reachable, for example on `http://web:10100/members`.
2. Install test dependencies:
   ```bash
   npm install
   ```
3. Run the login smoke test:
   ```bash
   npm run test:e2e -- tests/playwright/login.spec.js
   ```
4. Run the reusable multi-user workflow:
   ```bash
   npm run test:e2e -- tests/playwright/workflows/multiuser-race-flow.spec.js
   ```
5. Write new specs using shared constants instead of hardcoded usernames:
   ```js
   const { TEST_USERS } = require('./constants/users');
   const user = TEST_USERS.member;
   ```

## Configuration

- `PLAYWRIGHT_BASE_URL` overrides the application URL. Default: `http://web:10100/members/`
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
- Shared group IDs, route maps, and per-role login expectations live in `tests/playwright/constants/routes.js`
- Prefer importing `TEST_USERS` in specs instead of hardcoding seeded usernames
- Reusable workflow helpers live under `tests/playwright/helpers/`
- Shared multi-step workflow specs can live under `tests/playwright/workflows/`
