const { test, expect } = require('@playwright/test');
const { DEFAULT_PASSWORD } = require('../constants/auth');
const { TEST_USERS } = require('../constants/users');
const {
  getCurrentUser,
  getRaceDetail,
  loginViaApi,
} = require('../helpers/api');
const { login } = require('../components/login');
const { loginAs } = require('../helpers/browser');
const {
  ensureClubMembers,
  ensureMemberLogin,
  setMemberSmallManager,
  submitMemberRaceRegistration,
} = require('../helpers/app-actions');
const {
  createOrisMockRace,
  createOrisMockUser,
  deleteOrisMockRaceEntry,
  getOrisApiEvent,
  getOrisApiEventEntries,
  updateOrisMockRace,
} = require('../helpers/oris-mock');
const {
  ensureOrisRace,
  ensureRaceParticipants,
  removeRaceParticipant,
} = require('../helpers/oris-race-workflow');
const { createWorkflowRun } = require('../helpers/workflow-runtime');

const ORIS_MOCKUP_CONFLICT_WORKFLOW = {
  name: 'Oris Mockup Conflict Workflow',
  undefinedCategory: 'H18',
  definedCategory: 'H21',
  memberReg: '1005',
  memberLogin: 'tnov_5_2',
  memberRegNo: 'ZBM1005',
  memberOrisUserId: '721005',
  memberOrisClubUserId: '31005',
  user7203Reg: '7203',
  user7203RegNo: 'ZBM7203',
  user7203OrisUserId: '54452',
  user7203OrisClubUserId: '37517',
  user7203Category: 'H35',
};

function orisDateTimeDaysAgo(days) {
  const date = new Date();
  date.setUTCDate(date.getUTCDate() - days);
  date.setUTCHours(20, 0, 0, 0);
  return date.toISOString().slice(0, 19).replace('T', ' ');
}

async function expectMemberEntryAbsent(request, state) {
  const detail = await getRaceDetail(request, state.race.id);
  expect(detail.everyone.find((item) => item.user_id === state.memberUser.user_id)).toBeUndefined();

  const entries = await getOrisApiEventEntries(request, state.orisId);
  expect(entries.find((entry) => (
    String(entry.ClubUserID) === ORIS_MOCKUP_CONFLICT_WORKFLOW.memberOrisClubUserId
    || entry.RegNo === ORIS_MOCKUP_CONFLICT_WORKFLOW.memberRegNo
  ))).toBeUndefined();
}

test.describe(ORIS_MOCKUP_CONFLICT_WORKFLOW.name, () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ browser, request }) => {
    state.run = createWorkflowRun('oris-mockup-conflict-flow');
    const zuzanaToken = await loginViaApi(request, TEST_USERS.member);
    state.zuzanaUser = await getCurrentUser(request, zuzanaToken);

    const clubAdminContext = await browser.newContext();
    const clubAdminPage = await clubAdminContext.newPage();
    await loginAs(clubAdminPage, 'clubAdmin');
    const [petr, user7203] = await ensureClubMembers(clubAdminPage, [
      ORIS_MOCKUP_CONFLICT_WORKFLOW.memberReg,
      ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203Reg,
    ]);
    state.user7203 = user7203;
    await ensureMemberLogin(clubAdminPage, petr.userId, {
      login: ORIS_MOCKUP_CONFLICT_WORKFLOW.memberLogin,
      password: DEFAULT_PASSWORD,
    });
    await clubAdminContext.close();

    const managerContext = await browser.newContext();
    const managerPage = await managerContext.newPage();
    await loginAs(managerPage, 'manager');
    await setMemberSmallManager(managerPage, petr.userId, state.zuzanaUser.chief_id);
    await managerContext.close();

    state.memberToken = await loginViaApi(request, ORIS_MOCKUP_CONFLICT_WORKFLOW.memberLogin, DEFAULT_PASSWORD);
    state.memberUser = await getCurrentUser(request, state.memberToken);
    expect(state.memberUser.name).toBe('Petr');
    expect(state.memberUser.surname).toBe('Novák');
    expect(String(state.memberUser.registration_number)).toBe(ORIS_MOCKUP_CONFLICT_WORKFLOW.memberReg);
    expect(String(state.memberUser.chief_id)).toBe(String(state.zuzanaUser.chief_id));

    await createOrisMockUser(request, {
      userId: ORIS_MOCKUP_CONFLICT_WORKFLOW.memberOrisUserId,
      clubUserId: ORIS_MOCKUP_CONFLICT_WORKFLOW.memberOrisClubUserId,
      regNo: ORIS_MOCKUP_CONFLICT_WORKFLOW.memberRegNo,
      firstName: state.memberUser.name,
      lastName: state.memberUser.surname,
      si: state.memberUser.chip_number || '0',
      licence: 'C',
    });
    await createOrisMockUser(request, {
      userId: ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203OrisUserId,
      clubUserId: ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203OrisClubUserId,
      regNo: ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203RegNo,
      firstName: 'Radim',
      lastName: 'Cenek',
      si: '2181929',
      licence: 'C',
    });

    state.mockRace = await createOrisMockRace(request, {
      name: `Playwright ORIS mockup conflict ${state.run.runId}`,
      place: 'Playwright conflict arena',
      classes: [
        { Name: 'H21', Fee: 150 },
        { Name: 'H35', Fee: 150 },
      ],
    });
    state.orisId = String(state.mockRace.race.ID);
  });

  test('registrar can load the mockup conflict race into members', async ({ page, request }) => {
    await loginAs(page, 'registrar');
    state.race = await ensureOrisRace(page, state.orisId);

    const event = await getOrisApiEvent(request, state.orisId);
    expect(event.Classes.map((raceClass) => raceClass.Name)).not.toContain(ORIS_MOCKUP_CONFLICT_WORKFLOW.undefinedCategory);
    expect(event.Classes.map((raceClass) => raceClass.Name)).toContain(ORIS_MOCKUP_CONFLICT_WORKFLOW.definedCategory);
    expect(state.race.extId).toBe(state.orisId);
  });

  test('registrar can add user 7203 to the mockup conflict race', async ({ page, request }) => {
    await loginAs(page, 'registrar');
    const participants = await ensureRaceParticipants(page, state.race.id, {
      [ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203Reg]: {
        kateg: ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203Category,
      },
    });
    expect(participants[ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203Reg]).toBeTruthy();

    const entries = await getOrisApiEventEntries(request, state.orisId);
    const user7203Entry = entries.find((entry) => (
      String(entry.ClubUserID) === ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203OrisClubUserId
      || entry.RegNo === ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203RegNo
    ));
    expect(user7203Entry).toBeTruthy();
    expect(user7203Entry.ClassDesc).toBe(ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203Category);
    state.user7203EntryId = String(user7203Entry.ID);
  });

  test('member registration with an undefined H18 category is refused', async ({ page, request }) => {
    await login(page, ORIS_MOCKUP_CONFLICT_WORKFLOW.memberLogin, DEFAULT_PASSWORD);
    await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);
    await expect(page.locator('body')).toContainText(state.race.name);

    const result = await submitMemberRaceRegistration(page, {
      id_us: String(state.memberUser.user_id),
      id_zav: String(state.race.id),
      novy: '1',
      kat: ORIS_MOCKUP_CONFLICT_WORKFLOW.undefinedCategory,
      pozn: `undefined category ${state.run.runId}`,
      pozn2: '',
    }, {
      expectedOutcome: 'message',
    });

    expect(result.text).toContain('Chyba při synchronizaci s ORIS');
    expect(result.text).toContain(`Nelze spárovat kategorii '${ORIS_MOCKUP_CONFLICT_WORKFLOW.undefinedCategory}' s ORISem.`);
    await expectMemberEntryAbsent(request, state);
  });

  test('registrar cannot remove user 7203 after the API removes the ORIS entry', async ({ page, request }) => {
    await deleteOrisMockRaceEntry(request, state.orisId, state.user7203EntryId);

    const remoteEntries = await getOrisApiEventEntries(request, state.orisId);
    expect(remoteEntries.find((entry) => String(entry.ID) === state.user7203EntryId)).toBeUndefined();

    await loginAs(page, 'registrar');
    const result = await removeRaceParticipant(page, state.race.id, ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203Reg, {
      expectedOutcome: 'message',
    });

    expect(result.text).toContain('Chyby při synchronizaci s ORIS');
    expect(result.text).toContain('Entry not found');

    const detail = await getRaceDetail(request, state.race.id);
    const user7203LocalEntry = detail.everyone.find((entry) => String(entry.user_id) === String(state.user7203.userId));
    expect(user7203LocalEntry).toBeTruthy();
    expect(user7203LocalEntry.category).toBe(ORIS_MOCKUP_CONFLICT_WORKFLOW.user7203Category);
  });

  test('API changes the ORIS registration deadline to yesterday', async ({ request }) => {
    state.expiredDeadline = orisDateTimeDaysAgo(1);
    await updateOrisMockRace(request, state.orisId, {
      entryDate1: state.expiredDeadline,
    });

    const event = await getOrisApiEvent(request, state.orisId);
    expect(event.EntryDate1).toBe(state.expiredDeadline);
    expect(event.Classes.map((raceClass) => raceClass.Name)).toContain(ORIS_MOCKUP_CONFLICT_WORKFLOW.definedCategory);
    expect(event.Classes.map((raceClass) => raceClass.Name)).not.toContain(ORIS_MOCKUP_CONFLICT_WORKFLOW.undefinedCategory);
  });

  test('member registration with a defined category after the ORIS deadline is refused', async ({ page, request }) => {
    await login(page, ORIS_MOCKUP_CONFLICT_WORKFLOW.memberLogin, DEFAULT_PASSWORD);
    await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);
    await expect(page.locator('body')).toContainText(state.race.name);

    const result = await submitMemberRaceRegistration(page, {
      id_us: String(state.memberUser.user_id),
      id_zav: String(state.race.id),
      novy: '1',
      kat: ORIS_MOCKUP_CONFLICT_WORKFLOW.definedCategory,
      pozn: `expired deadline ${state.run.runId}`,
      pozn2: '',
    }, {
      expectedOutcome: 'message',
    });

    expect(result.text).toContain('Chyba při synchronizaci s ORIS');
    expect(result.text).toContain('Mimo termín přihlášek');
    await expectMemberEntryAbsent(request, state);
  });
});
