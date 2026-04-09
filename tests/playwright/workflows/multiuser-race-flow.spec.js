const { test, expect } = require('@playwright/test');
const { getTestMemberFixture } = require('../constants/members');
const { TEST_USERS } = require('../constants/users');
const {
  getCurrentUser,
  getManagingUsers,
  getRaceDetail,
  loginViaApi,
} = require('../helpers/api');
const {
  loginAs,
} = require('../helpers/browser');
const {
  ensureClubMembers,
  createPaymentRule,
  createRace,
  findRaceUserIdByReg,
  submitManagedRaceRegistration,
  submitMemberRaceRegistration,
  updateRace,
} = require('../helpers/app-actions');
const {
  addUtcDays,
  createWorkflowRun,
  formatCzDate,
} = require('../helpers/workflow-runtime');

function financeRow(page, memberName) {
  const row = page.locator('tr', {
    has: page.locator('a.adr_name', { hasText: memberName }),
  }).first();

  return {
    row,
    state: row.locator('.state'),
    amount: row.locator('input[data-col="amount"]'),
    note: row.locator('input[data-col="note"]'),
    entryFee: row.locator('[data-col="entryFee"]'),
    transport: row.locator('[data-col="transport"]'),
    accommodation: row.locator('[data-col="accommodation"]'),
  };
}

async function expectFinanceRowValues(finance, expected) {
  await expect(finance.state).toHaveText(expected.state);
  await expect(finance.amount).toHaveValue(expected.amount);
  await expect(finance.note).toHaveValue(expected.note);
  await expect(finance.entryFee).toContainText(expected.entryFee);
  await expect(finance.transport).toHaveText(expected.transport);
  await expect(finance.accommodation).toHaveText(expected.accommodation);
}

test.describe('Multi-User Race Workflow', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ browser, request }) => {
    state.run = createWorkflowRun('multi-user-race-flow');

    const today = new Date();
    const entryDate = formatCzDate(addUtcDays(today, 7));
    const raceDate = formatCzDate(addUtcDays(today, 14));

    state.managerRaceUser = getTestMemberFixture('8511');
    if (!state.managerRaceUser) {
      throw new Error('Missing test member fixture for registration id 8511');
    }
    state.managerRaceUser2 = getTestMemberFixture('8200');
    if (!state.managerRaceUser2) {
      throw new Error('Missing test member fixture for registration id 8200');
    }
    state.registrarRaceUser = getTestMemberFixture('7755');
    if (!state.registrarRaceUser) {
      throw new Error('Missing test member fixture for registration id 7755');
    }

    state.labels = {
      memberNote: `member-note-${state.run.runId}`,
      managerRaceUserNote: `manager-user-note-${state.run.runId}`,
      registrarRaceUserNote: `registrar-user-note-${state.run.runId}`,
      managerNote: `manager-note-${state.run.runId}`,
      raceName: `PW Race ${state.run.runId}`,
      raceNoteInitial: `Initial workflow note ${state.run.runId}`,
      raceNoteUpdated: `Updated workflow note ${state.run.runId}`,
      raceNameUpdated: `PW Race ${state.run.runId} v2`,
    };

    state.memberToken = await loginViaApi(request, TEST_USERS.member);
    state.memberUser = await getCurrentUser(request, state.memberToken);

    state.smallManagerToken = await loginViaApi(request, TEST_USERS.smallManager);
    state.smallManagerUser = await getCurrentUser(request, state.smallManagerToken);
    state.smallManagerManagedUsers = await getManagingUsers(request, state.smallManagerToken);
    state.smallManagerManagedChild = state.smallManagerManagedUsers.find(
      (user) => user.user_id !== state.smallManagerUser.user_id
    );

    if (!state.smallManagerManagedChild) {
      throw new Error('The seeded smallManager user has no managed child for workflow coverage');
    }

    const accountantContext = await browser.newContext();
    const accountantPage = await accountantContext.newPage();
    await loginAs(accountantPage, 'accountant');
    await createPaymentRule(accountantPage);
    await accountantContext.close();

    const managerContext = await browser.newContext();
    const managerPage = await managerContext.newPage();
    await loginAs(managerPage, 'manager');
    await ensureClubMembers(managerPage, [state.managerRaceUser.reg,state.managerRaceUser2.reg,state.registrarRaceUser.reg], {
      role: 'manager',
    });

    const registrarContext = await browser.newContext();
    const registrarPage = await registrarContext.newPage();
    await loginAs(registrarPage, 'registrar');
    state.race = await createRace(registrarPage, request, {
      name: state.labels.raceName,
      note: state.labels.raceNoteInitial,
      entryDate1: entryDate,
      date: raceDate,
      eventType: 'T',
      transport: '3'
    });

    state.registrarRaceUserId = await findRaceUserIdByReg(registrarPage, state.race.id, {
      groupId: 400,
      reg: state.registrarRaceUser.reg,
    });

    if (!state.registrarRaceUserId) {
      throw new Error(`Registrar could not find race user with reg ${state.registrarRaceUser.reg}`);
    }

    await submitManagedRaceRegistration(registrarPage, state.race.id, {
      groupId: 400,
      user_id: String(state.registrarRaceUserId),
      kateg: 'D21',
      pozn: state.labels.registrarRaceUserNote,
      pozn2: 'registrar setup',
      sedadel: -1
    });

    await registrarContext.close();

    state.managerRaceUserId = await findRaceUserIdByReg(managerPage, state.race.id, {
      groupId: 500,
      reg: state.managerRaceUser.reg,
    });

    if (!state.managerRaceUserId) {
      throw new Error(`Manager could not find race user with reg ${state.managerRaceUser.reg}`);
    }

    await submitManagedRaceRegistration(managerPage, state.race.id, {
      groupId: 500,
      user_id: String(state.managerRaceUserId),
      kateg: 'H21',
      pozn: state.labels.managerRaceUserNote,
      pozn2: 'manager setup',
      sedadel: 4
    });

    state.managerRaceUserId = await findRaceUserIdByReg(managerPage, state.race.id, {
      groupId: 500,
      reg: state.managerRaceUser2.reg,
    });

    if (!state.managerRaceUserId) {
      throw new Error(`Manager could not find race user with reg ${state.managerRaceUser2.reg}`);
    }    

    await submitManagedRaceRegistration(managerPage, state.race.id, {
      groupId: 500,
      user_id: String(state.managerRaceUserId),
      kateg: 'H35',
      pozn: state.labels.managerRaceUserNote,
      pozn2: 'manager setup',
    });

    await managerContext.close();
  });

  test('member can register using the created race id', async ({ page, request }) => {
    await loginAs(page, 'member');
    await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);

    await expect(page.locator('body')).toContainText(state.labels.raceName);

    await submitMemberRaceRegistration(page, {
      id_us: String(state.memberUser.user_id),
      id_zav: String(state.race.id),
      novy: '1',
      kat: 'D21',
      pozn: state.labels.memberNote,
      pozn2: 'member internal',
    });

    await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);
    await expect(page.locator('input[name="kat"]')).toHaveValue('D21');
    await expect(page.locator('input[name="pozn"]')).toHaveValue(state.labels.memberNote);

    const detail = await getRaceDetail(request, state.race.id);
    const entry = detail.everyone.find((item) => item.user_id === state.memberUser.user_id);

    expect(entry).toBeTruthy();
    expect(entry.category).toBe('D21');
    expect(entry.note).toBe(state.labels.memberNote);
  });

  test('registrar can modify the created race after member registration', async ({ page }) => {
    await loginAs(page, 'registrar');

    await updateRace(page, state.race.id, {
      nazev: state.labels.raceName,
      poznamka: state.labels.raceNoteUpdated,
    });

    await page.goto(`./race_edit.php?id=${state.race.id}`);
    await expect(page.locator('textarea[name="poznamka"]')).toHaveValue(state.labels.raceNoteUpdated);
  });

  test('member sees the registrar updates on the same race id', async ({ page }) => {
    await loginAs(page, 'member');
    await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);

    await expect(page.locator('body')).toContainText(state.labels.raceNoteUpdated);
    await expect(page.locator('input[name="kat"]')).toHaveValue('D21');
    await expect(page.locator('input[name="pozn"]')).toHaveValue(state.labels.memberNote);
  });

  test('small manager can use the same shared race context', async ({ page, request }) => {
    await loginAs(page, 'smallManager');
    await page.goto(`./race_regs_1.php?gr_id=600&id=${state.race.id}&show_ed=1`);

    await expect(page.locator('body')).toContainText(state.labels.raceName);
    await expect(page.locator('select[name="user_id"]')).toBeVisible();

    await submitManagedRaceRegistration(page, state.race.id, {
      user_id: String(state.smallManagerManagedChild.user_id),
      kateg: 'T1',
      pozn: state.labels.managerNote,
      pozn2: 'manager internal',
    });

    const detail = await getRaceDetail(request, state.race.id);
    const entry = detail.everyone.find((item) => item.user_id === state.smallManagerManagedChild.user_id);

    expect(entry).toBeTruthy();
    expect(entry.category).toBe('T1');
    expect(entry.note).toBe(state.labels.managerNote);
  });

  test('registrar can perform a second modification on the same race id', async ({ page, request }) => {
    await loginAs(page, 'registrar');

    await updateRace(page, state.race.id, {
      nazev: state.labels.raceNameUpdated,
      poznamka: state.labels.raceNoteUpdated,
    });

    const detail = await getRaceDetail(request, state.race.id);

    expect(detail.name).toBe(state.labels.raceNameUpdated);
  });

  test('accountant can apply payrule wizard on race finance view', async ({ page }) => {
    await loginAs(page, 'accountant');
    await page.goto(`./race_finance_view.php?race_id=${state.race.id}`);

    await page.locator('button[title="Vyplň platby podle pravidel"]').click();

    await expectFinanceRowValues(financeRow(page, 'Coufalová Martina'), {
      state: '🪄',
      amount: '145',
      note: '+45 startovné+100 doprava',
      entryFee: '45',
      transport: '✔+100',
      accommodation: '',
    });    

    await expectFinanceRowValues(financeRow(page, 'Drábek Jan'), {
      state: '🪄',
      amount: '45',
      note: '+45 startovné',
      entryFee: '45',
      transport: '🚗',
      accommodation: '',
    });

    await expectFinanceRowValues(financeRow(page, 'Koča Jaroslav'), {
      state: '📌',
      amount: '',
      note: '',
      entryFee: '',
      transport: '',
      accommodation: '',
    });

  });
});
