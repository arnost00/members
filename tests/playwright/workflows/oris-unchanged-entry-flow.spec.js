const { test, expect } = require('@playwright/test');
const { loginAs, postFormInSession, readFormState } = require('../helpers/browser');
const { ensureClubMember } = require('../helpers/app-actions');
const {
  clearOrisApiLog,
  createOrisMockRace,
  createOrisMockUser,
  getOrisApiEventEntries,
  getOrisApiLog,
} = require('../helpers/oris-mock');
const {
  ensureOrisRace,
  ensureRaceParticipants,
} = require('../helpers/oris-race-workflow');
const { createWorkflowRun } = require('../helpers/workflow-runtime');

// Issue #86: saving the bulk registration form must only send to ORIS the entries whose
// ORIS-visible data changed. The browser posts only changed rows; a whole-form post (no JS)
// sends everything as before and warns the user.
const MEMBERS = [
  { reg: '9741', surname: 'Nezmeneny', name: 'Norbert', orisUserId: '719741', clubUserId: '619741', kateg: 'H21' },
  { reg: '9742', surname: 'Zmeneny', name: 'Zdenek', orisUserId: '719742', clubUserId: '619742', kateg: 'H21' },
];

// The ORIS mock log is global (other workers may hit it), so only entry mutations
// naming our members are counted.
function entryMutations(log, clubUserIds) {
  return log.split('\n').filter(Boolean).filter((line) => {
    if (!/ method=(createEntry|updateEntry|deleteEntry) /.test(line)) {
      return false;
    }
    const params = new URLSearchParams(line.split(' params=')[1] || '');
    return clubUserIds.includes(params.get('clubuser'));
  });
}

test.describe('ORIS sync of unchanged entries', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ request }) => {
    state.run = createWorkflowRun('oris-unchanged-entry-flow');
    for (const member of MEMBERS) {
      await createOrisMockUser(request, {
        userId: member.orisUserId,
        clubUserId: member.clubUserId,
        regNo: `ZBM${member.reg}`,
        firstName: member.name,
        lastName: member.surname,
      });
    }
    state.mockRace = await createOrisMockRace(request, {
      name: `Playwright ORIS unchanged entries ${state.run.runId}`,
      place: 'Playwright unchanged arena',
      classes: [
        { Name: 'H21', Fee: 150 },
        { Name: 'H35', Fee: 150 },
      ],
    });
    state.orisId = String(state.mockRace.race.ID);
  });

  test('members are registered and synced to ORIS', async ({ page, request }) => {
    await loginAs(page, 'clubAdmin');
    for (const member of MEMBERS) {
      await ensureClubMember(page, {
        reg: member.reg,
        surname: member.surname,
        name: member.name,
        updateExisting: true,
      });
    }

    await page.context().clearCookies();

    await loginAs(page, 'registrar');
    state.race = await ensureOrisRace(page, state.orisId);
    await ensureRaceParticipants(page, state.race.id, MEMBERS.map((m) => ({ [m.reg]: { kateg: m.kateg } })));

    const entries = await getOrisApiEventEntries(request, state.orisId);
    for (const member of MEMBERS) {
      expect(entries.find((entry) => entry.RegNo === `ZBM${member.reg}`)).toBeTruthy();
    }
  });

  test('bulk form submits only changed rows', async ({ page }) => {
    await page.context().clearCookies();
    await loginAs(page, 'registrar');
    await page.goto(`./race_regs_all.php?gr_id=400&id=${state.race.id}`);
    const row = page.locator('tr').filter({ has: page.locator('td', { hasText: /^9742$/ }) });
    const katInput = row.locator('input[name^="kateg["]');
    const changedName = await katInput.getAttribute('name');
    await katInput.fill('H35');
    await katInput.fill(MEMBERS[1].kateg); // reverted - still unchanged

    const [submitted] = await Promise.all([
      page.waitForRequest((req) => req.url().includes('race_regs_all_exc.php') && req.method() === 'POST'),
      page.locator('input[type="submit"][value="Proveď změny"]').click(),
    ]);
    const body = new URLSearchParams(submitted.postData() || '');
    expect([...body.keys()].filter((key) => key.startsWith('kateg['))).toEqual([]);
    expect(body.get('only_changed')).toBe('1');

    await page.goto(`./race_regs_all.php?gr_id=400&id=${state.race.id}`);
    await page.locator(`input[name="${changedName}"]`).fill('H35');
    const [changed] = await Promise.all([
      page.waitForRequest((req) => req.url().includes('race_regs_all_exc.php') && req.method() === 'POST'),
      page.locator('input[type="submit"][value="Proveď změny"]').click(),
    ]);
    const changedBody = new URLSearchParams(changed.postData() || '');
    expect([...changedBody.keys()].filter((key) => key.startsWith('kateg['))).toEqual([changedName]);
    await page.waitForLoadState();

    // restore for the next test
    await ensureRaceParticipants(page, state.race.id, MEMBERS.map((m) => ({ [m.reg]: { kateg: m.kateg } })));
  });

  test('re-saving the bulk form sends only the changed entry to ORIS', async ({ page, request }) => {
    const clubUserIds = MEMBERS.map((m) => m.clubUserId);
    await page.context().clearCookies();
    await loginAs(page, 'registrar');

    // whole form posted (no JS row selection): everything goes to ORIS as before, with a warning
    await page.goto(`./race_regs_all.php?gr_id=400&id=${state.race.id}`);
    const form = await readFormState(page, 'form[name="form1"]');
    expect(form.fields.only_changed).toBe('0');
    await clearOrisApiLog(request);
    const posted = await postFormInSession(page, form.action, form.fields);
    expect(posted.ok).toBe(true);
    expect(posted.text).toContain('Formulář byl odeslán celý');
    const fullFormMutations = entryMutations(await getOrisApiLog(request), clubUserIds);
    expect(fullFormMutations).toHaveLength(2);
    expect(fullFormMutations.every((line) => line.includes('method=updateEntry'))).toBe(true);

    // the server still skips unchanged entries posted by the browser row selection
    await clearOrisApiLog(request);
    const selected = await postFormInSession(page, form.action, {
      ...form.fields,
      only_changed: '1',
    });
    expect(selected.ok).toBe(true);
    expect(selected.text).not.toContain('Formulář byl odeslán celý');
    expect(entryMutations(await getOrisApiLog(request), clubUserIds)).toEqual([]);

    await clearOrisApiLog(request);
    await ensureRaceParticipants(page, state.race.id, MEMBERS.map((m) => ({ [m.reg]: { kateg: m.kateg } })));
    expect(entryMutations(await getOrisApiLog(request), clubUserIds)).toEqual([]);

    // local-only fields (internal note) do not count as an ORIS change either
    await clearOrisApiLog(request);
    await ensureRaceParticipants(page, state.race.id, MEMBERS.map((m) => ({
      [m.reg]: { kateg: m.kateg, noteInternal: `internal ${state.run.runId}` },
    })));
    expect(entryMutations(await getOrisApiLog(request), clubUserIds)).toEqual([]);

    await clearOrisApiLog(request);
    await ensureRaceParticipants(page, state.race.id, [
      { [MEMBERS[0].reg]: { kateg: MEMBERS[0].kateg } },
      { [MEMBERS[1].reg]: { kateg: 'H35' } },
    ]);
    const mutations = entryMutations(await getOrisApiLog(request), clubUserIds);
    expect(mutations).toHaveLength(1);
    expect(mutations[0]).toContain('method=updateEntry');
    expect(mutations[0]).toContain(`clubuser=${MEMBERS[1].clubUserId}`);
  });
});
