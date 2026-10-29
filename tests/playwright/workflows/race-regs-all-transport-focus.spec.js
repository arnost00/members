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

// Issue #89: the common-transport checkbox of a registered member had a broken
// onfocus handler ("javascri]pt:"), so focusing it did not select the row for the
// category buttons - the category went to the previously focused row instead.
const MEMBERS = ['8511', '7203'];

function memberRow(page, reg) {
  return page.locator('tr').filter({ has: page.locator('td', { hasText: new RegExp(`^${formatClubReg(reg)}$`) }) });
}

test.describe('Bulk registration form transport focus', () => {
  test.beforeAll(async ({ browser }) => {
    const context = await browser.newContext();
    const page = await context.newPage();
    try {
      await loginAs(page, 'clubAdmin');
      await ensureClubMembers(page, MEMBERS);
    } finally {
      await context.close();
    }
  });

  test('common transport can be toggled for an unregistered member without a seat field', async ({ page }) => {
    const run = createWorkflowRun('race-regs-all-common-transport');
    await loginAs(page, 'registrar');
    const race = await createRace(page, {
      name: `PW bulk common transport ${run.runId}`,
      date: formatCzDate(addUtcDays(new Date(), 14)),
      categories: 'H21;D21',
      transport: '1',
      entryDate1: formatCzDate(addUtcDays(new Date(), 10)),
    });
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));
    await page.goto(`./race_regs_all.php?gr_id=400&id=${race.id}`);
    const row = memberRow(page, MEMBERS[0]);
    await expect(row.locator('input[name^="sedadel["]')).toHaveCount(0);
    const transport = row.locator('input[name^="transport["]');
    await transport.check();
    await expect(transport).toBeChecked();
    await transport.uncheck();
    await expect(transport).not.toBeChecked();
    expect(errors).toEqual([]);
  });

  test('focusing the transport checkbox selects its row for category buttons', async ({ page }) => {
    const run = createWorkflowRun('race-regs-all-transport-focus');
    await loginAs(page, 'registrar');
    const race = await createRace(page, {
      name: `PW bulk transport focus ${run.runId}`,
      date: formatCzDate(addUtcDays(new Date(), 14)),
      categories: 'H21;D21',
      transport: '1',
      entryDate1: formatCzDate(addUtcDays(new Date(), 10)),
    });
    await ensureRaceParticipants(page, race.id, Object.fromEntries(MEMBERS.map((reg) => [reg, { kateg: 'H21' }])));

    await page.goto(`./race_regs_all.php?gr_id=400&id=${race.id}`);
    const [first, second] = MEMBERS.map((reg) => memberRow(page, reg));
    await first.locator('input[name^="kateg["]').focus();
    await second.locator('input[name^="transport["]').focus();
    await page.getByRole('button', { name: 'D21', exact: true }).click();

    await expect(second.locator('input[name^="kateg["]')).toHaveValue('D21');
    await expect(first.locator('input[name^="kateg["]')).toHaveValue('H21');
  });
});
