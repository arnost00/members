const { test, expect } = require('@playwright/test');
const { execFileSync } = require('node:child_process');
const path = require('node:path');
const { loginAs, readFormState, postFormInSession, ensureHtmlSubmission } = require('./helpers/browser');

function fixture(id, action = 'read', options = {}) {
  return JSON.parse(execFileSync('php', ['-d', 'short_open_tag=1', path.join(__dirname, 'helpers/no-race-services-fixture.php')], {
    input: JSON.stringify({ id, action, ...options }), encoding: 'utf8', stdio: ['pipe', 'pipe', 'pipe'],
  }));
}

function cz(timestamp) {
  const parts = Object.fromEntries(new Intl.DateTimeFormat('en-GB', {
    timeZone: 'Europe/Prague', year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23',
  }).formatToParts(new Date(Number(timestamp) * 1000)).map(part => [part.type, part.value]));
  return `${parts.day}.${parts.month}.${parts.year} ${parts.hour}:${parts.minute}:${parts.second}`;
}

async function expectNoServices(page) {
  await expect(page.locator('[name=transport], [name=accommodation], [name=ubytovani], [name=sedadel], [name=transport_do], [name=ubytovani_do]')).toHaveCount(0);
  await expect(page.getByText(/společn(?:á|é|ou|ého) doprav|sdílen(?:á|é) doprav|společn(?:é|ého) ubytování/i)).toHaveCount(0);
}

async function saveForm(page, selector, fields, label) {
  const state = await readFormState(page, selector);
  for (const field of ['transport', 'accommodation', 'transport_do', 'ubytovani_do']) {
    expect(state.fields).not.toHaveProperty(field);
  }
  ensureHtmlSubmission(await postFormInSession(page, state.action, { ...state.fields, ...fields }), label);
}

for (const type of [0, 1]) {
  test.describe(type ? 'multistage race' : 'single-day race', () => {
    test.describe.configure({ mode: 'default' });
    const id = 24000 + type;
    test.beforeEach(() => fixture(id, 'reset', { type, mode: 3 }));
    test.afterEach(() => fixture(id, 'cleanup'));

    test('creation omits services and retains the registration deadline', async ({ page }) => {
      const { race, createdName } = fixture(id);
      await loginAs(page, 'registrar');
      await page.goto(`./race_new.php?type=${type}`);
      await expectNoServices(page);
      await expect(page.locator('[name=prihlasky1]')).toBeVisible();
      await saveForm(page, 'form', {
        nazev: createdName, datum: cz(race.datum).split(' ')[0],
        datum2: type ? cz(race.datum2).split(' ')[0] : '', etap: type ? 3 : 1,
        typ: 'ob', typ0: 'Z', oddil: 'TST', misto: 'Brno', ranking: '0',
        prihlasky1: cz(race.prihlasky1), prihlasky2: '', prihlasky3: '', prihlasky4: '', prihlasky5: '',
        ext_id: '', kategorie: 'H21;H35',
      }, 'Create race without services');
      const { created } = fixture(id);
      expect(created).toBeTruthy();
      expect(Number(created.vicedenni)).toBe(type);
      expect(Number(created.etap)).toBe(type ? 3 : 1);
      expect(created.prihlasky1).toBe(race.prihlasky1);
      expect(Number(created.transport)).toBe(0);
      expect(Number(created.ubytovani)).toBe(0);
      expect(created.transport_do).toBeNull();
      expect(created.ubytovani_do).toBeNull();
      await page.goto(`./race_edit.php?id=${created.id}`);
      await expectNoServices(page);
      await expect(page.locator('[name=prihlasky1]')).toHaveValue(cz(race.prihlasky1));
    });

    test('editing suppresses existing services and saves ordinary changes', async ({ page }) => {
      const { race } = fixture(id);
      await loginAs(page, 'registrar');
      await page.goto(`./race_edit.php?id=${id}`);
      await expectNoServices(page);
      await expect(page.locator('[name=prihlasky1]')).toBeVisible();
      await expect(page.locator('[name=prihlasky1]')).toHaveValue(cz(race.prihlasky1));
      await saveForm(page, 'form[name=form2]', { misto: 'PW updated place' }, 'Edit race without services');
      const updated = fixture(id).race;
      expect(updated.misto).toBe('PW updated place');
      expect(updated.prihlasky1).toBe(race.prihlasky1);
      // Existing save semantics: omitted modes become zero, omitted deadlines are retained.
      expect(Number(updated.transport)).toBe(0);
      expect(Number(updated.ubytovani)).toBe(0);
      expect(updated.transport_do).toBe(race.transport_do);
      expect(updated.ubytovani_do).toBe(race.ubytovani_do);
      await page.goto(`./race_edit.php?id=${id}`);
      await expectNoServices(page);
      await expect(page.locator('[name=misto]')).toHaveValue('PW updated place');
      await expect(page.locator('[name=prihlasky1]')).toHaveValue(cz(race.prihlasky1));
    });
  });
}

for (const mode of [1, 2, 3]) {
  test.describe(`member registration with stored service mode ${mode}`, () => {
    const id = 24001 + mode;
    test.beforeEach(() => fixture(id, 'reset', { mode }));
    test.afterEach(() => fixture(id, 'cleanup'));
    test('registration works and listings omit service actions and deadlines', async ({ browser }) => {
      // Exercise header propagation for manually created contexts as well.
      const context = await browser.newContext();
      try {
        const page = await context.newPage();
        const { race, member } = fixture(id);
        await loginAs(page, 'member');
        await page.goto(`./us_race_regon.php?id_zav=${id}&id_us=${member}`);
        await expectNoServices(page);
        await expect(page.locator('[name=kat]')).toBeEnabled();
        await saveForm(page, 'form[name=form1]', { kat: 'H21' }, 'Register without services');
        const { entry } = fixture(id);
        expect(entry.kat).toBe('H21');
        // Legacy automatic participation follows registration even when its UI is hidden.
        expect(Number(entry.transport)).toBe(mode === 2 ? 1 : 0);
        expect(Number(entry.ubytovani)).toBe(mode === 2 ? 1 : 0);
        await page.goto(`./us_race_regon.php?id_zav=${id}&id_us=${member}`);
        await expectNoServices(page);
        await expect(page.locator('[name=kat]')).toHaveValue('H21');
        await page.goto('./index.php?id=200&subid=2');
        const row = page.getByRole('link', { name: race.nazev, exact: true }).locator('xpath=ancestor::tr[1]');
        await expect(row).toBeVisible();
        await expect(row.getByRole('link', { name: /^[DU]$/ })).toHaveCount(0);
        for (const deadline of [race.transport_do, race.ubytovani_do]) {
          await expect(row.locator(`[title*="${cz(deadline)}"]`)).toHaveCount(0);
          await expect(row).not.toContainText(cz(deadline));
        }
      } finally {
        await context.close();
      }
    });
  });
}
