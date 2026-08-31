const { test, expect } = require('@playwright/test');
const { DEFAULT_PASSWORD } = require('../constants/auth');
const { TEST_USERS } = require('../constants/users');
const { login } = require('../components/login');
const { getCurrentUser, loginViaApi } = require('../helpers/api');
const {
  deleteMember,
  ensureClubMembers,
  ensureMemberLogin,
  findClubMemberByReg,
  getFinanceDirectoryEntryByReg,
  setMemberDisabled,
  setMemberSmallManager,
  submitMemberFinanceEntry,
  updateMemberProfile,
} = require('../helpers/app-actions');
const {
  loginAs,
  postFormInSession,
  readFormState,
} = require('../helpers/browser');

test.describe('Member 4444 Lifecycle Workflow', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {
    reg: '4444',
    login: 'member_4444',
  };

  async function expectProfileField(page, editPath, fieldName, expectedValue) {
    await page.goto(editPath);
    const form = await readFormState(page, 'form[action*="user_new_exc.php?update="]');
    expect(form.fields[fieldName]).toBe(expectedValue);
  }

  test('club admin creates member 4444', async ({ page }) => {
    await loginAs(page, 'clubAdmin');

    // A failed previous serial run may leave this dedicated test fixture behind.
    const staleMember = await findClubMemberByReg(page, state.reg, 'clubAdminAll');
    if (staleMember) {
      const staleUserId = new URL(staleMember.editPath, page.url()).searchParams.get('id');
      await deleteMember(page, staleUserId);
    }

    const [member] = await ensureClubMembers(page, [state.reg], {
      requireUnique: true,
    });

    expect(member.created).toBe(true);
    expect(member.userId).toBeTruthy();
    state.userId = member.userId;
  });

  test('club admin modifies the member', async ({ page }) => {
    await loginAs(page, 'clubAdmin');
    await updateMemberProfile(page, state.userId, {
      fields: { adresa: 'Club admin address 44' },
    });

    await expectProfileField(page, `./user_edit.php?id=${state.userId}`, 'adresa', 'Club admin address 44');
  });

  test('administrator modifies the member', async ({ page }) => {
    await loginAs(page, 'administrator');
    await updateMemberProfile(page, state.userId, {
      fields: { mesto: 'Administrator city 44' },
    });

    await expectProfileField(page, `./user_edit.php?id=${state.userId}`, 'mesto', 'Administrator city 44');
  });

  test('manager modifies and assigns the member to the small manager', async ({ page, request }) => {
    const smallManagerToken = await loginViaApi(request, TEST_USERS.smallManager);
    const smallManager = await getCurrentUser(request, smallManagerToken);

    await loginAs(page, 'manager');
    await updateMemberProfile(page, state.userId, {
      fields: { mobil: '704444445' },
    });
    await expectProfileField(page, `./user_edit.php?id=${state.userId}&cb=500`, 'mobil', '704444445');

    await setMemberSmallManager(page, state.userId, smallManager.user_id);
  });

  test('small manager modifies the assigned member', async ({ page }) => {
    await loginAs(page, 'smallManager');
    await updateMemberProfile(page, state.userId, {
      editPath: `./mns_user_edit.php?id=${state.userId}`,
      fields: { email: 'small.manager.4444@example.test' },
    });

    await expectProfileField(
      page,
      `./mns_user_edit.php?id=${state.userId}`,
      'email',
      'small.manager.4444@example.test'
    );
  });

  test('accountant adds member credit', async ({ page }) => {
    await loginAs(page, 'accountant');
    const before = await getFinanceDirectoryEntryByReg(page, state.reg, {
      path: './index.php?id=800&subid=1',
    });
    expect(before).toBeTruthy();

    await submitMemberFinanceEntry(page, state.userId, 'in', {
      amount: 444,
      note: 'Member 4444 lifecycle credit',
    });

    const after = await getFinanceDirectoryEntryByReg(page, state.reg, {
      path: './index.php?id=800&subid=1',
    });
    expect(after.amount).toBe(before.amount + 444);
  });

  test('administrator disables the member and manager cannot select them', async ({ page, browser }) => {
    await loginAs(page, 'administrator');
    await setMemberDisabled(page, state.userId, true);

    const managerContext = await browser.newContext();
    const managerPage = await managerContext.newPage();
    try {
      await loginAs(managerPage, 'manager');
      expect(await findClubMemberByReg(managerPage, state.reg, 'manager')).toBeNull();
    } finally {
      await managerContext.close();
    }
  });

  test('administrator enables the member', async ({ page }) => {
    await loginAs(page, 'administrator');
    await setMemberDisabled(page, state.userId, false);

    const member = await findClubMemberByReg(page, state.reg, 'clubAdmin', {
      requireUnique: true,
    });
    expect(member).toBeTruthy();
  });

  test('club admin creates a user account for the member', async ({ page }) => {
    await loginAs(page, 'clubAdmin');
    await ensureMemberLogin(page, state.userId, {
      login: state.login,
      password: DEFAULT_PASSWORD,
      signature: 'Member 4444',
    });
  });

  test('administrator views and updates the member through the admin detail page', async ({ page }) => {
    await loginAs(page, 'administrator');
    const detailPath = `./view_adm_user_detail.php?id=${state.userId}`;
    await page.goto(detailPath);

    await expect(page.getByText('Member Lifecycle')).toBeVisible();
    await expect(page.getByText(/4444/)).toBeVisible();
    await expect(page.locator('input[name="hidden"]')).not.toBeChecked();
    await expect(page.locator('input[name="locked"]')).not.toBeChecked();
    await expect(page.locator('input[name="entry_locked"]')).not.toBeChecked();

    const form = await readFormState(page, 'form[action*="view_adm_user_detail.php"]');
    const lockResult = await postFormInSession(page, form.action, {
      edit: '1',
      hidden: '1',
      locked: '1',
      entry_locked: '1',
    });
    expect(lockResult.ok).toBe(true);

    await page.goto(detailPath);
    await expect(page.locator('input[name="hidden"]')).toBeChecked();
    await expect(page.locator('input[name="locked"]')).toBeChecked();
    await expect(page.locator('input[name="entry_locked"]')).toBeChecked();

    const restoreResult = await postFormInSession(page, form.action, { edit: '1' });
    expect(restoreResult.ok).toBe(true);

    await page.goto(detailPath);
    await expect(page.locator('input[name="hidden"]')).not.toBeChecked();
    await expect(page.locator('input[name="locked"]')).not.toBeChecked();
    await expect(page.locator('input[name="entry_locked"]')).not.toBeChecked();
  });

  test('member user modifies their own profile', async ({ page }) => {
    await login(page, state.login, DEFAULT_PASSWORD);
    await updateMemberProfile(page, state.userId, {
      editPath: './index.php?id=200&subid=3',
      fields: { email: 'self.modified.4444@example.test' },
    });

    await expectProfileField(page, './index.php?id=200&subid=3', 'email', 'self.modified.4444@example.test');
  });

  test('administrator deletes the member and its user account', async ({ page, browser }) => {
    await loginAs(page, 'administrator');
    await deleteMember(page, state.userId);
    expect(await findClubMemberByReg(page, state.reg, 'clubAdmin')).toBeNull();

    const deletedUserContext = await browser.newContext();
    const deletedUserPage = await deletedUserContext.newPage();
    try {
      await login(deletedUserPage, state.login, DEFAULT_PASSWORD);
      await expect(deletedUserPage).toHaveURL(/error\.php\?code=101/);
    } finally {
      await deletedUserContext.close();
    }
  });
});
