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
  ensureClubMembers,
} = require('../helpers/app-actions');
const {
  createOrisMockRace,
  createOrisMockUser,
  getOrisApiEvent,
  getOrisMockParticipants,
} = require('../helpers/oris-mock');
const {
  ensureOrisRace,
} = require('../helpers/oris-race-workflow');
const {
  addUtcDays,
  createWorkflowRun,
} = require('../helpers/workflow-runtime');

function formatOrisDate(date) {
  return date.toISOString().slice(0, 10);
}

function formatDisplayedCzDate(date) {
  return `${date.getUTCDate()}.${date.getUTCMonth() + 1}.${date.getUTCFullYear()}`;
}

function stageCheckbox(popup, stage) {
  return popup.locator(`input[name="etapy[]"][value="${stage}"]`);
}

function raceListRow(page, raceName) {
  return page
    .getByRole('link', { name: raceName, exact: true })
    .locator('xpath=ancestor::tr[1]');
}

function bulkCategoryInput(popup, reg) {
  return popup
    .locator('td')
    .filter({ hasText: new RegExp(`^${reg}$`) })
    .first()
    .locator('xpath=ancestor::tr[1]')
    .locator('input[name^="kateg["]');
}

async function saveRegistrationAndExpectClose(popup) {
  await Promise.all([
    popup.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    popup.locator('form[name="form1"] input[type="submit"]').click(),
  ]);
  await popup.close();
  expect(popup.isClosed()).toBe(true);
}

test.describe('ORIS Mockup Multistage Race Workflow', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ browser, request }) => {
    state.run = createWorkflowRun('oris-mockup-multistage-race-flow');
    state.memberToken = await loginViaApi(request, TEST_USERS.member);
    state.memberUser = await getCurrentUser(request, state.memberToken);

    await createOrisMockUser(request, {
      userId: '29952',
      clubUserId: '39952',
      regNo: 'ZBM9952',
      firstName: state.memberUser.name || 'Zuzana',
      lastName: state.memberUser.surname || 'Novakova',
      si: state.memberUser.chip_number || '1341431',
      licence: 'C',
    });
    await createOrisMockUser(request, {
      userId: '28511',
      clubUserId: '38511',
      regNo: 'ZBM8511',
      firstName: 'Jan',
      lastName: 'Drabek',
      si: '49690',
      licence: 'C',
    });
    await createOrisMockUser(request, {
      userId: '54452',
      clubUserId: '37517',
      regNo: 'ZBM7203',
      firstName: 'Radim',
      lastName: 'Cenek',
      si: '2181929',
      licence: 'C',
    });

    const clubAdminContext = await browser.newContext();
    const clubAdminPage = await clubAdminContext.newPage();
    try {
      await loginAs(clubAdminPage, 'clubAdmin');
      await ensureClubMembers(clubAdminPage, ['8511', '7203']);
    } finally {
      await clubAdminContext.close();
    }

    const firstRaceDate = addUtcDays(new Date(), 14);
    state.expectedRaceDate = formatDisplayedCzDate(firstRaceDate);
    const classes = [{ Name: 'H35', Fee: 150 }];
    const thirdStage = await createOrisMockRace(request, {
      name: `PW multistage ${state.run.runId} - stage 3`,
      date: formatOrisDate(addUtcDays(firstRaceDate, 2)),
      classes,
    });
    const secondStage = await createOrisMockRace(request, {
      name: `PW multistage ${state.run.runId} - stage 2`,
      date: formatOrisDate(addUtcDays(firstRaceDate, 1)),
      classes,
    });
    state.secondStageId = String(secondStage.race.ID);
    state.thirdStageId = String(thirdStage.race.ID);

    state.mockRace = await createOrisMockRace(request, {
      name: `PW multistage ${state.run.runId}`,
      date: formatOrisDate(firstRaceDate),
      stages: 3,
      stage2: state.secondStageId,
      stage3: state.thirdStageId,
      classes,
    });
    state.orisId = String(state.mockRace.race.ID);
  });

  test('registrar imports the linked three-stage mock race', async ({ page, request }) => {
    await loginAs(page, 'registrar');
    state.race = await ensureOrisRace(page, state.orisId);

    const event = await getOrisApiEvent(request, state.orisId);
    const secondStage = await getOrisApiEvent(request, state.secondStageId);
    const thirdStage = await getOrisApiEvent(request, state.thirdStageId);
    expect(event.Stages).toBe(3);
    expect(event.Stage1).toBe(state.orisId);
    expect(event.Stage2).toBe(state.secondStageId);
    expect(event.Stage3).toBe(state.thirdStageId);
    expect(Date.parse(`${secondStage.Date}T00:00:00Z`) - Date.parse(`${event.Date}T00:00:00Z`))
      .toBe(24 * 60 * 60 * 1000);
    expect(Date.parse(`${thirdStage.Date}T00:00:00Z`) - Date.parse(`${secondStage.Date}T00:00:00Z`))
      .toBe(24 * 60 * 60 * 1000);
    expect(state.race.id).toBeTruthy();
    expect(state.race.date).toBe(state.expectedRaceDate);
  });

  test('member registers for H35 with stages 1 and 3', async ({ page }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    let row = raceListRow(page, state.mockRace.race.Name);
    await expect(row).toBeVisible();
    const popup = await openPopup(page, () => row.getByRole('link', { name: 'Přihl.' }).click());

    await expect(stageCheckbox(popup, 1)).toBeChecked();
    await expect(stageCheckbox(popup, 2)).toBeChecked();
    await expect(stageCheckbox(popup, 3)).toBeChecked();

    await popup.locator('input[name="kat"]').fill('H35');
    await stageCheckbox(popup, 2).uncheck();
    await saveRegistrationAndExpectClose(popup);
  });

  test('member changes the selected stages to 1 and 2', async ({ page, request }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    const row = raceListRow(page, state.mockRace.race.Name);
    await expect(row.getByRole('link', { name: 'H35' })).toBeVisible();
    const popup = await openPopup(page, () => row.getByRole('link', { name: 'H35' }).click());

    await expect(stageCheckbox(popup, 1)).toBeChecked();
    await expect(stageCheckbox(popup, 2)).not.toBeChecked();
    await expect(stageCheckbox(popup, 3)).toBeChecked();

    await stageCheckbox(popup, 2).check();
    await stageCheckbox(popup, 3).uncheck();
    await saveRegistrationAndExpectClose(popup);

    const { participants } = await getOrisMockParticipants(request);
    const entry = participants.find((participant) => (
      String(participant.event_id) === state.orisId
      && String(participant.club_user_id) === '39952'
    ));

    expect(entry).toBeTruthy();
    expect(entry.stages).toBe('1,2');
  });

  test('registrar changes categories for two users with the bulk registration form', async ({ page, request }) => {
    await loginAs(page, 'registrar');
    await page.goto('./index.php?id=400&subid=1');

    const row = raceListRow(page, state.mockRace.race.Name);
    const popup = await openPopup(page, () => (
      row.getByRole('link', { name: 'P.V', exact: true }).click()
    ));
    await expect(popup.getByRole('heading', { name: 'Hromadná přihlášky na závody' })).toBeVisible();

    const firstCategory = bulkCategoryInput(popup, '8511');
    const secondCategory = bulkCategoryInput(popup, '7203');
    await firstCategory.fill('H35');
    await secondCategory.fill('H35');
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
    await popup.close();

    const { participants } = await getOrisMockParticipants(request);
    const raceParticipants = participants.filter((participant) => (
      String(participant.event_id) === state.orisId
    ));

    expect(raceParticipants).toHaveLength(3);
    expect(raceParticipants).toEqual(expect.arrayContaining([
      expect.objectContaining({ club_user_id: '39952', stages: '1,2' }),
      expect.objectContaining({ club_user_id: '38511', stages: '1,2,3' }),
      expect.objectContaining({ club_user_id: '37517', stages: '1,2,3' }),
    ]));
  });

  test('member still has stages 1 and 2 selected after the bulk update', async ({ page }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    const row = raceListRow(page, state.mockRace.race.Name);
    await expect(row.getByRole('link', { name: 'H35' })).toBeVisible();
    const popup = await openPopup(page, () => row.getByRole('link', { name: 'H35' }).click());

    await expect(stageCheckbox(popup, 1)).toBeChecked();
    await expect(stageCheckbox(popup, 2)).toBeChecked();
    await expect(stageCheckbox(popup, 3)).not.toBeChecked();
    await popup.close();
  });

  test('member unregisters with the Od. race-list link', async ({ page }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    let row = raceListRow(page, state.mockRace.race.Name);
    await expect(row.getByRole('link', { name: 'H35' })).toBeVisible();

    page.once('dialog', (dialog) => dialog.accept());
    const popupPromise = page.waitForEvent('popup');
    await row.getByRole('link', { name: 'Od.' }).click();
    const popup = await popupPromise;
    await expect.poll(() => popup.isClosed()).toBe(true);
  });

  test('member sees no category on the unregistered race', async ({ page, request }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    const row = raceListRow(page, state.mockRace.race.Name);
    await expect(row.getByRole('link', { name: 'Přihl.' })).toBeVisible();
    await expect(row).not.toContainText('H35');

    const { participants } = await getOrisMockParticipants(request);
    const raceParticipants = participants.filter((participant) => (
      String(participant.event_id) === state.orisId
    ));

    expect(raceParticipants).toHaveLength(2);
    expect(raceParticipants).toEqual(expect.arrayContaining([
      expect.objectContaining({ club_user_id: '38511', stages: '1,2,3' }),
      expect.objectContaining({ club_user_id: '37517', stages: '1,2,3' }),
    ]));
    expect(raceParticipants).not.toEqual(expect.arrayContaining([
      expect.objectContaining({ club_user_id: '39952' }),
    ]));
  });
});
