const { test, expect } = require('@playwright/test');
const { TEST_USERS } = require('./constants/users');
const {
  getCurrentUser,
  getRaceDetail,
} = require('./helpers/api');
const {
  loginAs,
  openPopup,
} = require('./helpers/browser');
const {
  ensureClubMember,
  submitMemberRaceRegistration,
} = require('./helpers/app-actions');
const {
  createOrisMockRace,
  createOrisMockUser,
  deleteOrisMockRaceEntry,
  getOrisApiClubUserList,
  getOrisApiEvent,
  getOrisApiEventEntries,
  getOrisMockSettings,
  setOrisMockSettings,
  updateOrisMockRace,
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

function memberRow(page, reg) {
  return page
    .locator('td')
    .filter({ hasText: new RegExp(`^${reg}$`) })
    .first()
    .locator('xpath=ancestor::tr[1]');
}

function memberEntry(entries, state) {
  return entries.find((entry) => (
    String(entry.ClubUserID) === state.memberOrisClubUserId
    || entry.RegNo === state.memberRegNo
  ));
}

async function localMemberEntry(browser, state) {
  const detail = await getRaceDetail(browser, state.race.id);
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

async function ensureSeededRace(browser, state) {
  if (state.race) {
    return state.race;
  }

  const registrarContext = await browser.newContext();
  const registrarPage = await registrarContext.newPage();
  try {
    await loginAs(registrarPage, 'registrar');
    state.race = await ensureOrisRace(registrarPage, state.orisId);
    return state.race;
  } finally {
    await registrarContext.close();
  }
}

async function openSignupPopup(page, state) {
  await page.goto('./index.php?id=200&subid=2');
  return openPopup(page, () => page.evaluate(({ raceId, userId }) => {
    window.open(`./us_race_regon.php?id_zav=${raceId}&id_us=${userId}`, '');
  }, { raceId: state.race.id, userId: state.memberUser.user_id }));
}

async function submitSignupPopup(popup, category, note) {
  await popup.locator('input[name="kat"]').fill(category);
  await expect(popup.locator('input[name="kat"]')).toHaveValue(category);
  await popup.locator('input[name="pozn"]').fill(note);
  await Promise.all([
    popup.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    popup.locator('form[name="form1"] input[type="submit"]').click(),
  ]);
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
    currentSiReg: '9959',
    currentSiRegNo: 'ZBM9959',
    currentSiOrisUserId: '29959',
    currentSiOrisClubUserId: '39959',
    currentSi: '2181929',
    staleRegistrationSi: '999999',
  };

  let savedMockSettings;

  test.beforeAll(async ({ browser, request }) => {
    savedMockSettings = await getOrisMockSettings(request);
    await setOrisMockSettings(request, {
      mode: 'normal',
      clubUserListForbidden: false,
    });

    const run = createWorkflowRun('oris-connector-errors');
    state.runId = run.runId;
    state.memberUser = await getCurrentUser(browser, TEST_USERS.member);

    const clubAdminContext = await browser.newContext();
    const clubAdminPage = await clubAdminContext.newPage();
    await loginAs(clubAdminPage, 'clubAdmin');
    await ensureClubMember(clubAdminPage, {
      reg: state.currentSiReg,
      surname: 'Testovska',
      name: 'SiFallback9959',
      chip: state.currentSi,
      requireUnique: true,
      updateExisting: true,
    });
    await clubAdminContext.close();

    await createOrisMockUser(request, {
      userId: state.memberOrisUserId,
      clubUserId: state.memberOrisClubUserId,
      regNo: state.memberRegNo,
      firstName: state.memberUser.name || 'Zuzana',
      lastName: state.memberUser.surname || 'Novakova',
      si: state.memberUser.chip_number || '1341431',
      licence: 'C',
    });

    await createOrisMockUser(request, {
      userId: state.currentSiOrisUserId,
      clubUserId: state.currentSiOrisClubUserId,
      regNo: state.currentSiRegNo,
      firstName: 'SiFallback9959',
      lastName: 'Testovska',
      si: state.currentSi,
      regSi: state.staleRegistrationSi,
      licence: 'C',
    });

    const mockRace = await createOrisMockRace(request, {
      name: `Playwright ORIS connector errors ${run.runId}`,
      place: `Playwright ORIS error place ${run.runId}`,
      date: '2030-06-30',
      entryDate1: '2030-06-20 12:00:00',
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
    await setOrisMockSettings(request, {
      mode: 'normal',
      clubUserListForbidden: false,
    });
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

  test('getClubUserList failure can be simulated independently of other clubkey-protected methods', async ({ request }) => {
    await setOrisMockSettings(request, { clubUserListForbidden: true });

    const { httpStatus, body } = await getOrisApiClubUserList(request, 'mockClubKey');
    expect(httpStatus).toBe(200);
    expect(body.Status).toBe('Key not valid');
    expect(body.Data).toEqual([]);

    await setOrisMockSettings(request, { clubUserListForbidden: false });
    const restored = await getOrisApiClubUserList(request, 'mockClubKey');
    expect(restored.body.Status).toBe('OK');
  });

  test('members page falls back to the registration SI when getClubUserList fails', async ({ page, request }) => {
    await setOrisMockSettings(request, { clubUserListForbidden: true });

    await loginAs(page, 'clubAdmin');
    await page.goto('./index.php?id=700&subid=2');

    const row = memberRow(page, state.currentSiReg);
    await expect(row).toContainText(state.staleRegistrationSi);
    await expect(row.locator('a[href*="ads_oris_si_sync.php"]')).toHaveCount(1);
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

  test('signup popup stays open with a retryable warning when ORIS closes the connection', async ({ page, request, browser }) => {
    await ensureSeededRace(browser, state);

    await loginAs(page, 'member');
    let popup;
    let registrationSubmitted = false;
    try {
      popup = await openSignupPopup(page, state);
      await expect(popup.locator('input[name="kat"]')).toBeVisible();

      await setOrisMockSettings(request, { mode: 'close_connection' });
      await submitSignupPopup(popup, state.memberCategory, `popup retry ${state.runId}`);
      registrationSubmitted = true;

      await expect(popup.getByText('Synchronizace s ORIS se nezdařila (síťová chyba)')).toBeVisible();
      await expect(popup.getByRole('link', { name: 'Zpět na přehled' })).toBeVisible();
      expect(popup.isClosed()).toBe(false);
    } finally {
      await setOrisMockSettings(request, { mode: 'normal' });
      if (popup && !popup.isClosed()) {
        await popup.close();
      }
      if (registrationSubmitted) {
        await submitRegistration(
          page,
          state,
          `cleanup popup retry ${state.runId}`,
          'overview'
        );
        await deleteRegistration(page, state);
      }
    }
  });

  test('registration creation rolls back when the category is absent from ORIS', async ({ page, request, browser }) => {
    await ensureSeededRace(browser, state);
    await loginAs(page, 'member');

    const category = 'NO_ORIS';
    const popup = await openSignupPopup(page, state);
    await submitSignupPopup(popup, category, `missing category ${state.runId}`);

    await expect(popup.getByText('Chyba při synchronizaci s ORIS')).toBeVisible();
    await expect(popup.getByText(`Nelze spárovat kategorii '${category}' s ORISem.`)).toBeVisible();
    await popup.close();
    expect(await localMemberEntry(browser, state)).toBeUndefined();
    await expectRemoteMemberEntry(request, state, false);
  });

  test('registration creation rolls back when ORIS denies a valid category after its deadline', async ({ page, request, browser }) => {
    await ensureSeededRace(browser, state);
    const event = await getOrisApiEvent(request, state.orisId);
    const originalEntryDate1 = event.EntryDate1;
    try {
      await updateOrisMockRace(request, state.orisId, {
        entryDate1: '2020-06-20 12:00:00',
      });
      await loginAs(page, 'member');

      const popup = await openSignupPopup(page, state);
      await submitSignupPopup(popup, state.memberCategory, `ORIS denial ${state.runId}`);

      await expect(popup.getByText('Chyba při synchronizaci s ORIS')).toBeVisible();
      await expect(popup.getByText('Mimo termín přihlášek')).toBeVisible();
      await popup.close();
      expect(await localMemberEntry(browser, state)).toBeUndefined();
      await expectRemoteMemberEntry(request, state, false);
    } finally {
      await updateOrisMockRace(request, state.orisId, {
        entryDate1: originalEntryDate1,
      });
    }
  });

  for (const failure of TRANSIENT_FAILURES) {
    test(`registration creation remains pending for ${failure.name} and recovers`, async ({ page, request, browser }) => {
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
      expect(await localMemberEntry(browser, state)).toBeTruthy();
      await expectRemoteMemberEntry(request, state, false);

      await submitRegistration(
        page,
        state,
        `retry create ${failure.name} ${state.runId}`,
        'overview'
      );
      await expectRemoteMemberEntry(request, state, true);

      await deleteRegistration(page, state);
      expect(await localMemberEntry(browser, state)).toBeUndefined();
      await expectRemoteMemberEntry(request, state, false);
    });
  }

  for (const statusCode of CLIENT_ERROR_CODES) {
    test(`registration creation rolls back for client error ${statusCode}`, async ({ page, request, browser }) => {
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
      expect(await localMemberEntry(browser, state)).toBeUndefined();
      await expectRemoteMemberEntry(request, state, false);
    });
  }

  for (const failure of TRANSIENT_FAILURES) {
    test(`registration deletion remains retryable for ${failure.name} and recovers`, async ({ page, request, browser }) => {
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
      expect(await localMemberEntry(browser, state)).toBeTruthy();
      await expectRemoteMemberEntry(request, state, true);

      await deleteRegistration(page, state);
      expect(await localMemberEntry(browser, state)).toBeUndefined();
      await expectRemoteMemberEntry(request, state, false);
    });
  }

  for (const failure of TRANSIENT_FAILURES) {
    test(`registration deletion preserves the local entry after organiser removal during ${failure.name}`, async ({ page, request, browser }) => {
      if (failure.slow) test.slow();
      await ensureSeededRace(browser, state);
      await loginAs(page, 'member');
      try {
        await submitRegistration(
          page,
          state,
          `organiser removal ${failure.name} ${state.runId}`,
          'overview'
        );
        const entries = await getOrisApiEventEntries(request, state.orisId);
        const remoteEntry = memberEntry(entries, state);
        expect(remoteEntry).toBeTruthy();
        await deleteOrisMockRaceEntry(request, state.orisId, String(remoteEntry.ID));
        await expectRemoteMemberEntry(request, state, false);

        await setOrisMockSettings(request, failure.settings);
        await deleteRegistration(page, state, 'Zrušení v ORIS se nezdařilo (síťová chyba)');

        await setOrisMockSettings(request, { mode: 'normal' });
        expect(await localMemberEntry(browser, state)).toBeTruthy();
        await expectRemoteMemberEntry(request, state, false);

        await deleteRegistration(page, state);
        expect(await localMemberEntry(browser, state)).toBeUndefined();
        await expectRemoteMemberEntry(request, state, false);
      } finally {
        await setOrisMockSettings(request, { mode: 'normal' });
        if (await localMemberEntry(browser, state)) {
          await deleteRegistration(page, state);
        }
      }
    });
  }

  for (const statusCode of CLIENT_ERROR_CODES) {
    test(`registration deletion remains retryable for client error ${statusCode}`, async ({ page, request, browser }) => {
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
      expect(await localMemberEntry(browser, state)).toBeTruthy();
      await expectRemoteMemberEntry(request, state, true);

      await deleteRegistration(page, state);
      expect(await localMemberEntry(browser, state)).toBeUndefined();
      await expectRemoteMemberEntry(request, state, false);
    });
  }
});
