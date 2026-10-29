const { test, expect } = require('@playwright/test');
const { loginAs } = require('./helpers/browser');
const { ensureClubMember } = require('./helpers/app-actions');

// Issue #93: the member detail popup (view_adm_user_detail.php) changes hidden / locked /
// entry_locked flags, so only small admin / admin may use it.

const TARGET_REG = '9931';

function detailUrl(userId, params = {}) {
  const query = new URLSearchParams({ id: String(userId), ...params });
  return `./view_adm_user_detail.php?${query.toString()}`;
}

async function withRole(browser, role, callback) {
  const context = await browser.newContext();
  const page = await context.newPage();
  try {
    await loginAs(page, role);
    return await callback(page);
  } finally {
    await context.close();
  }
}

async function readHidden(browser, userId) {
  return withRole(browser, 'clubAdmin', async (page) => {
    await page.goto(detailUrl(userId));
    return page.locator('#hidden').isChecked();
  });
}

test.describe('Member detail popup authorization', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ browser }) => {
    state.target = await withRole(browser, 'clubAdmin', (page) => ensureClubMember(page, {
      reg: TARGET_REG,
      surname: 'Detailova',
      name: 'Auth93',
      requireUnique: true,
    }));
    expect(Number(state.target.userId)).toBeGreaterThan(0);
  });

  test('regular member cannot open the popup or change flags', async ({ browser }) => {
    expect(await readHidden(browser, state.target.userId)).toBe(false);

    await withRole(browser, 'member', async (page) => {
      await page.goto(detailUrl(state.target.userId));
      expect(page.url()).toContain('error.php?code=21');

      await page.goto(detailUrl(state.target.userId, { hidden: '1', edit: 'true' }));
      expect(page.url()).toContain('error.php?code=21');
    });

    expect(await readHidden(browser, state.target.userId)).toBe(false);
  });

  test('small admin can still change flags', async ({ browser }) => {
    await withRole(browser, 'clubAdmin', async (page) => {
      await page.goto(detailUrl(state.target.userId, { hidden: '1', edit: 'true' }));
    });
    expect(await readHidden(browser, state.target.userId)).toBe(true);

    await withRole(browser, 'clubAdmin', async (page) => {
      await page.goto(detailUrl(state.target.userId, { hidden: '0', edit: 'true' }));
    });
    expect(await readHidden(browser, state.target.userId)).toBe(false);
  });
});
