const { test, expect } = require('@playwright/test');
const { loginAs } = require('../helpers/browser');
const {
  createRace,
  ensureClubMembers,
  formatClubReg,
} = require('../helpers/app-actions');
const { ensureRaceParticipants } = require('../helpers/oris-race-workflow');
const {
  addUtcDays,
  createWorkflowRun,
  formatCzDate,
} = require('../helpers/workflow-runtime');

// Issue #88: the bulk registration form escaped category and notes twice, so an
// apostrophe or backslash was stored with an extra backslash.
const MEMBER_REG = '8511';

async function readNotes(page, raceId) {
  await page.goto(`./race_regs_all.php?gr_id=400&id=${raceId}`);
  const row = page.locator('tr').filter({ has: page.locator('td', { hasText: new RegExp(`^${formatClubReg(MEMBER_REG)}$`) }) });
  return {
    note: await row.locator('input[name^="pozn["]').inputValue(),
    noteInternal: await row.locator('input[name^="pozn2["]').inputValue(),
  };
}

test.describe('Bulk registration form escaping', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ browser }) => {
    state.run = createWorkflowRun('race-regs-all-escaping');
    const context = await browser.newContext();
    const page = await context.newPage();
    try {
      await loginAs(page, 'clubAdmin');
      await ensureClubMembers(page, [MEMBER_REG]);
    } finally {
      await context.close();
    }
  });

  test('notes with apostrophe and backslash are stored unchanged', async ({ page }) => {
    await loginAs(page, 'registrar');
    const race = await createRace(page, {
      name: `PW bulk escaping ${state.run.runId}`,
      date: formatCzDate(addUtcDays(new Date(), 14)),
      categories: 'H21',
      entryDate1: formatCzDate(addUtcDays(new Date(), 10)),
    });

    // new entry
    const created = { note: `O'Brien \\ new ${state.run.runId}`, noteInternal: `it's internal \\ new` };
    await ensureRaceParticipants(page, race.id, { [MEMBER_REG]: { kateg: 'H21', ...created } });
    expect(await readNotes(page, race.id)).toEqual(created);

    // update of an existing entry
    const updated = { note: `D'Artagnan \\ upd ${state.run.runId}`, noteInternal: `that's internal \\ upd` };
    await ensureRaceParticipants(page, race.id, { [MEMBER_REG]: { kateg: 'H21', ...updated } });
    expect(await readNotes(page, race.id)).toEqual(updated);
  });
});
