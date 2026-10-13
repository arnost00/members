const { test, expect } = require('@playwright/test');
const { DEFAULT_PASSWORD } = require('../constants/auth');
const { getRaceDetail } = require('../helpers/api');
const { loginAs, postFormInSession, readFormState } = require('../helpers/browser');
const { login } = require('../components/login');
const {
  createRace,
  ensureClubMember,
  ensureMemberLogin,
} = require('../helpers/app-actions');
const {
  addUtcDays,
  createWorkflowRun,
  formatCzDate,
} = require('../helpers/workflow-runtime');

// Issue #79: membership type (R regular / H hosting / T training) limits which events
// a member sees and can be signed up for.
const MEMBERS = {
  hosting: { reg: '9711', surname: 'Hostujici', name: 'Hana', login: 'tmt_host', type: 'H' },
  training: { reg: '9712', surname: 'Treninkovy', name: 'Tomas', login: 'tmt_tren', type: 'T' },
  regular: { reg: '9713', surname: 'Radny', name: 'Rudolf', login: 'tmt_radny', type: 'R' },
};

async function openMemberEditForm(page, userId) {
  await page.goto(`./user_edit.php?id=${userId}&cb=700`);
  return readFormState(page, 'form[action*="user_new_exc.php?update="]');
}

async function setMembershipType(page, userId, type) {
  const form = await openMemberEditForm(page, userId);
  const result = await postFormInSession(page, form.action, { ...form.fields, clenstvi: type });
  expect(result.ok).toBe(true);
}

async function readMembershipType(page, userId) {
  await page.goto(`./user_edit.php?id=${userId}&cb=700`);
  return page.locator('select[name="clenstvi"]').inputValue();
}

test.describe('Membership type workflow', () => {
  test.describe.configure({ mode: 'serial' });

  const state = { members: {} };

  test.beforeAll(async ({ browser }) => {
    state.run = createWorkflowRun('membership-type-flow');
    const context = await browser.newContext();
    const page = await context.newPage();
    await loginAs(page, 'clubAdmin');
    for (const [key, member] of Object.entries(MEMBERS)) {
      const ensured = await ensureClubMember(page, {
        reg: member.reg,
        surname: member.surname,
        name: member.name,
      });
      await ensureMemberLogin(page, ensured.userId, { login: member.login, password: DEFAULT_PASSWORD });
      state.members[key] = { ...member, userId: ensured.userId };
    }
    await context.close();
  });

  test('club admin sets the membership type', async ({ page }) => {
    await loginAs(page, 'clubAdmin');
    for (const member of Object.values(state.members)) {
      await setMembershipType(page, member.userId, member.type);
      expect(await readMembershipType(page, member.userId)).toBe(member.type);
    }

    await page.goto('./index.php?id=700&subid=1');
    const hostingRow = page.locator('table.ctmc tbody tr', { hasText: '9711' });
    await expect(hostingRow.locator('.membership-tag')).toHaveText('H');
    await expect(hostingRow.locator('.membership-tag')).toHaveAttribute('title', 'hostování');
    const regularRow = page.locator('table.ctmc tbody tr', { hasText: '9713' });
    await expect(regularRow.locator('.membership-tag')).toHaveCount(0);
  });

  test('manager edit form has no membership field and keeps the type', async ({ page }) => {
    await loginAs(page, 'manager');
    const userId = state.members.hosting.userId;
    await page.goto(`./user_edit.php?id=${userId}&cb=500`);
    await expect(page.locator('select[name="clenstvi"]')).toHaveCount(0);
    const form = await readFormState(page, 'form[action*="user_new_exc.php?update="]');
    const result = await postFormInSession(page, form.action, form.fields);
    expect(result.ok).toBe(true);

    await page.context().clearCookies();
    await loginAs(page, 'clubAdmin');
    expect(await readMembershipType(page, userId)).toBe('H');
  });

  test('member editing their own profile keeps the type', async ({ page }) => {
    await login(page, state.members.hosting.login, DEFAULT_PASSWORD);
    await page.goto('./index.php?id=200&subid=3');
    const form = await readFormState(page, 'form[action*="user_new_exc.php?update="]');
    const result = await postFormInSession(page, form.action, form.fields);
    expect(result.ok).toBe(true);

    await page.context().clearCookies();
    await loginAs(page, 'clubAdmin');
    expect(await readMembershipType(page, state.members.hosting.userId)).toBe('H');
  });

  test('invalid membership value falls back to regular', async ({ page }) => {
    await loginAs(page, 'clubAdmin');
    const userId = state.members.regular.userId;
    await setMembershipType(page, userId, "X' OR 1=1 -- ");
    expect(await readMembershipType(page, userId)).toBe('R');
  });

  test.describe('events', () => {
    test.beforeAll(async ({ browser }) => {
      const context = await browser.newContext();
      const page = await context.newPage();
      await loginAs(page, 'registrar');
      const today = new Date();
      const base = {
        date: formatCzDate(addUtcDays(today, 10)),
        entryDate1: formatCzDate(addUtcDays(today, 5)),
        categories: 'H21;D21;',
      };
      const defs = {
        training: { eventType: 'T' },
        camp: { eventType: 'S' },
        race: { eventType: 'Z' },
        relay: { eventType: 'Z', fields: { 'zebricek[5]': '1' } },
        medical: { eventType: 'V' },
      };
      state.races = {};
      for (const [key, def] of Object.entries(defs)) {
        const name = `PW clenstvi ${key} ${state.run.runId}`;
        const race = await createRace(page, { ...base, ...def, name });
        state.races[key] = { id: race.id, name };
      }
      await context.close();
    });

    async function memberRaceListText(page) {
      await page.goto('./index.php?id=200&subid=2');
      return page.locator('body').innerText();
    }

    async function memberNewsText(page) {
      await page.goto('./index.php?id=4');
      return page.locator('body').innerText();
    }

    const VISIBLE = {
      hosting: ['training', 'camp', 'relay', 'medical'],
      training: ['training', 'camp'],
      regular: ['training', 'camp', 'race', 'relay', 'medical'],
    };

    for (const [memberKey, visible] of Object.entries(VISIBLE)) {
      test(`${memberKey} member sees only allowed events`, async ({ page }) => {
        await page.context().clearCookies();
        await login(page, state.members[memberKey].login, DEFAULT_PASSWORD);
        const listText = await memberRaceListText(page);
        const newsText = await memberNewsText(page);
        for (const [raceKey, race] of Object.entries(state.races)) {
          const shouldSee = visible.includes(raceKey);
          expect(listText.includes(race.name), `list ${memberKey}/${raceKey}`).toBe(shouldSee);
          expect(newsText.includes(race.name), `news ${memberKey}/${raceKey}`).toBe(shouldSee);
        }
      });
    }

    test('forbidden event: form shows the message, forged POST creates no entry', async ({ page, browser }) => {
      const member = state.members.hosting;
      await page.context().clearCookies();
      await login(page, member.login, DEFAULT_PASSWORD);
      await page.goto(`./us_race_regon.php?id_zav=${state.races.race.id}&id_us=${member.userId}`);
      await expect(page.locator('body')).toContainText('Typ členství (hostování) neumožňuje přihlášku na tuto akci.');
      await expect(page.locator('form[name="form1"]')).toHaveCount(0);

      const result = await postFormInSession(page, './us_race_regon_exc.php', {
        id_us: String(member.userId), id_zav: String(state.races.race.id), novy: '1', kat: 'H21', pozn: '', pozn2: '',
      });
      expect(result.text).toContain('Typ členství (hostování) neumožňuje přihlášku na tuto akci.');
      const detail = await getRaceDetail(browser, state.races.race.id);
      expect(detail.everyone.find((entry) => entry.user_id === Number(member.userId))).toBeUndefined();
    });

    test('allowed event: hosting member can sign up for a relay', async ({ page, browser }) => {
      const member = state.members.hosting;
      await page.context().clearCookies();
      await login(page, member.login, DEFAULT_PASSWORD);
      await page.goto(`./us_race_regon.php?id_zav=${state.races.relay.id}&id_us=${member.userId}`);
      await expect(page.locator('form[name="form1"]')).toHaveCount(1);
      await postFormInSession(page, './us_race_regon_exc.php', {
        id_us: String(member.userId), id_zav: String(state.races.relay.id), novy: '1', kat: 'H21', pozn: '', pozn2: '',
      });
      const detail = await getRaceDetail(browser, state.races.relay.id);
      expect(detail.everyone.find((entry) => entry.user_id === Number(member.userId))?.category).toBe('H21');
    });

    test('existing signup survives a type change and can be edited and signed off', async ({ page, browser }) => {
      const member = state.members.regular;
      await page.context().clearCookies();
      await login(page, member.login, DEFAULT_PASSWORD);
      await postFormInSession(page, './us_race_regon_exc.php', {
        id_us: String(member.userId), id_zav: String(state.races.race.id), novy: '1', kat: 'H21', pozn: '', pozn2: '',
      });

      await page.context().clearCookies();
      await loginAs(page, 'clubAdmin');
      await setMembershipType(page, member.userId, 'T');

      await page.context().clearCookies();
      await login(page, member.login, DEFAULT_PASSWORD);
      await page.goto(`./us_race_regon.php?id_zav=${state.races.race.id}&id_us=${member.userId}`);
      await expect(page.locator('form[name="form1"]')).toHaveCount(1);
      await postFormInSession(page, './us_race_regon_exc.php', {
        id_us: String(member.userId), id_zav: String(state.races.race.id), novy: '1', kat: 'D21', pozn: '', pozn2: '',
      });
      let detail = await getRaceDetail(browser, state.races.race.id);
      expect(detail.everyone.find((entry) => entry.user_id === Number(member.userId))?.category).toBe('D21');

      await page.goto(`./us_race_regon.php?id_zav=${state.races.race.id}&id_us=${member.userId}`);
      page.on('dialog', (dialog) => dialog.accept());
      await Promise.all([
        page.waitForURL(/us_race_regoff_exc\.php/),
        page.getByRole('button', { name: 'Odhlásit ze závodu' }).click(),
      ]);
      detail = await getRaceDetail(browser, state.races.race.id);
      expect(detail.everyone.find((entry) => entry.user_id === Number(member.userId))).toBeUndefined();

      await page.context().clearCookies();
      await loginAs(page, 'clubAdmin');
      await setMembershipType(page, member.userId, 'R');
    });
  });
});
