const { test, expect } = require('@playwright/test');
const { TEST_USERS } = require('../constants/users');
const {
  getCurrentUser,
  loginViaApi,
} = require('../helpers/api');
const {
  loginAs,
  openPopup,
} = require('../helpers/browser');
const {
  createRace,
  ensureClubMembers,
  submitManagedRaceRegistration,
} = require('../helpers/app-actions');
const {
  addUtcDays,
  createWorkflowRun,
  formatCzDate,
} = require('../helpers/workflow-runtime');

function stageCheckbox(page, stage) {
  return page.locator(`input[name="etapy[]"][value="${stage}"]`);
}

function raceListRow(page, raceName) {
  return page
    .getByRole('link', { name: raceName, exact: true })
    .locator('xpath=ancestor::tr[1]');
}

function bulkRegistrationRow(page, reg) {
  return page
    .locator('td')
    .filter({ hasText: new RegExp(`^${reg}$`) })
    .first()
    .locator('xpath=ancestor::tr[1]');
}

function bulkCategoryInput(page, reg) {
  return bulkRegistrationRow(page, reg).locator('input[name^="kateg["]');
}

function bulkStageCheckbox(page, reg, stage) {
  return bulkRegistrationRow(page, reg)
    .locator(`input[name^="etapy["][value="${stage}"]`);
}

async function saveRegistrationAndExpectClose(popup) {
  await Promise.all([
    popup.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    popup.locator('form[name="form1"] input[type="submit"]').click(),
  ]);
  await popup.close();
  expect(popup.isClosed()).toBe(true);
}

test.describe('Local Multistage Race Workflow', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ browser, request }) => {
    state.run = createWorkflowRun('local-multistage-race-flow');
    const memberToken = await loginViaApi(request, TEST_USERS.member);
    state.memberUser = await getCurrentUser(request, memberToken);

    const clubAdminContext = await browser.newContext();
    const clubAdminPage = await clubAdminContext.newPage();
    try {
      await loginAs(clubAdminPage, 'clubAdmin');
      await ensureClubMembers(clubAdminPage, ['8511', '7203']);
    } finally {
      await clubAdminContext.close();
    }

    state.firstRaceDate = addUtcDays(new Date(), 14);
    state.raceName = `PW local multistage ${state.run.runId}`;
  });

  test('registrar creates a local three-stage race', async ({ page, request }) => {
    await loginAs(page, 'registrar');
    state.race = await createRace(page, request, {
      name: state.raceName,
      date: formatCzDate(state.firstRaceDate),
      endDate: formatCzDate(addUtcDays(state.firstRaceDate, 2)),
      stages: 3,
      categories: 'H35',
      entryDate1: formatCzDate(addUtcDays(new Date(), 10)),
    });

    expect(state.race.id).toBeTruthy();
    expect(state.race.name).toBe(state.raceName);
  });

  test('member registers for H35 with stages 1 and 3', async ({ page }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    const row = raceListRow(page, state.raceName);
    await expect(row).toBeVisible();
    const popup = await openPopup(page, () => row.getByRole('link', { name: 'Přihl.' }).click());

    for (const stage of [1, 2, 3]) {
      await expect(stageCheckbox(popup, stage)).toBeChecked();
    }

    await popup.locator('input[name="kat"]').fill('H35');
    await stageCheckbox(popup, 2).uncheck();
    await saveRegistrationAndExpectClose(popup);
  });

  test('member changes the selected stages to 1 and 2', async ({ page }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    const row = raceListRow(page, state.raceName);
    const popup = await openPopup(page, () => row.getByRole('link', { name: 'H35' }).click());

    await expect(stageCheckbox(popup, 1)).toBeChecked();
    await expect(stageCheckbox(popup, 2)).not.toBeChecked();
    await expect(stageCheckbox(popup, 3)).toBeChecked();

    await stageCheckbox(popup, 2).check();
    await stageCheckbox(popup, 3).uncheck();
    await saveRegistrationAndExpectClose(popup);
  });

  test('registrar changes categories for two users with the bulk registration form', async ({ page }) => {
    await loginAs(page, 'registrar');
    await page.goto('./index.php?id=400&subid=1');

    const row = raceListRow(page, state.raceName);
    const popup = await openPopup(page, () => (
      row.getByRole('link', { name: 'P.V', exact: true }).click()
    ));
    await expect(popup.getByRole('heading', { name: 'Hromadná přihlášky na závody' })).toBeVisible();

    await bulkCategoryInput(popup, '8511').fill('H35');
    await bulkCategoryInput(popup, '7203').fill('H35');

    for (const stage of [1, 2, 3]) {
      await expect(bulkStageCheckbox(popup, '8511', stage)).toBeChecked();
      await expect(bulkStageCheckbox(popup, '7203', stage)).toBeChecked();
    }

    await bulkStageCheckbox(popup, '8511', 2).uncheck();
    await bulkStageCheckbox(popup, '8511', 3).uncheck();

    const verificationUrl = new URL(
      `./race_regs_all.php?gr_id=400&id=${state.race.id}`,
      page.url()
    ).toString();

    await Promise.all([
      popup.waitForNavigation({ waitUntil: 'domcontentloaded' }),
      popup.locator('input[type="submit"][value="Proveď změny"]').click(),
    ]);
    await popup.goto(verificationUrl, { waitUntil: 'domcontentloaded' });

    await expect(bulkCategoryInput(popup, '8511')).toHaveValue('H35');
    await expect(bulkCategoryInput(popup, '7203')).toHaveValue('H35');
    await expect(bulkStageCheckbox(popup, '8511', 1)).toBeChecked();
    await expect(bulkStageCheckbox(popup, '8511', 2)).not.toBeChecked();
    await expect(bulkStageCheckbox(popup, '8511', 3)).not.toBeChecked();
    await popup.close();
  });

  test('member still has stages 1 and 2 selected after the bulk update', async ({ page }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    const row = raceListRow(page, state.raceName);
    const popup = await openPopup(page, () => row.getByRole('link', { name: 'H35' }).click());

    await expect(stageCheckbox(popup, 1)).toBeChecked();
    await expect(stageCheckbox(popup, 2)).toBeChecked();
    await expect(stageCheckbox(popup, 3)).not.toBeChecked();
    await popup.close();
  });

  test('member unregisters with the Od. race-list link', async ({ page }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    let row = raceListRow(page, state.raceName);
    page.once('dialog', (dialog) => dialog.accept());
    const popupPromise = page.waitForEvent('popup');
    await row.getByRole('link', { name: 'Od.' }).click();
    const popup = await popupPromise;
    await expect.poll(() => popup.isClosed()).toBe(true);
  });

  test('member sees no category on the unregistered race', async ({ page }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    const row = raceListRow(page, state.raceName);
    await expect(row.getByRole('link', { name: 'Přihl.' })).toBeVisible();
    await expect(row).not.toContainText('H35');
  });

  test('small manager bulk-adds the removed member and removes them with single edit', async ({ page }) => {
    await loginAs(page, 'smallManager');

    await page.goto(`./race_regs_all.php?gr_id=600&id=${state.race.id}`);
    const category = bulkCategoryInput(page, '9952');
    await expect(category).toHaveValue('');

    await category.fill('H35');
    for (const stage of [1, 2, 3]) {
      await expect(bulkStageCheckbox(page, '9952', stage)).toBeChecked();
    }

    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
      page.locator('input[type="submit"][value="Proveď změny"]').click(),
    ]);
    await expect(bulkCategoryInput(page, '9952')).toHaveValue('H35');

    await page.goto(`./race_regs_1.php?gr_id=600&id=${state.race.id}&show_ed=1`);
    await page.locator('select[name="user_id"]').selectOption(String(state.memberUser.user_id));
    await expect(page.locator('input[name="kateg"]')).toHaveValue('H35');

    await submitManagedRaceRegistration(page, state.race.id, {
      user_id: String(state.memberUser.user_id),
      kateg: '',
    });

    await page.goto(`./race_regs_all.php?gr_id=600&id=${state.race.id}`);
    await expect(bulkCategoryInput(page, '9952')).toHaveValue('');
  });
});
