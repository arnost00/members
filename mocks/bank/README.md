# Raiffeisen bank mock

Development-only Node.js + TypeScript mock for the members bank integration.

## What it does

- exposes `GET /rbcz/premium/api/accounts/:accountNumber/CZK/transactions`
- stores transactions in its own MariaDB database
- seeds an initial relative transaction history when the database is empty
- keeps the newest seeded transaction at least 3 days old by default
- provides an admin UI at `/__admin`
- provides admin JSON endpoints for automatic tests
- supports fault modes: `normal`, `force_client_error`, `delay`, `hang`, `close_connection`
- supports configurable `4xx` responses, including the common API cases `400`, `401`, `403`, `404`, and `429`
- auto-generates a variable symbol when one is not supplied

## Run inside the dev web container

```bash
npm run mock:bank
```

The server listens on port `10300` by default.

Set `BANK_MOCK_INITIAL_TRANSACTION_MIN_AGE_DAYS` to change how old the newest seeded transaction must be.

## Admin API

- `GET /__admin/api/settings`
- `POST /__admin/api/settings`
- `GET /__admin/api/transactions`
- `POST /__admin/api/transactions`

Example forced `429`:

```bash
curl -X POST http://127.0.0.1:10300/__admin/api/settings \
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
