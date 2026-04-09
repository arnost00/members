const { test, expect } = require('@playwright/test');
const { login } = require('./components/login');
const { TEST_USERS } = require('./constants/users');
const { ALL_PROTECTED_ROUTES, LOGIN_EXPECTATIONS } = require('./constants/routes');

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

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
}
