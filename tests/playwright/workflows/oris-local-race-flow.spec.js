const { test, expect } = require('@playwright/test');
const {
  loginAs,
} = require('../helpers/browser');
const {
  ensureClubMembers,
  ensurePaymentRules,
} = require('../helpers/app-actions');
const {
  POPULATED_PAYMENT_RULES,
} = require('../constants/payment-rules');
const {
  ensureOrisRace,
  readRaceParticipants,
} = require('../helpers/oris-race-workflow');
const {
  expandYear,
} = require('../helpers/workflow-runtime');
const {
  expectFinanceRowValues,
  financeRow,
  openRaceFinancePopup,
  runRaceFinanceWizard,
} = require('../helpers/race-finance');

const ORIS_LOCAL_RACE_WORKFLOW = {
  name: 'Oris Local Race Workflow',
  orisId: '8971',
  members: ['0953', '8511'],
  participants: {
    '0953': {
      kateg: 'D16',
      transport: 1,
      ubytovani: 1,
      term: 1,
    },
    '8511': {
      kateg: 'H21',
      transport: 1,
      ubytovani: 1,
    },
  },
  accountantWizardChecks: {
    'Drábek Jan': {
      state: '🪄',
      amount: '139',
      note: '+139 startovné',
    },
    'Křístková Veronika': {
      state: '🪄',
      amount: '75',
      note: '+75 startovné',
    },
    'Coufalová Rea': {
      state: '📌',
      amount: '',
      note: '',
      transport: '✔',
      accommodation: '✔',
    },
  },
};

test.describe(ORIS_LOCAL_RACE_WORKFLOW.name, () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ browser }) => {
    const accountantContext = await browser.newContext();
    const accountantPage = await accountantContext.newPage();

    try {
      await loginAs(accountantPage, 'accountant');
      await ensurePaymentRules(accountantPage, POPULATED_PAYMENT_RULES);
    } finally {
      await accountantContext.close();
    }
  });

  test('club admin can ensure the configured members exist', async ({ page, browser }) => {
    await loginAs(page, 'clubAdmin');

    const accountantContext = await browser.newContext();
    const accountantPage = await accountantContext.newPage();

    try {
      await loginAs(accountantPage, 'accountant');

      state.members = await ensureClubMembers(page, ORIS_LOCAL_RACE_WORKFLOW.members, {
        financePage: accountantPage,
      });
    } finally {
      await accountantContext.close();
    }

    expect(state.members).toHaveLength(2);
  });

  test('registrar can ensure the ORIS race exists locally', async ({ page }) => {
    await loginAs(page, 'registrar');

    state.race = await ensureOrisRace(page, ORIS_LOCAL_RACE_WORKFLOW.orisId);

    expect(state.race.id).toBeTruthy();
    expect(state.race.extId).toBe(ORIS_LOCAL_RACE_WORKFLOW.orisId);
    expect(state.race.date).toBeTruthy();
    expect(state.race.name).toBeTruthy();
    expect(state.race.place).toBeTruthy();
    expect(state.race.club).toBeTruthy();
  });

  test('registrar can inspect the configured participants on the ORIS race', async ({ page }) => {
    await loginAs(page, 'registrar');

    if (!state.race) {
      state.race = await ensureOrisRace(page, ORIS_LOCAL_RACE_WORKFLOW.orisId);
    }

    state.participants = await readRaceParticipants(
      page,
      state.race.id,
      ORIS_LOCAL_RACE_WORKFLOW.participants
    );

    for (const [reg, expectedParticipant] of Object.entries(ORIS_LOCAL_RACE_WORKFLOW.participants)) {
      const participant = state.participants[reg];

      expect(participant).toBeTruthy();
      expect(participant.category).toBe(String(expectedParticipant.kateg));
      expect(participant.transport).toBe(Boolean(expectedParticipant.transport));
      expect(participant.accommodation).toBe(Boolean(expectedParticipant.ubytovani));

      if (expectedParticipant.term !== undefined) {
        expect(participant.term).toBe(String(expectedParticipant.term));
      }
    }
  });

  test('accountant can open the race wizard and inspect the expected members', async ({ page }) => {
    await loginAs(page, 'accountant');

    if (!state.race) {
      throw new Error('Race state is missing from the registrar setup step');
    }

    await page.goto('./index.php?id=800&subid=2&fC=1');
    await expandYear(page, 2025);
    await expect(page.locator('body')).toContainText(state.race.name);

    const financePopup = await openRaceFinancePopup(page, state.race.id);

    try {
      await expect(financePopup.locator('body')).toContainText(state.race.name);

      await runRaceFinanceWizard(financePopup);

      for (const [memberName, expectedValues] of Object.entries(ORIS_LOCAL_RACE_WORKFLOW.accountantWizardChecks)) {
        await expectFinanceRowValues(financeRow(financePopup, memberName), expectedValues);
      }
    } finally {
      await financePopup.close();
    }
  });
});
