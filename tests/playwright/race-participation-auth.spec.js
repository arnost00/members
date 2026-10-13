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

// Issue #80: the participation page ("Účast") and its JSON API must not be reachable
// without login, and only race leader / manager / registrar / finance / admin may use it.

const READ_ACTIONS = ['detail', 'accomodation'];
const WRITE_ACTIONS = ['participate', 'entryByFin', 'uncheckAll'];

function apiUrl(raceId, params = {}) {
  const query = new URLSearchParams({ id_race: String(raceId), ...params });
  return `./api_race_entry.php?${query.toString()}`;
}

test.describe('Race participation page authorization', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ browser }) => {
    state.run = createWorkflowRun('race-participation-auth');
    state.leader = await getCurrentUser(browser, TEST_USERS.member);

    const context = await browser.newContext();
    const page = await context.newPage();
    await loginAs(page, 'registrar');
    const today = new Date();
    state.race = await createRace(page, {
      name: `Playwright participation auth ${state.run.runId}`,
      date: formatCzDate(addUtcDays(today, -1)),
      entryDate1: formatCzDate(addUtcDays(today, -3)),
      eventType: 'T',
      categories: 'H21;D21;',
    });
    await page.goto('./');
    await postFormInSession(page, './race_boss_exc.php', {
      id: String(state.race.id),
      boss: String(state.leader.user_id),
    });
    await context.close();
  });

  test('anonymous visitor cannot read participation data', async ({ request }) => {
    for (const action of READ_ACTIONS) {
      const response = await request.get(apiUrl(state.race.id, { action }));
      expect(response.status(), action).toBe(403);
      expect(await response.text(), action).not.toContain(state.leader.surname);
    }

    const userDetail = await request.get(apiUrl(state.race.id, { id_user: String(state.leader.user_id) }));
    expect(userDetail.status()).toBe(403);

    const view = await request.get(`./api_race_entry.view.php?race_id=${state.race.id}`);
    expect(view.status()).toBe(403);
    expect(await view.text()).not.toContain(state.race.name);
  });

  test('anonymous visitor cannot change entries or attendance', async ({ request, browser }) => {
    const writes = [
      { action: 'entryByFin', id_user: String(state.leader.user_id), kat: 'H21' },
      { action: 'participate', id_user: String(state.leader.user_id) },
      { action: 'uncheckAll' },
    ];
    for (const params of writes) {
      const viaGet = await request.get(apiUrl(state.race.id, params));
      expect(viaGet.status(), `GET ${params.action}`).toBe(403);
      const viaPost = await request.post(apiUrl(state.race.id), { form: params });
      expect(viaPost.status(), `POST ${params.action}`).toBe(403);
    }

    const detail = await getRaceDetail(browser, state.race.id);
    expect(detail.everyone.find((entry) => entry.user_id === state.leader.user_id)).toBeUndefined();
  });

  test('logged-in user without a permitted role is refused', async ({ page }) => {
    await loginAs(page, 'smallManager');
    const response = await page.request.get(apiUrl(state.race.id));
    expect(response.status()).toBe(403);

    const view = await page.request.get(`./api_race_entry.view.php?race_id=${state.race.id}`);
    expect(view.status()).toBe(403);
  });

  for (const role of ['registrar', 'manager', 'accountant']) {
    test(`${role} can read participation data`, async ({ page }) => {
      await loginAs(page, role);
      const response = await page.request.get(apiUrl(state.race.id));
      expect(response.status()).toBe(200);
      expect(Array.isArray(await response.json())).toBe(true);

      await page.goto(`./api_race_entry.view.php?race_id=${state.race.id}`);
      await expect(page.locator('#title')).toContainText(state.race.name);
    });
  }

  test('race leader can read participation data of their race', async ({ page }) => {
    await loginAs(page, 'member');
    const response = await page.request.get(apiUrl(state.race.id));
    expect(response.status()).toBe(200);
    const rows = await response.json();
    expect(rows.some((row) => Number(row.id_user) === state.leader.user_id)).toBe(true);
  });

  test('write actions require POST', async ({ page }) => {
    await loginAs(page, 'registrar');
    for (const action of WRITE_ACTIONS) {
      const response = await page.request.get(apiUrl(state.race.id, {
        action,
        id_user: String(state.leader.user_id),
      }));
      expect(response.status(), action).toBe(405);
    }
  });

  test('participation page buttons still work for an authorized user', async ({ page }) => {
    await loginAs(page, 'registrar');
    await page.goto(`./api_race_entry.view.php?race_id=${state.race.id}`);

    const entryButton = page.locator(`#btnEntry-${state.leader.user_id}`);
    const participateButton = page.locator(`#btnParticipate-${state.leader.user_id}`);
    // entries added on the page have no category, so read them through the API
    const readLeaderEntry = async () => (await page.request.get(apiUrl(state.race.id, {
      id_user: String(state.leader.user_id),
    }))).json();
    const waitForAction = (action) => page.waitForResponse((response) => (
      response.url().includes('api_race_entry.php')
      && response.request().method() === 'POST'
      && (response.request().postData() || '').includes(`action=${action}`)
    ));

    await Promise.all([waitForAction('entryByFin'), entryButton.click()]);
    await expect(participateButton).toHaveClass(/active/);
    expect(await readLeaderEntry()).toMatchObject({ add_by_fin: '1', participated: '1' });

    const [participate] = await Promise.all([waitForAction('participate'), participateButton.click()]);
    expect(participate.status()).toBe(200);
    await expect(participateButton).not.toHaveClass(/active/);
    expect(await readLeaderEntry()).toMatchObject({ participated: '0' });

    await Promise.all([waitForAction('entryByFin'), entryButton.click()]);
    await expect(entryButton).not.toHaveClass(/active/);
    expect(await readLeaderEntry()).toBeNull();
  });

  test('registrar can add and remove an on-site entry via POST', async ({ page, browser }) => {
    await loginAs(page, 'registrar');
    const form = { action: 'entryByFin', id_user: String(state.leader.user_id), kat: 'H21' };

    const added = await page.request.post(apiUrl(state.race.id), { form });
    expect(added.status()).toBe(200);
    expect(await added.json()).toBe('inserted:1');
    let detail = await getRaceDetail(browser, state.race.id);
    expect(detail.everyone.find((entry) => entry.user_id === state.leader.user_id)?.category).toBe('H21');

    const removed = await page.request.post(apiUrl(state.race.id), { form });
    expect(await removed.json()).toBe('deleted:1');
    detail = await getRaceDetail(browser, state.race.id);
    expect(detail.everyone.find((entry) => entry.user_id === state.leader.user_id)).toBeUndefined();
  });
});
