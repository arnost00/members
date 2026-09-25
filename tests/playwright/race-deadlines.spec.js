const { test, expect } = require('@playwright/test');
const { execFileSync } = require('node:child_process');
const path = require('node:path');
const { loginAs, postFormInSession, readFormState } = require('./helpers/browser');

function fixture(id, action = 'read', fields) {
  return JSON.parse(execFileSync('php', ['-d', 'short_open_tag=1', path.join(__dirname, 'helpers/deadline-fixture.php')], {
    input: JSON.stringify({ id, action, fields }), encoding: 'utf8', stdio: ['pipe', 'pipe', 'pipe'],
  }));
}
const now = () => Math.floor(Date.now() / 1000);
function cz(timestamp) {
  const parts = Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Prague', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23' }).formatToParts(new Date(timestamp*1000)).map(p=>[p.type,p.value]));
  return `${parts.day}.${parts.month}.${parts.year} ${parts.hour}:${parts.minute}:${parts.second}`;
}

for (const [index, timezoneId] of ['America/Los_Angeles', 'Asia/Tokyo'].entries()) {
  test.describe(timezoneId, () => {
    test.describe.configure({ mode: 'serial' });
    test.use({ timezoneId });
    const id = 6200 + index;
    test.afterEach(() => fixture(id, 'cleanup'));
    test('exact cutoffs, stale requests, independent services and staff override', async ({ page, browser }) => {
      test.setTimeout(60000);
      const { member } = fixture(id, 'reset');
      await loginAs(page, 'member');
      await page.goto('./index.php?id=200&subid=2');
      const unregisteredRow = page.getByRole('link', { name:`PW exact deadlines ${id}`, exact:true }).locator('xpath=ancestor::tr[1]');
      await expect(unregisteredRow.getByRole('link', { name:/^[DU]$/ })).toHaveCount(0);
      await page.goto(`./us_race_regon.php?id_zav=${id}&id_us=${member}`);
      expect(await page.locator('input[name=kat]').isEnabled()).toBe(true);
      let result = await postFormInSession(page, './us_race_regon_exc.php', { id_zav:id, id_us:member, novy:1, kat:'H21', sedadel:3, ubytovani:1, pozn:'keep', pozn2:'internal' });
      expect(result.status, result.text).toBe(200);
      let stored = fixture(id);
      expect(stored.entry.kat).toBe('H21');
      expect(stored.entry.sedadel).toBe('3');
      const openRegistrationList = await page.context().newPage();
      await openRegistrationList.goto('./index.php?id=200&subid=2');
      const openRegistrationRow = openRegistrationList.getByRole('link', { name:`PW exact deadlines ${id}`, exact:true }).locator('xpath=ancestor::tr[1]');
      await expect(openRegistrationRow.getByRole('link', { name:/^[DU]$/ })).toHaveCount(0);
      await openRegistrationList.close();
      // The browser still holds the open form, but the server cutoff has passed.
      fixture(id, 'patch', { prihlasky1:now()-1, transport_do:now()-1, ubytovani_do:now()+3600 });
      result = await postFormInSession(page, './us_race_regon_exc.php', { id_zav:id, id_us:member, kat:'H35', sedadel:4, ubytovani:1 });
      expect(result.status).toBe(409);
      const previous = fixture(id).entry;
      result = await postFormInSession(page, './us_race_regon_exc.php', { id_zav:id, id_us:member, kat:'H35' });
      expect(result.status, result.text).toBe(200);
      stored = fixture(id);
      expect(stored.entry.kat).toBe('H21');
      expect(stored.entry.pozn).toBe('keep');
      expect(stored.entry.termin).toBe(previous.termin);
      expect(stored.entry.sync_status).toBe(previous.sync_status);
      expect(stored.entry.sedadel).toBe('3');
      expect(stored.entry.ubytovani).toBe('0');
      await page.goto(`./us_race_regon.php?id_zav=${id}&id_us=${member}`);
      await expect(page.locator('input[name=kat]')).toBeDisabled();
      await expect(page.locator('select[name=sedadel]')).toBeDisabled();
      await expect(page.locator('input[name=ubytovani]')).toBeEnabled();
      await expect(page.locator('fieldset[disabled]').first()).toHaveAttribute('title', /^Pouze do /);
      await expect(page.locator('fieldset').first()).toHaveCSS('border-top-width', '0px');
      await expect(page.locator('fieldset legend')).toHaveCount(0);
      await expect(page.getByRole('button',{name:'Odhlásit ze závodu'})).toBeDisabled();
      const cancelled = await page.request.get(`./us_race_regoff_exc.php?id_zav=${id}&id_us=${member}`);
      expect(cancelled.status()).toBe(409);
      await page.goto('./index.php?id=200&subid=2');
      const row = page.getByRole('link',{name:`PW exact deadlines ${id}`,exact:true}).locator('xpath=ancestor::tr[1]');
      const options = row.locator('td').filter({ has: page.getByRole('link', { name:'U', exact:true }) });
      await expect(options.getByRole('link', { name:'U', exact:true })).toHaveClass('Highlight');
      await expect(row.getByRole('link', { name:'U', exact:true })).toBeVisible();
      await expect(row.getByRole('link', { name:'D', exact:true })).toHaveCount(0);
      const popupPromise = page.waitForEvent('popup');
      await row.getByRole('link', { name:'U', exact:true }).click();
      const servicePopup = await popupPromise;
      await expect(servicePopup.locator('[name=kat]')).toBeDisabled();
      await expect(servicePopup.locator('[name=ubytovani]')).toBeEnabled();
      await expect(servicePopup.locator('[name=sedadel]')).toBeDisabled();
      await servicePopup.locator('[name=ubytovani]').check();
      await servicePopup.getByRole('button', {name:'Změnit údaje'}).click();
      await expect(servicePopup.locator('[name=ubytovani]')).toBeChecked();
      expect(fixture(id).entry.ubytovani).toBe('1');
      expect(fixture(id).entry.kat).toBe('H21');
      await servicePopup.close();
      fixture(id, 'patch', { transport_do:now()+3600, ubytovani_do:now()-1 });
      await page.reload();
      await expect(row.getByRole('link', { name:'D', exact:true })).toBeVisible();
      await expect(row.getByRole('link', { name:'U', exact:true })).toHaveCount(0);
      const apiBypass = await page.request.get(`./api_race_entry.php?id_race=${id}&id_user=${member}&action=entryByFin`);
      expect(apiBypass.status()).toBe(403);
      fixture(id, 'patch', { vedouci:member });
      const leaderAttendance = await page.request.get(`./api_race_entry.php?id_race=${id}&id_user=${member}&action=participate`);
      expect(leaderAttendance.status()).toBe(200);
      expect(await leaderAttendance.json()).toBe(1);
      const context = await browser.newContext();
      const staff = await context.newPage();
      try {
        fixture(id, 'patch', { ext_id:8971 });
        await loginAs(staff,'registrar');
        await staff.goto(`./race_regs_1.php?gr_id=400&id=${id}&show_ed=1`);
        result = await postFormInSession(staff, `./race_regs_1_exc.php?gr_id=400&id=${id}&show_ed=1`, { user_id:member, kateg:'H21', sedadel:4, ubytovani:1, new_termin:1 });
        expect(result.status, result.text).toBe(200);
        expect(result.text).toContain('změna nebyla odeslána do ORIS');
        expect(fixture(id).entry.sedadel).toBe('4');
        expect(fixture(id).entry.ubytovani).toBe('1');
        expect(fixture(id).entry.sync_status).toBe('LOCAL_ONLY');

        await staff.goto(`./race_regs_all.php?gr_id=400&id=${id}`);
        result = await postFormInSession(staff, `./race_regs_all_exc.php?gr_id=400&id=${id}`, {
          [`kateg[${member}]`]:'H35',
          [`pozn[${member}]`]:'bulk local override',
          [`pozn2[${member}]`]:'bulk internal override',
          [`sedadel[${member}]`]:5,
        });
        expect(result.status, result.text).toBe(200);
        expect(result.text).toContain('změny nebyly odeslány do ORIS');
        expect(fixture(id).entry.kat).toBe('H35');
        expect(fixture(id).entry.sedadel).toBe('5');
        expect(fixture(id).entry.sync_status).toBe('LOCAL_ONLY');

        result = await postFormInSession(staff, `./race_regs_all_exc.php?gr_id=400&id=${id}`, {
          [`kateg[${member}]`]:'',
          [`pozn[${member}]`]:'',
          [`pozn2[${member}]`]:'',
        });
        expect(result.status, result.text).toBe(200);
        expect(result.text).toContain('změny nebyly odeslány do ORIS');
        expect(fixture(id).entry).toBeNull();

        result = await postFormInSession(staff, `./race_regs_all_exc.php?gr_id=400&id=${id}`, {
          [`kateg[${member}]`]:'H21',
          [`pozn[${member}]`]:'bulk local add',
          [`pozn2[${member}]`]:'',
          [`sedadel[${member}]`]:2,
        });
        expect(result.status, result.text).toBe(200);
        expect(result.text).toContain('změny nebyly odeslány do ORIS');
        expect(fixture(id).entry.kat).toBe('H21');
        expect(fixture(id).entry.sync_status).toBe('LOCAL_ONLY');
      } finally { await context.close(); }
    });
    test('new forms preserve exact registration timestamps and automatic bookings follow registration', async ({ page }) => {
      const { member } = fixture(id, 'reset');
      await loginAs(page, 'registrar');
      await page.goto('./race_new.php?type=0');
      await expect(page.locator('[name=transport_do]')).toBeVisible();
      await expect(page.locator('[name=ubytovani_do]')).toBeVisible();
      const first = now()+3600;
      const state = await readFormState(page);
      const result = await postFormInSession(page, './race_new_exc.php?rtype=0', {
        ...state.fields, nazev:`PW exact deadlines ${id} created`, datum:cz(now()+864000).split(' ')[0],
        typ:'ob', typ0:'Z', oddil:'TST', misto:'Brno', ranking:'0', etap:1,
        prihlasky1:cz(first), prihlasky2:'', prihlasky3:'', prihlasky4:'', prihlasky5:'',
        ext_id:'8971', transport:2, accommodation:2, kategorie:'H21',
      });
      expect(result.status, result.text).toBe(200);
      const created = fixture(id,'created').race;
      expect(created).toBeTruthy();
      expect(Number(created.prihlasky1)).toBe(first);
      expect(created.transport_do).toBeNull();
      expect(created.ubytovani_do).toBeNull();
      fixture(id, 'patch', { transport:2, ubytovani:2, transport_do:now()-1, ubytovani_do:now()-1 });
      // A separate context avoids relying on the application's logout URL.
      const memberContext = await page.context().browser().newContext();
      const memberPage = await memberContext.newPage();
      try {
        await loginAs(memberPage, 'member');
        await memberPage.goto(`./us_race_regon.php?id_zav=${id}&id_us=${member}`);
        const registered = await postFormInSession(memberPage, './us_race_regon_exc.php', { id_zav:id, id_us:member, novy:1, kat:'H21' });
        expect(registered.status, registered.text).toBe(200);
        expect(fixture(id).entry.transport).toBe('1');
        expect(fixture(id).entry.ubytovani).toBe('1');
        await memberPage.goto(`./us_race_regon.php?id_zav=${id}&id_us=${member}`);
        await expect(memberPage.getByRole('button', { name:'Odhlásit ze závodu' })).toBeEnabled();
        await expect(memberPage.getByText('Společná doprava je součástí přihlášky na závod automaticky.', { exact:true })).toBeVisible();
        // Closing the race deadline rejects a subsequent direct registration edit.
        fixture(id,'patch',{prihlasky1:now()-1});
        const closed = await postFormInSession(memberPage, './us_race_regon_exc.php', { id_zav:id, id_us:member, novy:1, kat:'H35' });
        expect(closed.status).toBe(409);
      } finally { await memberContext.close(); }
    });
    test('manager edits preserve NULL values for closed services', async ({ page }) => {
      const { member } = fixture(id, 'reset');
      await loginAs(page, 'manager');
      const created = await postFormInSession(page, `./race_regs_all_exc.php?gr_id=500&id=${id}`, {
        [`kateg[${member}]`]: 'H21',
        [`pozn[${member}]`]: '',
        [`pozn2[${member}]`]: '',
      });
      expect(created.status, created.text).toBe(200);
      expect(fixture(id).entry.transport).toBeNull();
      expect(fixture(id).entry.ubytovani).toBeNull();

      fixture(id, 'patch', { transport_do: now()-60, ubytovani_do: now()-60 });
      await page.goto(`./race_regs_1.php?gr_id=500&id=${id}&show_ed=1`);
      await page.locator('[name=user_id]').selectOption(String(member));
      await expect(page.locator('[name=sedadel]')).toBeDisabled();
      await expect(page.locator('[name=ubytovani]')).toBeDisabled();
      await page.locator('[name=kateg]').fill('H35');
      await page.locator('form[name=form1] input[type=submit]').click();
      let entry = fixture(id).entry;
      expect(entry.kat).toBe('H35');
      expect(entry.transport).toBeNull();
      expect(entry.ubytovani).toBeNull();

      fixture(id, 'patch', { prihlasky1: now()-60, transport_do: now()+3600 });
      const updated = await postFormInSession(page, `./race_regs_1_exc.php?gr_id=500&id=${id}&show_ed=1`, {
        user_id: member, sedadel: 2,
      });
      expect(updated.status, updated.text).toBe(200);
      entry = fixture(id).entry;
      expect(entry.kat).toBe('H35');
      expect(entry.transport).toBe('1');
      expect(entry.sedadel).toBe('2');
      expect(entry.ubytovani).toBeNull();
    });
    test('bulk edit screen preserves registration content once the deadline closes', async ({ page, browser }) => {
      let { member } = fixture(id, 'reset');
      fixture(id, 'patch', { prihlasky1: now()-60 });
      await loginAs(page, 'manager');
      const blockedNew = await postFormInSession(page, `./race_regs_all_exc.php?gr_id=500&id=${id}`, {
        [`kateg[${member}]`]: 'H21',
        [`pozn[${member}]`]: '',
        [`pozn2[${member}]`]: '',
      });
      expect(blockedNew.status, blockedNew.text).toBe(409);
      expect(fixture(id).entry).toBeNull();

      ({ member } = fixture(id, 'reset'));
      const created = await postFormInSession(page, `./race_regs_all_exc.php?gr_id=500&id=${id}`, {
        [`kateg[${member}]`]: 'H21',
        [`pozn[${member}]`]: 'before deadline',
        [`pozn2[${member}]`]: '',
      });
      expect(created.status, created.text).toBe(200);
      expect(fixture(id).entry.kat).toBe('H21');

      fixture(id, 'patch', { prihlasky1: now()-60 });
      const attempted = await postFormInSession(page, `./race_regs_all_exc.php?gr_id=500&id=${id}`, {
        [`kateg[${member}]`]: 'H35',
        [`pozn[${member}]`]: 'after deadline',
        [`pozn2[${member}]`]: '',
      });
      expect(attempted.status, attempted.text).toBe(200);
      let entry = fixture(id).entry;
      expect(entry.kat).toBe('H21');
      expect(entry.pozn).toBe('before deadline');

      // Registrator keeps full override through the same bulk screen.
      const registrarContext = await browser.newContext();
      const registrarPage = await registrarContext.newPage();
      try {
        await loginAs(registrarPage, 'registrar');
        const overridden = await postFormInSession(registrarPage, `./race_regs_all_exc.php?gr_id=400&id=${id}`, {
          [`kateg[${member}]`]: 'H35',
          [`pozn[${member}]`]: 'registrar override',
          [`pozn2[${member}]`]: '',
        });
        expect(overridden.status, overridden.text).toBe(200);
        entry = fixture(id).entry;
        expect(entry.kat).toBe('H35');
        expect(entry.pozn).toBe('registrar override');
      } finally { await registrarContext.close(); }
    });
    test('editing preserves exact cutoffs and overrides round trip', async ({ page }) => {
      fixture(id,'reset');
      const deadline = now()+86400;
      fixture(id,'patch',{prihlasky1:deadline});
      await loginAs(page,'registrar');
      await page.goto(`./race_edit.php?id=${id}`);
      await expect(page.locator('[name=prihlasky1]')).toHaveValue(cz(deadline));
      const state = await readFormState(page,'form[name=form2]');
      let result = await postFormInSession(page, `./race_edit_exc.php?id=${id}&rtype=0`, { ...state.fields, transport_do:cz(deadline+1000), ubytovani_do:'' });
      expect(result.status, result.text).toBe(200);
      let race = fixture(id).race;
      expect(Number(race.prihlasky1)).toBe(deadline);
      expect(Number(race.transport_do)).toBe(deadline+1000);
      expect(race.ubytovani_do).toBeNull();
      await page.goto(`./race_edit.php?id=${id}`);
      const updated = await readFormState(page,'form[name=form2]');
      result = await postFormInSession(page, `./race_edit_exc.php?id=${id}&rtype=0`, { ...updated.fields, prihlasky1:cz(deadline+2000), transport_do:'' });
      expect(result.status, result.text).toBe(200);
      expect(fixture(id).race.transport_do).toBeNull();
      await page.goto(`./race_edit.php?id=${id}`);
      await expect(page.locator('[name=transport_do]')).toHaveAttribute('placeholder', cz(deadline+2000));
      await expect(page.locator('[name=ubytovani_do]')).toHaveAttribute('placeholder', cz(deadline+2000));
    });
  });
}
