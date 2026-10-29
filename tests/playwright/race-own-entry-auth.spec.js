const { test, expect } = require('@playwright/test');
const { TEST_USERS } = require('./constants/users');
const { getCurrentUser, getRaceDetail } = require('./helpers/api');
const { loginAs, postFormInSession } = require('./helpers/browser');
const { createRace } = require('./helpers/app-actions');
const {
  addUtcDays,
  createWorkflowRun,
  formatCzDate,
} = require('./helpers/workflow-runtime');

// Issue #94: a member may enter / change / cancel only their own race entry via
// us_race_regon(_exc).php and us_race_regoff_exc.php; entries for others go through race_regs_*.

function entryFields(raceId, userId, extra = {}) {
  return {
    id_zav: String(raceId),
    id_us: String(userId),
    kat: 'H21',
    pozn: '',
    pozn2: '',
    ...extra,
  };
}

async function withRole(browser, role, callback) {
  const context = await browser.newContext();
  const page = await context.newPage();
  try {
    await loginAs(page, role);
    await page.goto('./');
    return await callback(page);
  } finally {
    await context.close();
  }
}

function entryOf(detail, userId) {
  return detail.everyone.find((entry) => entry.user_id === userId);
}

test.describe('Member race entry authorization', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ browser }) => {
    state.run = createWorkflowRun('race-own-entry-auth');
    state.member = await getCurrentUser(browser, TEST_USERS.member);
    state.other = await getCurrentUser(browser, TEST_USERS.smallManager);

    const today = new Date();
    state.race = await withRole(browser, 'registrar', (page) => createRace(page, {
      name: `Playwright own entry auth ${state.run.runId}`,
      date: formatCzDate(addUtcDays(today, 10)),
      entryDate1: formatCzDate(addUtcDays(today, 5)),
      eventType: 'T',
      categories: 'H21;D21;',
    }));
  });

  test('member cannot open the entry form or enter another member', async ({ browser }) => {
    await withRole(browser, 'member', async (page) => {
      await page.goto(`./us_race_regon.php?id_zav=${state.race.id}&id_us=${state.other.user_id}`);
      expect(page.url()).toContain('error.php?code=21');

      const result = await postFormInSession(
        page,
        './us_race_regon_exc.php',
        entryFields(state.race.id, state.other.user_id, { novy: '1' }),
      );
      expect(result.url).toContain('error.php?code=21');
    });

    const detail = await getRaceDetail(browser, state.race.id);
    expect(entryOf(detail, state.other.user_id)).toBeUndefined();
  });

  test('member cannot change or cancel another member\'s entry', async ({ browser }) => {
    await withRole(browser, 'smallManager', async (page) => {
      const result = await postFormInSession(
        page,
        './us_race_regon_exc.php',
        entryFields(state.race.id, state.other.user_id, { novy: '1' }),
      );
      expect(result.url).not.toContain('error.php');
    });

    const otherEntryId = await withRole(browser, 'registrar', async (page) => {
      const response = await page.request.get(`./api_race_entry.php?id_race=${state.race.id}`);
      const rows = await response.json();
      return Number(rows.find((row) => Number(row.id_user) === state.other.user_id)?.id);
    });
    expect(otherEntryId).toBeGreaterThan(0);

    await withRole(browser, 'member', async (page) => {
      const cancel = await postFormInSession(page, './us_race_regoff_exc.php', {
        id_zav: String(state.race.id),
        id_us: String(state.other.user_id),
      });
      expect(cancel.url).toContain('error.php?code=21');

      // own id_us, but id_z of another member's entry
      await postFormInSession(
        page,
        './us_race_regon_exc.php',
        entryFields(state.race.id, state.member.user_id, { id_z: String(otherEntryId), kat: 'D21' }),
      );
    });

    const detail = await getRaceDetail(browser, state.race.id);
    expect(entryOf(detail, state.other.user_id)?.category).toBe('H21');
    expect(entryOf(detail, state.member.user_id)).toBeUndefined();
  });

  test('member can still enter and cancel their own entry', async ({ browser }) => {
    await withRole(browser, 'member', async (page) => {
      const result = await postFormInSession(
        page,
        './us_race_regon_exc.php',
        entryFields(state.race.id, state.member.user_id, { novy: '1' }),
      );
      expect(result.url).not.toContain('error.php');
    });
    expect(entryOf(await getRaceDetail(browser, state.race.id), state.member.user_id)?.category).toBe('H21');

    await withRole(browser, 'member', async (page) => {
      const cancel = await postFormInSession(page, './us_race_regoff_exc.php', {
        id_zav: String(state.race.id),
        id_us: String(state.member.user_id),
      });
      expect(cancel.url).not.toContain('error.php');
    });
    expect(entryOf(await getRaceDetail(browser, state.race.id), state.member.user_id)).toBeUndefined();
  });
});
