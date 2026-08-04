const { test, expect } = require('@playwright/test');
const { TEST_USERS } = require('./constants/users');
const {
  getCurrentUser,
  getRaceDetail,
  loginViaApi,
} = require('./helpers/api');
const {
  loginAs,
} = require('./helpers/browser');
const {
  submitMemberRaceRegistration,
} = require('./helpers/app-actions');
const {
  createOrisMockRace,
  createOrisMockUser,
  getOrisApiEventEntries,
  getOrisMockSettings,
  setOrisMockSettings,
} = require('./helpers/oris-mock');
const {
  ensureOrisRace,
  openOrisRaceImportPopup,
  readOrisRaceSummary,
} = require('./helpers/oris-race-workflow');
const { createWorkflowRun } = require('./helpers/workflow-runtime');

const CLIENT_ERROR_CODES = [400, 401, 403, 404, 429];
const TRANSIENT_FAILURES = [
  { name: 'a hung connection', settings: { mode: 'hang' }, slow: true },
  { name: 'a closed connection', settings: { mode: 'close_connection' } },
  { name: 'HTTP 503', settings: { mode: 'service_down', forceStatusCode: 503 } },
];

function memberEntry(entries, state) {
  return entries.find((entry) => (
    String(entry.ClubUserID) === state.memberOrisClubUserId
    || entry.RegNo === state.memberRegNo
  ));
}

async function localMemberEntry(request, state) {
  const detail = await getRaceDetail(request, state.race.id);
  return detail.everyone.find((entry) => entry.user_id === state.memberUser.user_id);
}

async function openRaceImportTimed(page, orisId) {
  const startedAt = Date.now();
  const popup = await openOrisRaceImportPopup(page, orisId);

  return {
    elapsedMs: Date.now() - startedAt,
    popup,
  };
}

async function expectRaceImportUnavailable(page, state) {
  await loginAs(page, 'registrar');
  const result = await openRaceImportTimed(page, state.orisId);
  const summary = await readOrisRaceSummary(result.popup);

  await expect(result.popup.locator('body')).toContainText('neplatné ID závodu');
  expect(summary.extId).toBe(state.orisId);
  expect(summary.date).toBe('');
  expect(summary.name).toBe('');
  expect(summary.place).toBe('');
  await result.popup.close();

  return result.elapsedMs;
}

async function submitRegistration(page, state, note, expectedOutcome) {
  await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);
  await expect(page.locator('input[name="kat"]')).toBeVisible();

  return submitMemberRaceRegistration(page, {
    kat: state.memberCategory,
    pozn: note,
    pozn2: `internal ${note}`,
  }, { expectedOutcome });
}

async function expectRemoteMemberEntry(request, state, expected) {
  const entries = await getOrisApiEventEntries(request, state.orisId);
  const entry = memberEntry(entries, state);

  if (expected) {
    expect(entry).toBeTruthy();
    expect(entry.ClassDesc).toBe(state.memberCategory);
  } else {
    expect(entry).toBeUndefined();
  }
}

async function deleteRegistration(page, state, expectedMessage) {
  await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.memberUser.user_id}`);
  const button = page.getByRole('button', { name: 'Odhlásit ze závodu' });
  await expect(button).toBeVisible();

  const dialogMessages = [];
  const acceptDialog = async (dialog) => {
    dialogMessages.push(dialog.message());
    await dialog.accept();
  };
  page.on('dialog', acceptDialog);

  await Promise.all([
    page.waitForURL(/us_race_regoff_exc\.php/),
    button.click(),
  ]);

  if (expectedMessage) {
    await expect.poll(
      () => dialogMessages.some((message) => message.includes(expectedMessage)),
      { timeout: 5000 }
    ).toBe(true);
  }

  page.removeListener('dialog', acceptDialog);

  return dialogMessages;
}

test.describe('Oris Connector Errors', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {
    memberCategory: 'D21C',
    memberRegNo: 'ZBM9952',
    memberOrisUserId: '29952',
    memberOrisClubUserId: '39952',
  };

  let savedMockSettings;

  test.beforeAll(async ({ request }) => {
    savedMockSettings = await getOrisMockSettings(request);
    await setOrisMockSettings(request, { mode: 'normal' });

    const run = createWorkflowRun('oris-connector-errors');
    state.runId = run.runId;
    state.memberToken = await loginViaApi(request, TEST_USERS.member);
    state.memberUser = await getCurrentUser(request, state.memberToken);

    await createOrisMockUser(request, {
      userId: state.memberOrisUserId,
      clubUserId: state.memberOrisClubUserId,
      regNo: state.memberRegNo,
      firstName: state.memberUser.name || 'Zuzana',
      lastName: state.memberUser.surname || 'Novakova',
      si: state.memberUser.chip_number || '1341431',
      licence: 'C',
    });

    const mockRace = await createOrisMockRace(request, {
      name: `Playwright ORIS connector errors ${run.runId}`,
      place: `Playwright ORIS error place ${run.runId}`,
      classes: [
        { Name: state.memberCategory, Fee: 150 },
        { Name: 'H21C', Fee: 150 },
      ],
    });

    state.orisId = String(mockRace.race.ID);
    state.raceName = mockRace.race.Name;
    state.racePlace = mockRace.race.Place;
  });

  test.afterEach(async ({ request }) => {
    await setOrisMockSettings(request, { mode: 'normal' });
  });

  test.afterAll(async ({ request }) => {
    if (savedMockSettings) {
      await setOrisMockSettings(request, savedMockSettings);
    }
  });

  test('race import times out gracefully when the ORIS mock hangs', async ({ page, request }) => {
    test.slow();
    await setOrisMockSettings(request, { mode: 'hang' });

    const elapsedMs = await expectRaceImportUnavailable(page, state);

    expect(elapsedMs).toBeGreaterThanOrEqual(29000);
  });

  test('race import fails gracefully when the ORIS mock closes the connection', async ({ page, request }) => {
    await setOrisMockSettings(request, { mode: 'close_connection' });

    await expectRaceImportUnavailable(page, state);
  });

  for (const statusCode of CLIENT_ERROR_CODES) {
    test(`race import fails gracefully for client error ${statusCode}`, async ({ page, request }) => {
      await setOrisMockSettings(request, {
        mode: 'force_client_error',
        forceStatusCode: statusCode,
      });

      await expectRaceImportUnavailable(page, state);
    });
  }

  test('race import fails gracefully while the ORIS service is down', async ({ page, request }) => {
    await setOrisMockSettings(request, { mode: 'service_down', forceStatusCode: 503 });

    await expectRaceImportUnavailable(page, state);
  });

  test('race import recovers and creates the seeded race locally', async ({ page, request }) => {
    await setOrisMockSettings(request, { mode: 'normal' });
    await loginAs(page, 'registrar');

    state.race = await ensureOrisRace(page, state.orisId);

    expect(state.race.name).toBe(state.raceName);
    expect(state.race.place).toBe(state.racePlace);
    expect(state.race.extId).toBe(state.orisId);
  });

  for (const failure of TRANSIENT_FAILURES) {
    test(`registration creation remains pending for ${failure.name} and recovers`, async ({ page, request }) => {
      if (failure.slow) test.slow();
      await loginAs(page, 'member');
      await setOrisMockSettings(request, failure.settings);

      const result = await submitRegistration(
        page,
        state,
        `create ${failure.name} ${state.runId}`,
        'message'
      );

      expect(result.text).toContain('Synchronizace s ORIS se nezdařila (síťová chyba)');

      await setOrisMockSettings(request, { mode: 'normal' });
      expect(await localMemberEntry(request, state)).toBeTruthy();
      await expectRemoteMemberEntry(request, state, false);

      await submitRegistration(
        page,
        state,
        `retry create ${failure.name} ${state.runId}`,
        'overview'
      );
      await expectRemoteMemberEntry(request, state, true);

      await deleteRegistration(page, state);
      expect(await localMemberEntry(request, state)).toBeUndefined();
      await expectRemoteMemberEntry(request, state, false);
    });
  }

  for (const statusCode of CLIENT_ERROR_CODES) {
    test(`registration creation rolls back for client error ${statusCode}`, async ({ page, request }) => {
      await loginAs(page, 'member');
      await setOrisMockSettings(request, {
        mode: 'force_client_error',
        forceStatusCode: statusCode,
      });

      const result = await submitRegistration(
        page,
        state,
        `create HTTP ${statusCode} ${state.runId}`,
        'message'
      );

      expect(result.text).toContain('Chyba při synchronizaci s ORIS');

      await setOrisMockSettings(request, { mode: 'normal' });
      expect(await localMemberEntry(request, state)).toBeUndefined();
      await expectRemoteMemberEntry(request, state, false);
    });
  }

  for (const failure of TRANSIENT_FAILURES) {
    test(`registration deletion remains retryable for ${failure.name} and recovers`, async ({ page, request }) => {
      if (failure.slow) test.slow();
      await loginAs(page, 'member');
      await submitRegistration(
        page,
        state,
        `delete setup ${failure.name} ${state.runId}`,
        'overview'
      );
      await expectRemoteMemberEntry(request, state, true);

      await setOrisMockSettings(request, failure.settings);
      await deleteRegistration(
        page,
        state,
        'Zrušení v ORIS se nezdařilo (síťová chyba)'
      );

      await setOrisMockSettings(request, { mode: 'normal' });
      expect(await localMemberEntry(request, state)).toBeTruthy();
      await expectRemoteMemberEntry(request, state, true);

      await deleteRegistration(page, state);
      expect(await localMemberEntry(request, state)).toBeUndefined();
      await expectRemoteMemberEntry(request, state, false);
    });
  }

  for (const statusCode of CLIENT_ERROR_CODES) {
    test(`registration deletion remains retryable for client error ${statusCode}`, async ({ page, request }) => {
      await loginAs(page, 'member');
      await submitRegistration(
        page,
        state,
        `delete setup HTTP ${statusCode} ${state.runId}`,
        'overview'
      );
      await expectRemoteMemberEntry(request, state, true);

      await setOrisMockSettings(request, {
        mode: 'force_client_error',
        forceStatusCode: statusCode,
      });
      const messages = await deleteRegistration(page, state, 'Chyba při synchronizaci s ORIS');
      expect(messages.some((message) => message.includes('Chyba při synchronizaci s ORIS'))).toBe(true);

      await setOrisMockSettings(request, { mode: 'normal' });
      expect(await localMemberEntry(request, state)).toBeTruthy();
      await expectRemoteMemberEntry(request, state, true);

      await deleteRegistration(page, state);
      expect(await localMemberEntry(request, state)).toBeUndefined();
      await expectRemoteMemberEntry(request, state, false);
    });
  }
});
