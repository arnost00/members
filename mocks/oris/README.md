# ORIS mock

Development and autotest sidecar for the members ORIS integration.

## What it does

- exposes an ORIS-compatible `GET/POST /API/`
- stores local races, users, entries, classes, and services as MariaDB columns
- reads real ORIS for unstored or overlay races, then applies local column overlays and local deletions before returning JSON
- serves proxy-only event, entry, and service requests entirely from the mock database without calling real ORIS
- treats `NULL` overlay columns as "leave the upstream value unchanged"
- composes proxy-only objects from columns and defaults on every request
- keeps mutating calls local-only: `createEntry`, `updateEntry`, `deleteEntry`, `createPerson`, `editPerson`, `createClubUser`, `editClubUser`
- provides a testbench UI at `/__testbench`
- provides JSON endpoints under `/__testbench/api/*` for automatic tests
- logs ORIS-compatible `/API/` requests to `www/logs/oris_mock_api.log` by default
- supports network disturbance modes: `normal`, `force_client_error`, `service_down`, `delay`, `hang`, `close_connection`
- requires the exact key `mockClubKey` for the same protected methods as `OrisIntegrationService::CLUB_KEY_REQUIRED_METHODS`

Protected methods without `clubkey` return the ORIS status `Zadejte všechny požadované informace`. A supplied key other than `mockClubKey` returns `Key not valid`. Both responses keep the normal ORIS JSON envelope (`Method`, `Format`, `Status`, `ExportCreated`, and an empty `Data` array).

## Run inside the dev web container

```bash
npm run mock:oris
```

The server listens on port `10301` by default.

## Testbench API

- `GET /__testbench/api/settings`
- `POST /__testbench/api/settings`
- `GET /__testbench/api/log`
- `DELETE /__testbench/api/log`
- `POST /__testbench/api/reset`
- `GET /__testbench/api/races`
- `POST /__testbench/api/races`
- `PUT /__testbench/api/races/:id`
- `DELETE /__testbench/api/races/:id`
- `GET /__testbench/api/users`
- `POST /__testbench/api/users`
- `PUT /__testbench/api/users/:userId`
- `DELETE /__testbench/api/users/:userId?regNo=ZBM9999&sport=1&year=2026`
- `GET /__testbench/api/entries`
- `GET /__testbench/api/participants`
- `GET /__testbench/api/races/:eventId/entries`
- `POST /__testbench/api/races/:eventId/entries`
- `PUT /__testbench/api/entries/:entryId`
- `PUT /__testbench/api/participants/:entryId`
- `DELETE /__testbench/api/races/:eventId/entries/:entryId`
- `DELETE /__testbench/api/entries/:entryId`
- `POST /__testbench/api/races/:eventId/services`

Race create/update accepts `proxy_only` (`1` for mock/local-only, `0` for upstream overlay).
Mock multistage races can link their component events with `stage1`, `stage2`, and
`stage3` (or the equivalent ORIS-style `Stage1`, `Stage2`, and `Stage3` fields).
Race sport is stored by name (`OB`, `LOB`, `MTBO`, or `TRAIL`) in an enum column.
Levels are stored by name (for example `MČR,OŽ`), while regions continue to use ORIS region IDs.
For compatibility, race input may use either names (`sport`, `levels`) or ORIS IDs (`sportId`, `levelIds`).
When omitted, `entryStart` remains empty for newly created races.
When an upstream overlay race is saved, the mock fetches the upstream event and stores all returned classes in the mock database so later local entry mutations can resolve `class` IDs without another event lookup.
Successful upstream `getEvent` requests through the mock also refresh the stored class snapshot.
Participant `stages` values are stored as comma-separated, one-based stage indexes (for example `1,3`).
ORIS-compatible `createEntry` and `updateEntry` calls accept stage flags such as
`stage1=1&stage2=0&stage3=1`; a multistage entry must select at least one stage.
`getEventEntries` exposes the selection as a `Stages` object containing `Stage1`, `Stage2`, and so on.

The API log path can be overridden with `ORIS_MOCK_API_LOG_FILE`.

Example service-down mode:

```bash
curl -X POST http://127.0.0.1:10301/__testbench/api/settings \
  -H 'Content-Type: application/json' \
  -d '{"mode":"service_down","forceStatusCode":503}'
```

Example long delay:

```bash
curl -X POST http://127.0.0.1:10301/__testbench/api/settings \
  -H 'Content-Type: application/json' \
  -d '{"mode":"delay","responseDelayMs":30000}'
```

Example:

```bash
curl -X POST http://127.0.0.1:10301/__testbench/api/races \
  -H 'Content-Type: application/json' \
  -d '{"id":"990001","name":"Proxy test race","date":"2026-06-01","entryStart":"2026-05-25 20:00:00","entryDate1":"2026-05-30 20:00:00","classes":[{"ID":"99000101","Name":"H21","Fee":150}]}'
```
