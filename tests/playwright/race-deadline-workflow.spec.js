const { test, expect } = require('@playwright/test');
const { execFileSync } = require('node:child_process');
const path = require('node:path');
const { loginAs, postFormInSession, readFormState } = require('./helpers/browser');
const { login } = require('./components/login');

const RACE_ID = 24010;
const RACE_NAME = `PW deadline workflow ${RACE_ID}`;
const ROLE_ROUTES = {
  member: './index.php?id=200&subid=2',
  smallManager: './index.php?id=600&subid=2',
  manager: './index.php?id=500&subid=2',
  registrar: './index.php?id=400&subid=1',
};

function fixture(action = 'read', fields) {
  return JSON.parse(execFileSync('php', ['-d', 'short_open_tag=1', path.join(__dirname, 'helpers/deadline-workflow-fixture.php')], {
    input: JSON.stringify({ action, fields }), encoding: 'utf8', stdio: ['pipe', 'pipe', 'pipe'],
  }));
}

const now = () => Math.floor(Date.now() / 1000);
function czParts(timestamp) {
  return Object.fromEntries(new Intl.DateTimeFormat('en-GB', {
    timeZone: 'Europe/Prague', year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23',
  }).formatToParts(new Date(timestamp * 1000)).map((part) => [part.type, part.value]));
}
function cz(timestamp) {
  const p = czParts(timestamp);
  return `${p.day}.${p.month}.${p.year} ${p.hour}:${p.minute}:${p.second}`;
}
function czDate(timestamp) {
  const p = czParts(timestamp);
  return `${p.day}.${p.month}.${p.year}`;
}
function raceRow(page) {
  return page.getByRole('link', { name: RACE_NAME, exact: true }).locator('xpath=ancestor::tr[1]');
}
function serviceIndicator(row, letter) {
  return row.locator('span[title]').filter({ hasText: new RegExp(`^${letter}$`) });
}

async function openRolePages(browser) {
  const roles = {};
  for (const role of Object.keys(ROLE_ROUTES)) {
    const context = await browser.newContext({ timezoneId: 'Europe/Prague' });
    const page = await context.newPage();
    await loginAs(page, role);
    roles[role] = { context, page };
  }
  return roles;
}

async function replaceMemberSession(browser, roles, loginName) {
  await roles.member.context.close();
  const context = await browser.newContext({ timezoneId: 'Europe/Prague' });
  const page = await context.newPage();
  await login(page, loginName);
  roles.member = { context, page };
}

async function refreshRows(roles) {
  const rows = {};
  for (const [role, session] of Object.entries(roles)) {
    await session.page.goto(ROLE_ROUTES[role]);
    rows[role] = raceRow(session.page);
    await expect(rows[role]).toBeVisible();
  }
  return rows;
}

async function expectRegistrationDisplay(rows, { date, exact = null, transport = 'none', accommodation = 'none' }) {
  for (const [role, row] of Object.entries(rows)) {
    await expect(row).toContainText(date);
    const clock = row.locator('[aria-label^="Přesný termín:"]');
    if (exact === null) {
      await expect(clock).toHaveCount(0);
    } else {
      await expect(row.locator(`[aria-label*="${exact}"]`)).toHaveCount(1);
    }
    for (const [letter, state] of [['D', transport], ['U', accommodation]]) {
      const indicator = serviceIndicator(row, letter);
      if (state === 'none') {
        await expect(indicator, `${role} ${letter} indicator`).toHaveCount(0);
      } else {
        await expect(indicator, `${role} ${letter} indicator`).toHaveCount(1);
        await expect(indicator.locator('s'), `${role} ${letter} indicator state`).toHaveCount(state === 'closed' ? 1 : 0);
      }
    }
  }
}

async function expectMemberActions(rows, expected = []) {
  for (const role of ['member', 'smallManager', 'manager']) {
    for (const letter of ['D', 'U']) {
      await expect(rows[role].getByRole('link', { name: letter, exact: true }), `${role} ${letter} action`).toHaveCount(expected.includes(letter) ? 1 : 0);
    }
  }
}

async function managedDialog(page, groupId, member, requireMember) {
  await page.goto(`./race_regs_1.php?gr_id=${groupId}&id=${RACE_ID}&show_ed=1`);
  const select = page.locator('select[name=user_id]');
  const option = select.locator(`option[value="${member}"]`);
  if (requireMember) await expect(option).toHaveCount(1);
  if (await option.count()) {
    await select.selectOption(String(member));
  }
  return page;
}

async function expectDialogFields(roles, member, expected) {
  await roles.member.page.goto(`./us_race_regon.php?id_zav=${RACE_ID}&id_us=${member}`);
  const requireMember = expected.dialog !== false;
  const dialogs = {
    member: roles.member.page,
    smallManager: await managedDialog(roles.smallManager.page, 600, member, requireMember),
    manager: await managedDialog(roles.manager.page, 500, member, requireMember),
    registrar: await managedDialog(roles.registrar.page, 400, member, true),
  };
  for (const [role, page] of Object.entries(dialogs)) {
    const override = role === 'registrar';
    if (expected.dialog === false && !override) {
      if (role === 'member') {
        await expect(page.locator('input[name=kat]')).toBeDisabled();
        await expect(page.locator('input[type=submit]')).toBeDisabled();
      } else {
        await expect(page.locator('input[name=kateg]')).toHaveCount(0);
      }
      continue;
    }
    for (const [field, selector] of Object.entries({
      registration: 'input[name=kat], input[name=kateg]',
      transport: 'select[name=sedadel], input[name=transport]',
      accommodation: 'input[name=ubytovani]',
    })) {
      const control = page.locator(selector).first();
      const shouldExist = expected[field] || override;
      if (!shouldExist) {
        await expect(control).toBeVisible();
        await expect(control).toBeDisabled();
      } else {
        await expect(control).toBeVisible();
        await expect(control).toBeEnabled();
      }
    }
  }
}

test.describe('complete local race deadline workflow', () => {
  test.describe.configure({ mode: 'serial' });
  test.afterEach(() => fixture('cleanup'));

  test('registration, accommodation and transport deadlines remain independent across roles', async ({ page, browser }) => {
    test.setTimeout(180000);
    fixture('prepare');
    await loginAs(page, 'registrar');

    const tomorrow = now() + 86400;
    const firstDate = czDate(tomorrow);
    const firstEndOfDay = `${firstDate} 23:59:59`;

    await test.step('registrar creates a local race with an end-of-day deadline', async () => {
      await page.goto('./race_new.php?type=0');
      const form = await readFormState(page);
      const result = await postFormInSession(page, './race_new_exc.php?rtype=0', {
        ...form.fields,
        nazev: RACE_NAME,
        datum: czDate(now() + 7 * 86400),
        datum2: '',
        typ: 'ob', typ0: 'Z', oddil: 'TST', misto: 'Brno', ranking: '0', etap: 1,
        prihlasky1: firstEndOfDay, prihlasky2: '', prihlasky3: '', prihlasky4: '', prihlasky5: '',
        ext_id: '', transport: 3, accommodation: 1, transport_do: '', ubytovani_do: '', kategorie: 'H21;H35',
      });
      expect(result.status, result.text).toBe(200);
      const state = fixture('claim_created');
      expect(state.race).toBeTruthy();
      expect(state.race.ext_id || null).toBeNull();
    });

    const state = fixture();
    await page.goto(`./us_race_regon.php?id_zav=${RACE_ID}&id_us=${state.member}`);
    let result = await postFormInSession(page, './us_race_regon_exc.php', {
      id_zav: RACE_ID, id_us: state.member, novy: 1, kat: 'H35', sedadel: 2, ubytovani: 1,
    });
    expect(result.status, result.text).toBe(200);

    const roles = await openRolePages(browser);
    try {
      let rows = await refreshRows(roles);
      await expectRegistrationDisplay(rows, { date: firstDate });
      await expectMemberActions(rows);

      const exactRegistration = fixture().race.prihlasky1 - 1;
      fixture('patch', { prihlasky1: exactRegistration });
      rows = await refreshRows(roles);
      await expectRegistrationDisplay(rows, { date: czDate(exactRegistration - 86400), exact: cz(exactRegistration) });

      const accommodationOpen = now() + 600;
      fixture('patch', { ubytovani_do: accommodationOpen });
      rows = await refreshRows(roles);
      await expectRegistrationDisplay(rows, { date: czDate(exactRegistration - 86400), exact: cz(exactRegistration), accommodation: 'open' });

      const transportOpen = now() + 600;
      fixture('patch', { transport_do: transportOpen });
      rows = await refreshRows(roles);
      await expectRegistrationDisplay(rows, { date: czDate(exactRegistration - 86400), exact: cz(exactRegistration), transport: 'open', accommodation: 'open' });
      await expectDialogFields(roles, state.member, { registration: true, transport: true, accommodation: true });

      fixture('patch', { transport_do: now() - 120 });
      rows = await refreshRows(roles);
      await expectRegistrationDisplay(rows, { date: czDate(exactRegistration - 86400), exact: cz(exactRegistration), transport: 'closed', accommodation: 'open' });
      await expectDialogFields(roles, state.member, { registration: true, transport: false, accommodation: true });

      const firstClosed = now() - 60;
      fixture('patch', { prihlasky1: firstClosed });
      rows = await refreshRows(roles);
      await expectRegistrationDisplay(rows, { date: czDate(firstClosed - 86400), exact: cz(firstClosed), transport: 'closed', accommodation: 'open' });
      await expectMemberActions(rows, ['U']);
      await expectDialogFields(roles, state.member, { registration: false, transport: false, accommodation: true });

      fixture('patch', { ubytovani_do: now() - 60 });
      rows = await refreshRows(roles);
      await expectMemberActions(rows);
      await expect(rows.manager.getByRole('link', { name: 'Př-1', exact: true })).toHaveCount(0);
      await expect(rows.registrar.getByRole('link', { name: 'P.1', exact: true })).toBeVisible();
      await expectDialogFields(roles, state.member, { dialog: false, registration: false, transport: false, accommodation: false });

      fixture('patch', { transport_do: null, ubytovani_do: null });
      rows = await refreshRows(roles);
      await expectMemberActions(rows);
      await expectDialogFields(roles, state.member, { dialog: false, registration: false, transport: false, accommodation: false });

      const second = now() + 600;
      fixture('patch', { prihlasky: 2, prihlasky2: second });
      rows = await refreshRows(roles);
      await expectRegistrationDisplay(rows, { date: czDate(second - 86400), exact: cz(second), transport: 'closed', accommodation: 'closed' });
      await roles.member.page.goto(`./us_race_regon.php?id_zav=${RACE_ID}&id_us=${state.member}`);
      await expect(roles.member.page.locator('input[name=kat]')).toBeDisabled();
      await roles.smallManager.page.goto(`./race_regs_1.php?gr_id=600&id=${RACE_ID}&show_ed=1`);
      await expect(roles.smallManager.page.locator(`select[name=user_id] option[value="${state.member}"]`)).toHaveCount(0);

      const secondServiceDeadline = now() + 600;
      fixture('patch', { transport_do: secondServiceDeadline, ubytovani_do: secondServiceDeadline });
      rows = await refreshRows(roles);
      await expect(rows.member.getByRole('link', { name: 'D', exact: true })).toBeVisible();
      await expect(rows.member.getByRole('link', { name: 'U', exact: true })).toBeVisible();

      await replaceMemberSession(browser, roles, 'tnov_5_2');
      await expectDialogFields(roles, state.secondMember, { registration: true, transport: true, accommodation: true });

      rows = await refreshRows(roles);
      await expectRegistrationDisplay(rows, { date: czDate(second - 86400), exact: cz(second), transport: 'open', accommodation: 'open' });
      await expectMemberActions(rows);
      await expectDialogFields(roles, state.secondMember, { registration: true, transport: true, accommodation: true });
      fixture('patch', { transport_do: null, ubytovani_do: null });

      const third = now() + 600;
      fixture('patch', { prihlasky2: now() - 60, prihlasky: 3, prihlasky3: third });
      rows = await refreshRows(roles);
      await expectRegistrationDisplay(rows, { date: czDate(third - 86400), exact: cz(third), transport: 'closed', accommodation: 'closed' });
      await expectDialogFields(roles, state.secondMember, { registration: true, transport: false, accommodation: false });

      const thirdServiceDeadline = now() + 600;
      fixture('patch', { transport_do: thirdServiceDeadline, ubytovani_do: thirdServiceDeadline });
      rows = await refreshRows(roles);
      await expectRegistrationDisplay(rows, { date: czDate(third - 86400), exact: cz(third), transport: 'open', accommodation: 'open' });
      await expectMemberActions(rows);
      await expectDialogFields(roles, state.secondMember, { registration: true, transport: true, accommodation: true });
      fixture('patch', { transport_do: null, ubytovani_do: null });

      fixture('patch', { prihlasky3: now() - 60 });
      await expectDialogFields(roles, state.secondMember, { dialog: false, registration: false, transport: false, accommodation: false });

      await replaceMemberSession(browser, roles, 'tnov_5');

      fixture('patch', { ubytovani_do: now() + 600 });
      rows = await refreshRows(roles);
      await expectMemberActions(rows, ['U']);
      await expectDialogFields(roles, state.member, { registration: false, transport: false, accommodation: true });

      fixture('patch', { ubytovani_do: now() - 60, transport_do: now() + 600 });
      rows = await refreshRows(roles);
      await expectMemberActions(rows, ['D']);
      await expectDialogFields(roles, state.member, { registration: false, transport: true, accommodation: false });

    } finally {
      await Promise.all(Object.values(roles).map(({ context }) => context.close()));
    }
  });
});
