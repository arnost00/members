const { test, expect } = require('@playwright/test');
const { TEST_USERS } = require('../constants/users');
const {
  getCurrentUser,
  getRaceDetail,
} = require('../helpers/api');
const { loginAs } = require('../helpers/browser');
const { submitMemberRaceRegistration } = require('../helpers/app-actions');
const {
  createOrisMockRace,
  createOrisMockUser,
  getOrisApiEventEntries,
} = require('../helpers/oris-mock');
const {
  ensureOrisRace,
  ensureRaceParticipants,
} = require('../helpers/oris-race-workflow');
const { createWorkflowRun } = require('../helpers/workflow-runtime');

// Issue #71: a category kept only in members (e.g. a coach travelling with the club
// without running) is never sent to ORIS.
const LOCAL_ONLY_CATEGORY_WORKFLOW = {
  name: 'Oris Local-Only Category Workflow',
  localOnlyCategory: 'Nebezim',
  orisCategory: 'D21',
  memberRegNo: 'ZBM9952',
  memberOrisUserId: '29952',
  memberOrisClubUserId: '39952',
  participantReg: '7203',
  participantOrisUser: {
    userId: '54452',
    clubUserId: '37517',
    regNo: 'ZBM7203',
    firstName: 'Radim',
    lastName: 'Cenek',
    si: '2181929',
    licence: 'C',
  },
};

function findMemberOrisEntry(entries) {
  return entries.find((entry) => (
    String(entry.ClubUserID) === LOCAL_ONLY_CATEGORY_WORKFLOW.memberOrisClubUserId
    || entry.RegNo === LOCAL_ONLY_CATEGORY_WORKFLOW.memberRegNo
  ));
}

test.describe(LOCAL_ONLY_CATEGORY_WORKFLOW.name, () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  async function openMemberRegistration(page) {
    await loginAs(page, 'member');
    await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);
    await expect(page.locator('body')).toContainText(state.race.name);
  }

  async function registerMember(page, category) {
    await openMemberRegistration(page);
    return submitMemberRaceRegistration(page, {
      id_us: String(state.memberUser.user_id),
      id_zav: String(state.race.id),
      novy: '1',
      kat: category,
      pozn: `local-only category ${state.run.runId}`,
      pozn2: '',
    });
  }

  async function expectMemberLocalCategory(browser, category) {
    const detail = await getRaceDetail(browser, state.race.id);
    const entry = detail.everyone.find((item) => item.user_id === state.memberUser.user_id);
    expect(entry).toBeTruthy();
    expect(entry.category).toBe(category);
  }

  test.beforeAll(async ({ browser, request }) => {
    state.run = createWorkflowRun('oris-local-only-category-flow');
    state.memberUser = await getCurrentUser(browser, TEST_USERS.member);

    await createOrisMockUser(request, {
      userId: LOCAL_ONLY_CATEGORY_WORKFLOW.memberOrisUserId,
      clubUserId: LOCAL_ONLY_CATEGORY_WORKFLOW.memberOrisClubUserId,
      regNo: LOCAL_ONLY_CATEGORY_WORKFLOW.memberRegNo,
      firstName: state.memberUser.name,
      lastName: state.memberUser.surname,
      si: state.memberUser.chip_number || '0',
      licence: 'C',
    });
    await createOrisMockUser(request, LOCAL_ONLY_CATEGORY_WORKFLOW.participantOrisUser);

    state.mockRace = await createOrisMockRace(request, {
      name: `Playwright ORIS local-only category ${state.run.runId}`,
      place: 'Playwright local-only arena',
      classes: [
        { Name: 'D21', Fee: 150 },
        { Name: 'H35', Fee: 150 },
      ],
    });
    state.orisId = String(state.mockRace.race.ID);
  });

  test('registrar can load the mockup race into members', async ({ page }) => {
    await loginAs(page, 'registrar');
    state.race = await ensureOrisRace(page, state.orisId);
    expect(state.race.extId).toBe(state.orisId);
  });

  test('member registration page offers the local-only category', async ({ page }) => {
    await openMemberRegistration(page);
    await page.getByRole('button', { name: LOCAL_ONLY_CATEGORY_WORKFLOW.localOnlyCategory, exact: true }).click();
    await expect(page.locator('input[name="kat"]')).toHaveValue(LOCAL_ONLY_CATEGORY_WORKFLOW.localOnlyCategory);
  });

  test('member can register in the local-only category without an ORIS entry', async ({ page, request, browser }) => {
    await registerMember(page, LOCAL_ONLY_CATEGORY_WORKFLOW.localOnlyCategory);

    await expectMemberLocalCategory(browser, LOCAL_ONLY_CATEGORY_WORKFLOW.localOnlyCategory);
    expect(findMemberOrisEntry(await getOrisApiEventEntries(request, state.orisId))).toBeUndefined();
  });

  test('member switching to an ORIS category creates the ORIS entry', async ({ page, request, browser }) => {
    await registerMember(page, LOCAL_ONLY_CATEGORY_WORKFLOW.orisCategory);

    await expectMemberLocalCategory(browser, LOCAL_ONLY_CATEGORY_WORKFLOW.orisCategory);
    const orisEntry = findMemberOrisEntry(await getOrisApiEventEntries(request, state.orisId));
    expect(orisEntry).toBeTruthy();
    expect(orisEntry.ClassDesc).toBe(LOCAL_ONLY_CATEGORY_WORKFLOW.orisCategory);
  });

  test('member switching back to the local-only category cancels the ORIS entry', async ({ page, request, browser }) => {
    await registerMember(page, LOCAL_ONLY_CATEGORY_WORKFLOW.localOnlyCategory);

    await expectMemberLocalCategory(browser, LOCAL_ONLY_CATEGORY_WORKFLOW.localOnlyCategory);
    expect(findMemberOrisEntry(await getOrisApiEventEntries(request, state.orisId))).toBeUndefined();
  });

  test('member can unregister from the local-only category', async ({ page, request, browser }) => {
    await openMemberRegistration(page);

    const dialogs = [];
    page.on('dialog', (dialog) => {
      dialogs.push(dialog.message());
      dialog.accept();
    });
    await Promise.all([
      page.waitForURL(/us_race_regoff_exc\.php/),
      page.getByRole('button', { name: 'Odhlásit ze závodu' }).click(),
    ]);

    expect(dialogs.filter((message) => message.includes('ORIS'))).toEqual([]);
    const detail = await getRaceDetail(browser, state.race.id);
    expect(detail.everyone.find((item) => item.user_id === state.memberUser.user_id)).toBeUndefined();
    expect(findMemberOrisEntry(await getOrisApiEventEntries(request, state.orisId))).toBeUndefined();
  });

  test('registrar can add a participant in the local-only category without an ORIS entry', async ({ page, request }) => {
    await loginAs(page, 'registrar');
    const participants = await ensureRaceParticipants(page, state.race.id, {
      [LOCAL_ONLY_CATEGORY_WORKFLOW.participantReg]: {
        kateg: LOCAL_ONLY_CATEGORY_WORKFLOW.localOnlyCategory,
      },
    });
    expect(participants[LOCAL_ONLY_CATEGORY_WORKFLOW.participantReg]).toBeTruthy();

    const entries = await getOrisApiEventEntries(request, state.orisId);
    expect(entries.find((entry) => (
      String(entry.ClubUserID) === LOCAL_ONLY_CATEGORY_WORKFLOW.participantOrisUser.clubUserId
      || entry.RegNo === LOCAL_ONLY_CATEGORY_WORKFLOW.participantOrisUser.regNo
    ))).toBeUndefined();
  });
});
