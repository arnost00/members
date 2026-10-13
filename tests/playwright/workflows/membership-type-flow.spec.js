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
});
