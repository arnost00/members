const { test, expect } = require('@playwright/test');
const { execFileSync } = require('node:child_process');
const path = require('node:path');
const { loginAs, postFormInSession } = require('./helpers/browser');

function fixture(action = 'read', fields) {
  return JSON.parse(execFileSync('php', ['-d', 'short_open_tag=1', path.join(__dirname, 'helpers/oris-deadline-workflow-fixture.php')], {
    input: JSON.stringify({ action, fields }), encoding: 'utf8', stdio: ['pipe', 'pipe', 'pipe'],
  }));
}

test.describe('ORIS-linked deadline workflow', () => {
  test.afterEach(() => fixture('cleanup'));

  test('manager service edits preserve expired ORIS-linked registration details and sync state', async ({ page, browser }) => {
    const state = fixture('reset');
    const notes = { pozn: "O'Brien", pozn2: "Member's note: C:\\trips" };
    await loginAs(page, 'member');
    let result = await postFormInSession(page, './us_race_regon_exc.php', {
      id_zav: state.id, id_us: state.member, novy: 1, kat: 'H35', sedadel: 2, ubytovani: 1,
      ...notes,
    });
    expect(result.status, result.text).toBe(200);

    fixture('patch', { ext_id: 8971, prihlasky1: Math.floor(Date.now() / 1000) - 60, transport_do: Math.floor(Date.now() / 1000) + 600 });
    fixture('patch_entry');
    const managerContext = await browser.newContext({ timezoneId: 'Europe/Prague' });
    const managerPage = await managerContext.newPage();
    await loginAs(managerPage, 'smallManager');
    result = await postFormInSession(managerPage, `./race_regs_1_exc.php?gr_id=600&id=${state.id}&show_ed=1`, {
      user_id: state.member, kateg: 'ignored-after-deadline', sedadel: 4,
    });
    await managerContext.close();
    expect(result.status, result.text).toBe(200);
    expect(fixture().entry.sedadel).toBe('4');
    expect(fixture().entry.sync_status).toBe('SYNCED');

    const bulkContext = await browser.newContext();
    try {
      const bulkPage = await bulkContext.newPage();
      await loginAs(bulkPage, 'manager');
      // Repeated service-only saves must not accumulate escaping or mark the
      // unchanged ORIS registration as local-only.
      for (const seats of [5, 6]) {
        result = await postFormInSession(bulkPage, `./race_regs_all_exc.php?gr_id=500&id=${state.id}`, {
          [`kateg[${state.member}]`]: 'ignored-after-deadline',
          [`pozn[${state.member}]`]: 'ignored',
          [`pozn2[${state.member}]`]: 'ignored',
          [`sedadel[${state.member}]`]: seats,
        });
        expect(result.status, result.text).toBe(200);
        expect(fixture().entry).toMatchObject({
          kat: 'H35', pozn: notes.pozn, pozn_in: notes.pozn2,
          termin: '1', ubytovani: '1', sedadel: String(seats), sync_status: 'SYNCED',
        });
      }
    } finally {
      await bulkContext.close();
    }
  });
});
