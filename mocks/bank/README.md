# Raiffeisen bank mock

Development-only Node.js + TypeScript mock for the members bank integration.

## What it does

- exposes `GET /rbcz/premium/api/accounts/:accountNumber/CZK/transactions`
- stores transactions in its own MariaDB database
- seeds an initial relative transaction history when the database is empty
- keeps the newest seeded transaction at least 3 days old by default
- provides a testbench UI at `/__testbench` and keeps the legacy admin UI at `/__admin`
- provides JSON endpoints for automatic tests under both `/__testbench/api/*` and `/__admin/api/*`
- supports fault modes: `normal`, `force_client_error`, `delay`, `hang`, `close_connection`
- supports configurable `4xx` responses, including the common API cases `400`, `401`, `403`, `404`, and `429`
- auto-generates a variable symbol when one is not supplied

## Run inside the dev web container

```bash
npm run mock:bank
```

The server listens on port `10300` by default.

Set `BANK_MOCK_INITIAL_TRANSACTION_MIN_AGE_DAYS` to change how old the newest seeded transaction must be.

## Testbench API

- `GET /__testbench/api/settings`
- `POST /__testbench/api/settings`
- `GET /__testbench/api/transactions`
- `POST /__testbench/api/transactions`

The legacy `/__admin/api/*` paths are still supported.

Example forced `429`:

```bash
curl -X POST http://127.0.0.1:10300/__testbench/api/settings \
  -H 'Content-Type: application/json' \
  -d '{
    "mode": "force_client_error",
    "forceStatusCode": 429
  }'
```

Example:

```bash
r
```
