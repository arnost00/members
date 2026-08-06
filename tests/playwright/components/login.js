const { expect } = require('@playwright/test');
const { DEFAULT_PASSWORD } = require('../constants/auth');

async function login(page, user, password = DEFAULT_PASSWORD) {
  const testSuite = process.env.MEMBERS_E2E_SUITE;
  if (testSuite === 'no-oris' || testSuite === 'no-oris-key') {
    await page.context().setExtraHTTPHeaders({
      'X-Members-Autotest-Suite': testSuite,
    });
  }

  await page.goto('./');

  const loginInput = page.locator('input[name^="mbr_l_"]');
  const passwordInput = page.locator('input[name^="mbr_p_"]');
  const submitButton = page.locator('input[type="submit"][value="Přihlásit"]');

  await expect(loginInput).toBeVisible();
  await expect(passwordInput).toBeVisible();

  await loginInput.fill(user);
  await passwordInput.fill(password);
  await submitButton.click();
}

module.exports = {
  DEFAULT_PASSWORD,
  login,
};
