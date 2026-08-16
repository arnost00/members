const { test, expect } = require('@playwright/test');
const { login, submitLogin } = require('./components/login');
const { TEST_USERS } = require('./constants/users');
const { ALL_PROTECTED_ROUTES, LOGIN_EXPECTATIONS } = require('./constants/routes');

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

const DIRECT_BANK_ORPHAN_ROUTE = 'fin_bank_orphan_assign.php?tx_id=0';
const CLAIM_PRIVILEGED_ROLES = new Set(['administrator', 'manager', 'clubAdmin', 'accountant']);
const FINANCE_ALLOWED_ROLES = new Set(['administrator', 'clubAdmin', 'accountant']);
const PAYMENT_CLAIM_CASES = [
  {
    path: 'claim.php?payment_id=1',
    relatedRoles: new Set(['member', 'smallManager']),
    description: 'payment owned by the member managed by the small manager',
  },
  {
    path: 'claim.php?payment_id=3',
    relatedRoles: new Set(),
    description: 'payment unrelated to the member and small manager',
  },
];

test('does not preserve non-allowlisted action parameters for login forwarding', async ({ page }) => {
  await page.goto('mns_user_edit.php?id=7&chiefPayFor=7&chief_pay=7');

  await expect(page).toHaveURL(/error\.php\?code=21$/);
  await expect(page.locator('body')).toContainText('nemáte přístupová práva');
});

async function expectActiveRoute(page, route) {
  await expect(page).toHaveURL(new RegExp(`${escapeRegExp(route.path)}$`));
  await expect(page.locator('body')).toContainText('Přihlášen');
  await expect(page.locator('input[name^="mbr_l_"]')).toHaveCount(0);
  await expect(page.locator('span.NaviColSmSel').filter({ hasText: route.label })).toBeVisible();
}

async function expectForbiddenRoute(page, route) {
  await expect(page).toHaveURL(new RegExp(`${escapeRegExp(route.path)}$`));
  await expect(page.locator('body')).toContainText('Přihlášen');
  await expect(page.locator('input[name^="mbr_l_"]')).toHaveCount(0);
  await expect(page.locator('h2')).toHaveText('Novinky');
  await expect(page.locator('span.NaviColSmSel')).toHaveCount(0);
  await expect(page.locator(`a.NaviColSm[href="${route.path}"]`)).toHaveCount(0);
}

for (const [role, user] of Object.entries(TEST_USERS)) {
  const expectation = LOGIN_EXPECTATIONS[role];
  const accessibleRoutePaths = new Set(expectation.accessibleRoutes.map((route) => route.path));
  const forbiddenRoutes = ALL_PROTECTED_ROUTES.filter((route) => !accessibleRoutePaths.has(route.path));

  test(`allows ${role} to log in and access expected routes`, async ({ page }) => {
    await login(page, user);
    await expectActiveRoute(page, expectation.landingRoute);

    for (const route of expectation.accessibleRoutes) {
      await page.goto(route.path);
      await expectActiveRoute(page, route);
    }
  });

  test(`prevents ${role} from activating forbidden routes`, async ({ page }) => {
    await login(page, user);
    await expectActiveRoute(page, expectation.landingRoute);

    for (const route of forbiddenRoutes) {
      await page.goto(route.path);
      await expectForbiddenRoute(page, route);
    }
  });

  test(`redirects ${role} back to every directly requested allowed route`, async ({ page }) => {
    for (const route of expectation.accessibleRoutes) {
      await page.context().clearCookies();
      await page.goto(route.path);
      await expect(page.locator('input[name^="mbr_l_"]')).toBeVisible();

      await submitLogin(page, user);
      await expectActiveRoute(page, route);
    }
  });

  test(`does not grant ${role} direct access to forbidden routes after login`, async ({ page }) => {
    for (const route of forbiddenRoutes) {
      await page.context().clearCookies();
      await page.goto(route.path);
      await expect(page.locator('input[name^="mbr_l_"]')).toBeVisible();

      await submitLogin(page, user);
      await expectForbiddenRoute(page, route);
    }
  });

  test(`applies payment claim direct-access rules to ${role}`, async ({ page }) => {
    for (const claimCase of PAYMENT_CLAIM_CASES) {
      await page.context().clearCookies();
      await page.goto(claimCase.path);
      await expect(page.locator('input[name^="mbr_l_"]')).toBeVisible();

      await submitLogin(page, user);

      const hasAccess = CLAIM_PRIVILEGED_ROLES.has(role) || claimCase.relatedRoles.has(role);
      if (hasAccess) {
        await expect(page, claimCase.description).toHaveURL(
          new RegExp(`${escapeRegExp(claimCase.path)}$`),
        );
        await expect(page.locator('h2').first()).toHaveText('Reklamace platby');
        await expect(page.locator('input[name^="mbr_l_"]')).toHaveCount(0);
      } else {
        await expect(page, claimCase.description).toHaveURL(/error\.php\?code=21$/);
        await expect(page.locator('body')).toContainText('nemáte přístupová práva');
      }
    }
  });

  test(`applies finance-only bank assignment access to ${role}`, async ({ page }) => {
    await page.goto(DIRECT_BANK_ORPHAN_ROUTE);
    await expect(page.locator('input[name^="mbr_l_"]')).toBeVisible();

    await submitLogin(page, user);

    if (FINANCE_ALLOWED_ROLES.has(role)) {
      await expect(page).toHaveURL(new RegExp(`${escapeRegExp(DIRECT_BANK_ORPHAN_ROUTE)}$`));
      await expect(page.locator('h2')).toHaveText('Přiřazení bankovní transakce');
      await expect(page.locator('body')).toContainText('Nenalezeno.');
    } else {
      await expect(page).toHaveURL(/error\.php\?code=21$/);
      await expect(page.locator('body')).toContainText('nemáte přístupová práva');
    }
  });
}
