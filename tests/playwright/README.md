# Playwright tests

This directory contains browser end-to-end tests for local development, CI, and debugging.
These files are not part of the PHP runtime and must not be deployed to the productive web root.

## Local usage

1. Start the development stack so the application is reachable, for example on `http://127.0.0.1:10100/members`.
2. Install test dependencies:
   ```bash
   npm install
   ```
3. Run the login smoke test:
   ```bash
   npm run test:e2e -- tests/playwright/login.spec.js
   ```

## Configuration

- `PLAYWRIGHT_BASE_URL` overrides the application URL. Default: `http://127.0.0.1:10100/members/`
- `PLAYWRIGHT_LOGIN_USER` overrides the seeded username used by `login.spec.js`. Default: `tnov_5`
- The reusable login helper lives in `tests/playwright/components/login.js`
- The shared seeded password used by the helper is `54321`
