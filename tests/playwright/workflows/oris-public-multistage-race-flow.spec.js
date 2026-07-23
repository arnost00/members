const { test, expect } = require('@playwright/test');
const {
  loginAs,
} = require('../helpers/browser');
const {
  ensurePaymentRules,
  ensureClubMembers,
  updateRace,
} = require('../helpers/app-actions');
const {
  POPULATED_PAYMENT_RULES,
} = require('../constants/payment-rules');
const {
  ensureOrisRace,
  ensureRaceParticipants,
} = require('../helpers/oris-race-workflow');
const {
  addUtcDays,
  expandYear,
  formatCzDate,
} = require('../helpers/workflow-runtime');
const {
  expectFinanceRowValues,
  financeRow,
  openRaceFinancePopup,
  runRaceFinanceWizard,
} = require('../helpers/race-finance');

const ORIS_PUBLIC_MULTISTAGE_RACE_WORKFLOW = {
  name: 'Oris Public Multistage Race Workflow',
  orisId: '9026',
  members: ['0953', '6700', '9711'],
  raceSetup: {
    transport: '1',
    accommodation: '1',
  },
  participants: {
    '0953': {
      kateg: 'D16',
      transport: 1,
      ubytovani: 1,
    },
    '6700': {
      kateg: 'H35',
      transport: 1,
      ubytovani: 1,
    },
    '9711': {
      kateg: 'H21A',
    },
  },
  accountantWizardChecks: {
    'Kelbl Vladimír': {
      state: '🪄',
      amount: '837',
      note: '+837 startovné',
      entryFee: '837',
      transport: '',
      accommodation: '',
    },
    'Coufalová Rea': {
      state: '📌',
      note: '',
      amount: '',
      transport: '✔',
      accommodation: '✔',
    },
  },
};

async function readRaceDateFields(page, raceId) {
  await page.goto(`./race_edit.php?id=${raceId}`);

  return page.evaluate(() => {
    const inputValue = (name) => {
      const input = document.querySelector(`[name="${name}"]`);
      return input ? input.value : '';
    };

    return {
      datum: inputValue('datum'),
      datum2: inputValue('datum2'),
      entryStart: inputValue('entryStart'),
      prihlasky1: inputValue('prihlasky1'),
    };
  });
}

test.describe(ORIS_PUBLIC_MULTISTAGE_RACE_WORKFLOW.name, () => {
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

  test('registrar can ensure the ORIS public multistage race exists locally', async ({ page }) => {
    await loginAs(page, 'registrar');

    state.race = await ensureOrisRace(page, ORIS_PUBLIC_MULTISTAGE_RACE_WORKFLOW.orisId);

    expect(state.race.id).toBeTruthy();
    expect(state.race.extId).toBe(ORIS_PUBLIC_MULTISTAGE_RACE_WORKFLOW.orisId);
    expect(state.race.name).toBeTruthy();
    expect(state.race.place).toBeTruthy();
    expect(state.race.club).toBeTruthy();
  });

  test('club admin can ensure the configured members exist', async ({ page, browser }) => {
    await loginAs(page, 'clubAdmin');

    const accountantContext = await browser.newContext();
    const accountantPage = await accountantContext.newPage();

    try {
      await loginAs(accountantPage, 'accountant');

      state.members = await ensureClubMembers(page, ORIS_PUBLIC_MULTISTAGE_RACE_WORKFLOW.members, {
        financePage: accountantPage,
      });
    } finally {
      await accountantContext.close();
    }

    expect(state.members).toHaveLength(3);
  });

  test('registrar can enable transport and accommodation and ensure the configured participants', async ({ page }) => {
    await loginAs(page, 'registrar');

    if (!state.race) {
      state.race = await ensureOrisRace(page, ORIS_PUBLIC_MULTISTAGE_RACE_WORKFLOW.orisId);
    }

    const previousRaceDates = await readRaceDateFields(page, state.race.id);
    const now = new Date();

    await updateRace(page, state.race.id, {
      datum: formatCzDate(addUtcDays(now, 3)),
      datum2: formatCzDate(addUtcDays(now, 5)),
      entryStart: formatCzDate(addUtcDays(now, 1)) + ` 21:00:00`,
      prihlasky1: formatCzDate(addUtcDays(now, 2)),
    });

    try {
      await updateRace(page, state.race.id, ORIS_PUBLIC_MULTISTAGE_RACE_WORKFLOW.raceSetup);
      state.participants = await ensureRaceParticipants(
        page,
        state.race.id,
        ORIS_PUBLIC_MULTISTAGE_RACE_WORKFLOW.participants,
        {expectedOutcome: 'message'}
      );
    } finally {
      await updateRace(page, state.race.id, previousRaceDates);
      state.race.date = previousRaceDates.datum;
    }

    expect(state.participants['0953']).toBeTruthy();
    expect(state.participants['6700']).toBeTruthy();
    expect(state.participants['9711']).toBeTruthy();
    expect(state.participants['0953'].lastColumnText).toContain('🕒');
    expect(state.participants['6700'].lastColumnText).toContain('🕒');
    expect(state.participants['9711'].lastColumnText).toContain('🕒');
  });

  test('accountant can inspect the race wizard without overwriting seeded finance rows', async ({ page }) => {
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

      for (const [memberName, expectedValues] of Object.entries(ORIS_PUBLIC_MULTISTAGE_RACE_WORKFLOW.accountantWizardChecks)) {
        await expectFinanceRowValues(financeRow(financePopup, memberName), expectedValues);
      }
    } finally {
      await financePopup.close();
    }
  });
});
