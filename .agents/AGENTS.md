# AGENTS.md

## Project context
This repository contains a legacy PHP application.
Most PHP entry files live directly in the repository root and this layout must remain stable unless a task explicitly requires otherwise.

Production is deployed to a classic LAMP server by copying or checking out only the runtime subset of the repository.
Production is not Docker-based.

## General change rules
- Prefer incremental, low-risk changes.
- Do not move root PHP entry files into `src/`, `app/`, or `public/` unless explicitly requested.
- Do not redesign the legacy application structure as part of unrelated work.
- Avoid touching unrelated PHP business logic.
- Keep comments, docs, and helper scripts in English.
- Keep scripts simple, readable, and pragmatic.

## Runtime vs test separation
- Keep productive runtime files separate from development and test tooling.
- Place browser end-to-end tests under `tests/`.
- Place API mock services under `mocks/`.
- Do not make Playwright, Node, mock services, or test reports part of the productive web root.
- Do not introduce a production dependency on Docker or Node.

## End-to-end test isolation
- Assume Playwright workflows run in parallel and may be repeated against an existing test database; every test must be reentrant.
- Users shared by multiple workflows must come from the test-data import or be created by a prerequisite setup project before the parallel workflows start.
- Use deterministic, dedicated IDs for test-owned users and mock records. Do not generate random user IDs.
- When a test creates dedicated persistent data, either delete it during teardown or ensure that a later run verifies and updates the existing record to the required state.
- Keep tests that change shared mock-server behavior or configuration out of the regular parallel suite. ORIS mock changes belong in the dedicated ORIS error suite, bank mock changes belong in the dedicated bank error suite, and the existing no-ORIS/no-key suites cover their respective connector configurations.

## Preferred locations for new infrastructure
- `tests/playwright/` for Playwright tests
- `mocks/bank/` for bank API mock
- `mocks/oris/` for ORIS API mock
- `tools/` for packaging and deployment helper scripts
- `docker-compose.dev.yml` for local development
- `docker-compose.autotest.yml` for local/CI end-to-end testing

## Production deployment expectations
When adding files or directories, ensure production packaging can exclude at least:
- `docker/`
- `tests/`
- `mocks/`
- `.github/`
- `playwright-report/`
- `test-results/`
- `node_modules/`
- local-only helper artifacts
- CI-only files

Production packaging should include only the PHP runtime subset required by the LAMP deployment.

## Documentation expectations
When adding test or mock infrastructure, also add or update documentation that explains:
- purpose
- local usage
- that the files are for dev/test/CI only
- that they must not be deployed into the productive web root

## Change style
- Prefer separation by packaging/deployment rules over moving legacy application files.
- Preserve existing behavior unless the task explicitly requires a change.
- If something is ambiguous, choose the least disruptive option.
