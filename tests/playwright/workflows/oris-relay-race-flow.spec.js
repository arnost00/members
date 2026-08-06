const { test, expect } = require('@playwright/test');
const { TEST_USERS } = require('../constants/users');
const {
  getCurrentUser,
  getRaceDetail,
  loginViaApi,
} = require('../helpers/api');
const {
  loginAs,
} = require('../helpers/browser');
const {
  submitMemberRaceRegistration,
  updateRace,
} = require('../helpers/app-actions');
const {
  createOrisMockRace,
  getOrisApiEvent,
  getOrisApiEventEntries,
} = require('../helpers/oris-mock');
const {
  ensureOrisRace,
  ensureRaceParticipants,
} = require('../helpers/oris-race-workflow');
const {
  createWorkflowRun,
} = require('../helpers/workflow-runtime');
const {
  expectFinanceRowValues,
  financeRow,
  openRaceFinancePopup,
} = require('../helpers/race-finance');

const RELAY_RACE_WORKFLOW = {
  name: 'Relay Race',
  memberCategory: 'D21',
  categories: ['H21', 'D21', 'H135', 'D135'],
  levelIds: '7',
  rankings: [
    { id: 1, name: 'Celostátní' },
    { id: 32, name: 'Štafety' },
  ],
  participants: [
    {
      '8001': {
        kateg: 'H21',
      },
    },
    {
      '6107': {
        kateg: 'H135',
      },
    },
  ],
};

function formatSavedRaceDate(date) {
  return [
    date.getUTCDate(),
    date.getUTCMonth() + 1,
    date.getUTCFullYear(),
  ].join('.');
}

test.describe(RELAY_RACE_WORKFLOW.name, () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ request }) => {
    state.run = createWorkflowRun('relay-race-flow');
    state.memberToken = await loginViaApi(request, TEST_USERS.member);
    state.memberUser = await getCurrentUser(request, state.memberToken);

    state.mockRace = await createOrisMockRace(request, {
      name: `Playwright ORIS mock relay ${state.run.runId}`,
      place: 'Playwright relay arena',
      levelIds: RELAY_RACE_WORKFLOW.levelIds,
      classes: RELAY_RACE_WORKFLOW.categories.map((Name) => ({ Name, Fee: 150 })),
    });
    state.orisId = String(state.mockRace.race.ID);
  });

  test('registrar can load the mockup relay race into members', async ({ page, request }) => {
    await loginAs(page, 'registrar');

    state.race = await ensureOrisRace(page, state.orisId);
    const orisEvent = await getOrisApiEvent(request, state.orisId);
    const detail = await getRaceDetail(request, state.race.id);

    expect(Number(state.race.extId)).toBeGreaterThanOrEqual(25000);
    expect(Number(state.race.extId)).toBeLessThanOrEqual(999999);
    expect(state.race.extId).toBe(state.orisId);
    expect(state.race.name).toBe(state.mockRace.race.Name);
    expect(state.race.id).toBeTruthy();
    expect(orisEvent.Level.ID).toBe(RELAY_RACE_WORKFLOW.levelIds);
    expect(orisEvent.Classes.map((raceClass) => raceClass.Name).sort())
      .toEqual([...RELAY_RACE_WORKFLOW.categories].sort());
    expect(detail.categories.sort()).toEqual([...RELAY_RACE_WORKFLOW.categories].sort());
    expect(RELAY_RACE_WORKFLOW.rankings.reduce((flags, ranking) => flags | ranking.id, 0)).toBe(33);
    expect(detail.rankings.sort())
      .toEqual(RELAY_RACE_WORKFLOW.rankings.map((ranking) => ranking.name).sort());
  });

  test('registrar can ensure the configured participants locally', async ({ page, request }) => {
    await loginAs(page, 'registrar');

    if (!state.race) {
      state.race = await ensureOrisRace(page, state.orisId);
    }

    state.participants = await ensureRaceParticipants(
      page,
      state.race.id,
      RELAY_RACE_WORKFLOW.participants
    );

    expect(state.participants['8001']).toBeTruthy();
    expect(state.participants['6107']).toBeTruthy();
    expect(await getOrisApiEventEntries(request, state.orisId)).toEqual([]);
  });

  test('member can register to the imported mockup relay race locally', async ({ page, request }) => {
    if (!state.race) {
      await loginAs(page, 'registrar');
      state.race = await ensureOrisRace(page, state.orisId);
    }

    await loginAs(page, 'member');
    await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);
    await expect(page.locator('body')).toContainText(state.race.name);

    await submitMemberRaceRegistration(page, {
      id_us: String(state.memberUser.user_id),
      id_zav: String(state.race.id),
      novy: '1',
      kat: RELAY_RACE_WORKFLOW.memberCategory,
      pozn: `member relay note ${state.run.runId}`,
      pozn2: 'member relay internal',
    });

    await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);
    await expect(page.locator('input[name="kat"]')).toHaveValue(RELAY_RACE_WORKFLOW.memberCategory);

    const detail = await getRaceDetail(request, state.race.id);
    const entry = detail.everyone.find((item) => item.user_id === state.memberUser.user_id);

    expect(entry).toBeTruthy();
    expect(entry.category).toBe(RELAY_RACE_WORKFLOW.memberCategory);
    expect(await getOrisApiEventEntries(request, state.orisId)).toEqual([]);
  });

  test('ORIS API contains no member or participant race entries', async ({ request }) => {
    expect(await getOrisApiEventEntries(request, state.orisId)).toEqual([]);
  });

  test('member can unregister from the mockup relay race locally', async ({ page, request }) => {
    await loginAs(page, 'member');
    await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);
    await expect(page.getByRole('button', { name: 'Odhlásit ze závodu' })).toBeVisible();

    page.once('dialog', (dialog) => dialog.accept());
    await Promise.all([
      page.waitForURL(/us_race_regoff_exc\.php/),
      page.getByRole('button', { name: 'Odhlásit ze závodu' }).click(),
    ]);

    const detail = await getRaceDetail(request, state.race.id);
    const localEntry = detail.everyone.find((item) => item.user_id === state.memberUser.user_id);
    expect(localEntry).toBeUndefined();
    expect(await getOrisApiEventEntries(request, state.orisId)).toEqual([]);
  });

  test('registrar can move registration dates to past', async ({ page }) => {
    const oneMonthAgo = new Date();
    oneMonthAgo.setUTCMonth(oneMonthAgo.getUTCMonth() - 1);

    const oneWeekAgo = new Date();
    oneWeekAgo.setUTCDate(oneWeekAgo.getUTCDate() - 7);

    const oneDayAgo = new Date();
    oneDayAgo.setUTCDate(oneDayAgo.getUTCDate() - 1);

    state.expiredEntryDates = {
      first: formatSavedRaceDate(oneMonthAgo),
      second: formatSavedRaceDate(oneWeekAgo),
      third: formatSavedRaceDate(oneDayAgo),
    };

    await loginAs(page, 'registrar');
    await updateRace(page, state.race.id, {
      prihlasky1: state.expiredEntryDates.first,
      prihlasky2: state.expiredEntryDates.second,
      prihlasky3: state.expiredEntryDates.third,
    });

    await page.goto(`./race_edit.php?id=${state.race.id}`);
    await expect(page.locator('input[name="prihlasky1"]')).toHaveValue(state.expiredEntryDates.first);
    await expect(page.locator('input[name="prihlasky2"]')).toHaveValue(state.expiredEntryDates.second);
    await expect(page.locator('input[name="prihlasky3"]')).toHaveValue(state.expiredEntryDates.third);
  });

  test('member cannot register after the mockup relay race deadline', async ({ page, request }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    const raceRow = page.locator('a.adr_name', { hasText: state.race.name }).locator('xpath=ancestor::tr[1]');
    await expect(raceRow).toBeVisible();
    await expect(raceRow.getByText('Přihl.', { exact: true })).toHaveCount(0);
    await expect(raceRow.getByText('Zobrazit', { exact: true })).toBeVisible();

    const detail = await getRaceDetail(request, state.race.id);
    expect(detail.everyone.find((item) => item.user_id === state.memberUser.user_id)).toBeUndefined();
    expect(await getOrisApiEventEntries(request, state.orisId)).toEqual([]);
  });

  test('accountant can create and update a payment for another racer', async ({ page }) => {
    await loginAs(page, 'accountant');

    const financePopup = await openRaceFinancePopup(page, state.race.id);

    try {
      const otherRacersForm = financePopup.locator('form', {
        has: financePopup.locator('h3', { hasText: 'Ostatní závodníci' }),
      });
      const firstMemberRow = otherRacersForm.locator('tr', {
        has: financePopup.locator('a.adr_name'),
      }).first();
      const memberName = (await firstMemberRow.locator('td').nth(1).innerText()).trim();

      await firstMemberRow.locator('input[data-col="amount"]').fill('222');
      await firstMemberRow.locator('input[data-col="note"]').fill('Test nová platba');

      await Promise.all([
        financePopup.waitForURL(new RegExp(`race_finance_view\\.php\\?race_id=${state.race.id}.*status=ok`)),
        otherRacersForm.locator('input[type="submit"][value="Vytvořit nové platby"]').click(),
      ]);

      const createdPayment = financeRow(financePopup, memberName);
      await expectFinanceRowValues(createdPayment, {
        amount: '222',
        note: 'Test nová platba',
      });

      await createdPayment.amount.fill('234');
      await createdPayment.note.fill('Test změna platby');

      await Promise.all([
        financePopup.waitForURL(new RegExp(`race_finance_view\\.php\\?race_id=${state.race.id}.*status=ok`)),
        financePopup.locator('input[type="submit"][value="Změnit platby"]').click(),
      ]);

      await expectFinanceRowValues(financeRow(financePopup, memberName), {
        amount: '234',
        note: 'Test změna platby',
      });
    } finally {
      await financePopup.close();
    }
  });
});
