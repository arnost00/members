const { test, expect } = require('@playwright/test');
const {
  loginAs,
  openPopup,
} = require('../helpers/browser');
const {
  createRace,
  ensureClubMembers,
} = require('../helpers/app-actions');
const {
  addUtcDays,
  createWorkflowRun,
  formatCzDate,
} = require('../helpers/workflow-runtime');

function raceListRow(page, raceName) {
  return page
    .getByRole('link', { name: raceName, exact: true })
    .locator('xpath=ancestor::tr[1]');
}

async function saveRegistrationAndExpectClose(popup) {
  await popup.locator('form[name="form1"] input[type="submit"]').click();
  await expect.poll(() => popup.isClosed()).toBe(true);
}

// Multi-day camps (soustředění) have no stages - stage selection is offered only for races (#82)
test.describe('Local Camp Stages Workflow', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ browser }) => {
    state.run = createWorkflowRun('local-camp-stages-flow');

    const clubAdminContext = await browser.newContext();
    const clubAdminPage = await clubAdminContext.newPage();
    try {
      await loginAs(clubAdminPage, 'clubAdmin');
      await ensureClubMembers(clubAdminPage, ['8511', '7203']);
    } finally {
      await clubAdminContext.close();
    }

    state.firstDate = addUtcDays(new Date(), 14);
    state.campName = `PW local camp ${state.run.runId}`;
  });

  test('registrar creates a local three-day camp', async ({ page }) => {
    await loginAs(page, 'registrar');
    state.camp = await createRace(page, {
      name: state.campName,
      eventType: 'S',
      date: formatCzDate(state.firstDate),
      endDate: formatCzDate(addUtcDays(state.firstDate, 2)),
      stages: 3,
      categories: 'H35',
      entryDate1: formatCzDate(addUtcDays(new Date(), 10)),
    });

    expect(state.camp.id).toBeTruthy();
  });

  test('member registers for the camp without stage selection', async ({ page }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    const row = raceListRow(page, state.campName);
    await expect(row).toBeVisible();
    const popup = await openPopup(page, () => row.getByRole('link', { name: 'Přihl.' }).click());

    await expect(popup.locator('input[name="kat"]')).toBeVisible();
    await expect(popup.locator('input[name="etapy[]"]')).toHaveCount(0);

    await popup.locator('input[name="kat"]').fill('H35');
    await saveRegistrationAndExpectClose(popup);

    await page.reload();
    await expect(raceListRow(page, state.campName).getByRole('link', { name: 'H35' })).toBeVisible();
  });

  test('registrar forms offer no stage selection for the camp', async ({ page }) => {
    await loginAs(page, 'registrar');

    await page.goto(`./race_regs_all.php?gr_id=400&id=${state.camp.id}`);
    await expect(page.locator('input[name^="kateg["]').first()).toBeVisible();
    await expect(page.locator('input[name^="etapy["]')).toHaveCount(0);

    await page.goto(`./race_regs_1.php?gr_id=400&id=${state.camp.id}&show_ed=1`);
    await expect(page.locator('input[name="kateg"]')).toBeVisible();
    await expect(page.locator('input[name="etapy[]"]')).toHaveCount(0);

    await page.goto(`./race_reg_view.php?gr_id=400&id=${state.camp.id}`);
    await expect(page.getByText('H35').first()).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Etapy', exact: true })).toHaveCount(0);
  });

  test('member unregisters from the camp', async ({ page }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    const row = raceListRow(page, state.campName);
    page.once('dialog', (dialog) => dialog.accept());
    const popupPromise = page.waitForEvent('popup');
    await row.getByRole('link', { name: 'Od.' }).click();
    const popup = await popupPromise;
    await expect.poll(() => popup.isClosed()).toBe(true);

    await page.reload();
    await expect(raceListRow(page, state.campName).getByRole('link', { name: 'Přihl.' })).toBeVisible();
  });
});
