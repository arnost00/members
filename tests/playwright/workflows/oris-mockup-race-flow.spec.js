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
  ensureClubMembers,
  submitMemberRaceRegistration,
  updateRace,
} = require('../helpers/app-actions');
const {
  createOrisMockRace,
  createOrisMockUser,
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

const ORIS_MOCKUP_RACE_WORKFLOW = {
  name: 'Oris Mockup Race Workflow',
  memberCategory: 'D21',
  memberRegNo: 'ZBM9952',
  memberOrisUserId: '29952',
  memberOrisClubUserId: '39952',
  members: ['7203'],
  participants: [{
    '8511': {
      kateg: 'H21',
    },
  },
  {
    '7203': {
      kateg: 'H35',
    },
  }],
  participantOrisUsers: [
    {
      userId: '28511',
      clubUserId: '38511',
      regNo: 'ZBM8511',
      firstName: 'Jan',
      lastName: 'Drabek',
      si: '49690',
      licence: 'C',
    },
    {
      userId: '54452',
      clubUserId: '37517',
      regNo: 'ZBM7203',
      firstName: 'Radim',
      lastName: 'Cenek',
      si: '2181929',
      licence: 'C',
    },
  ],
};

function participantConfigByReg(participants) {
  return Array.isArray(participants)
    ? Object.assign({}, ...participants)
    : participants;
}

function regSuffix(regNo) {
  return String(regNo).replace(/^[A-Z]+/, '');
}

function formatSavedRaceDate(date) {
  return [
    date.getUTCDate(),
    date.getUTCMonth() + 1,
    date.getUTCFullYear(),
  ].join('.');
}

test.describe(ORIS_MOCKUP_RACE_WORKFLOW.name, () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ request }) => {
    state.run = createWorkflowRun('oris-mockup-race-flow');
    state.memberToken = await loginViaApi(request, TEST_USERS.member);
    state.memberUser = await getCurrentUser(request, state.memberToken);

    await createOrisMockUser(request, {
      userId: ORIS_MOCKUP_RACE_WORKFLOW.memberOrisUserId,
      clubUserId: ORIS_MOCKUP_RACE_WORKFLOW.memberOrisClubUserId,
      regNo: ORIS_MOCKUP_RACE_WORKFLOW.memberRegNo,
      firstName: state.memberUser.name || 'Zuzana',
      lastName: state.memberUser.surname || 'Novakova',
      si: state.memberUser.chip_number || '1341431',
      licence: 'C',
    });

    for (const user of ORIS_MOCKUP_RACE_WORKFLOW.participantOrisUsers) {
      await createOrisMockUser(request, user);
    }

    state.mockRace = await createOrisMockRace(request, {
      name: `Playwright ORIS mockup ${state.run.runId}`,
      place: 'Playwright proxy arena',
      classes: [
        { Name: 'H21', Fee: 150 },
        { Name: 'D21', Fee: 150 },
        { Name: 'H35', Fee: 150 },
      ],
    });
    state.orisId = String(state.mockRace.race.ID);
  });

  test('registrar can load the mockup ORIS race into members', async ({ page, request }) => {
    await loginAs(page, 'registrar');

    state.race = await ensureOrisRace(page, state.orisId);
    const orisEvent = await getOrisApiEvent(request, state.orisId);

    expect(Number(state.race.extId)).toBeGreaterThanOrEqual(25000);
    expect(Number(state.race.extId)).toBeLessThanOrEqual(999999);
    expect(state.race.extId).toBe(state.orisId);
    expect(state.race.name).toBe(state.mockRace.race.Name);
    expect(state.race.id).toBeTruthy();
    expect(orisEvent.Classes.map((raceClass) => raceClass.Name).sort()).toEqual(['D21', 'H21', 'H35']);
  });

  test('club admin can ensure the configured mockup members exist', async ({ page }) => {
    await loginAs(page, 'clubAdmin');

    state.members = await ensureClubMembers(page, ORIS_MOCKUP_RACE_WORKFLOW.members);

    expect(state.members).toHaveLength(1);
    expect(state.members[0].reg).toBe('7203');
  });

  test('registrar can ensure the configured participants on the ORIS race', async ({ page }) => {
    await loginAs(page, 'registrar');

    if (!state.race) {
      state.race = await ensureOrisRace(page, state.orisId);
    }

    state.participants = await ensureRaceParticipants(
      page,
      state.race.id,
      ORIS_MOCKUP_RACE_WORKFLOW.participants
    );

    expect(state.participants['8511']).toBeTruthy();
    expect(state.participants['7203']).toBeTruthy();
  });

  test('member can register to the imported mockup ORIS race', async ({ page, request }) => {
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
      kat: ORIS_MOCKUP_RACE_WORKFLOW.memberCategory,
      pozn: `member mockup note ${state.run.runId}`,
      pozn2: 'member mockup internal',
    });

    await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);
    await expect(page.locator('input[name="kat"]')).toHaveValue(ORIS_MOCKUP_RACE_WORKFLOW.memberCategory);

    const detail = await getRaceDetail(request, state.race.id);
    const entry = detail.everyone.find((item) => item.user_id === state.memberUser.user_id);

    expect(entry).toBeTruthy();
    expect(entry.category).toBe(ORIS_MOCKUP_RACE_WORKFLOW.memberCategory);
  });

  test('ORIS API contains the member and participant race entries', async ({ request }) => {
    const entries = await getOrisApiEventEntries(request, state.orisId);
    const participants = participantConfigByReg(ORIS_MOCKUP_RACE_WORKFLOW.participants);
    const memberEntry = entries.find((entry) => (
      String(entry.ClubUserID) === ORIS_MOCKUP_RACE_WORKFLOW.memberOrisClubUserId
      || entry.RegNo === ORIS_MOCKUP_RACE_WORKFLOW.memberRegNo
    ));

    for (const user of ORIS_MOCKUP_RACE_WORKFLOW.participantOrisUsers) {
      const participantEntry = entries.find((entry) => (
        String(entry.ClubUserID) === user.clubUserId
        || entry.RegNo === user.regNo
      ));
      const participant = participants[regSuffix(user.regNo)];

      expect(participantEntry).toBeTruthy();
      expect(participantEntry.EventID).toBe(state.orisId);
      expect(participantEntry.RegNo).toBe(user.regNo);
      expect(participantEntry.ClassDesc).toBe(participant.kateg);
      expect(participantEntry.Class.Name).toBe(participant.kateg);
      expect(participantEntry.SI).toBe(user.si);
    }

    expect(memberEntry).toBeTruthy();
    expect(memberEntry.EventID).toBe(state.orisId);
    expect(memberEntry.RegNo).toBe(ORIS_MOCKUP_RACE_WORKFLOW.memberRegNo);
    expect(memberEntry.ClassDesc).toBe(ORIS_MOCKUP_RACE_WORKFLOW.memberCategory);
    expect(memberEntry.Class.ID).toBe(`${state.orisId}02`);
    expect(memberEntry.SI).toBe('1341431');
  });

  test('member can unregister from the mockup ORIS race', async ({ page, request }) => {
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

    const entries = await getOrisApiEventEntries(request, state.orisId);
    const memberEntry = entries.find((entry) => (
      String(entry.ClubUserID) === ORIS_MOCKUP_RACE_WORKFLOW.memberOrisClubUserId
      || entry.RegNo === ORIS_MOCKUP_RACE_WORKFLOW.memberRegNo
    ));
    expect(memberEntry).toBeUndefined();
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

  test('member cannot register after the mockup ORIS race deadline', async ({ page, request }) => {
    await loginAs(page, 'member');
    await page.goto('./index.php?id=200&subid=2');

    const raceRow = page.locator('a.adr_name', { hasText: state.race.name }).locator('xpath=ancestor::tr[1]');
    await expect(raceRow).toBeVisible();
    await expect(raceRow.getByText('Přihl.', { exact: true })).toHaveCount(0);
    await expect(raceRow.getByText('Zobrazit', { exact: true })).toBeVisible();

    const detail = await getRaceDetail(request, state.race.id);
    expect(detail.everyone.find((item) => item.user_id === state.memberUser.user_id)).toBeUndefined();

    const entries = await getOrisApiEventEntries(request, state.orisId);
    expect(entries.find((entry) => (
      String(entry.ClubUserID) === ORIS_MOCKUP_RACE_WORKFLOW.memberOrisClubUserId
      || entry.RegNo === ORIS_MOCKUP_RACE_WORKFLOW.memberRegNo
    ))).toBeUndefined();
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
