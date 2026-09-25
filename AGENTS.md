# Repository agent instructions

Read and follow the project guidance in `.agents/AGENTS.md`.

## File edits and VS Code review

- Use the dedicated `apply_patch` tool for creating and editing text files so the Codex VS Code extension can display its native change review cards.
- Do not replace patch-based edits with shell scripts, Python file rewrites, `sed -i`, or shell redirection for routine source and documentation changes.
- Use shell commands for inspection, tests, and build tools. If a generated file or another operation cannot reasonably use `apply_patch`, explain the exception briefly.
- Keep each patch focused on the requested change so the resulting diff is easy to review.

## Test race-ID reservations

- The `no-race-services` fixtures reserve race IDs 24000–24004 from the 24000–25000 test range.
- The `race-deadline-workflow` fixture reserves race ID 24010.
- When another test suite reserves a new race-ID range, leave at least five unused race IDs after the highest ID in the preceding suite's reserved range; after the `no-race-services` range 24000–24004, another test suite's range must start at 24010 or later.
