const { test, expect } = require('@playwright/test');
const { login } = require('./components/login');

test('allows a seeded user to log in', async ({ page }) => {
  const user = process.env.PLAYWRIGHT_LOGIN_USER || 'tnov_5';

  await login(page, user);

  await expect(page).toHaveURL(/\/index\.php\?(id=4|id=300&subid=1)$/);
  await expect(page.locator('body')).toContainText('Přihlášen');
});
