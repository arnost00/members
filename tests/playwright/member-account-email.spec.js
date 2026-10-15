const { randomUUID } = require('node:crypto');
const { test, expect } = require('@playwright/test');
const { login } = require('./components/login');
const {
  loginAs,
  readFormState,
  postFormInSession,
  ensureHtmlSubmission,
} = require('./helpers/browser');

const mailpitURL = (process.env.MAILPIT_BASE_URL || 'http://mailpit:8025').replace(/\/$/, '');

test('club admin creates a member account and emails working generated credentials', async ({ page, request, browser }) => {
  const token = randomUUID().replaceAll('-', '').slice(0, 16);
  const surname = `EmailTest${token}`;
  const accountLogin = `mail_${token}`;
  const recipient = `${accountLogin}@example.test`;
  let userId;
  let messageId;

  async function searchMail() {
    const response = await request.get(`${mailpitURL}/api/v1/search`, {
      params: { query: `to:${recipient}` },
    });
    expect(response.ok(), 'Mailpit search must succeed').toBeTruthy();
    return (await response.json()).messages;
  }

  // Fail before creating fixtures if the local mail catcher is unavailable.
  expect(await searchMail()).toHaveLength(0);
  await loginAs(page, 'clubAdmin');

  try {
    await page.goto('./index.php?id=700&subid=1');
    const memberForm = await readFormState(page, 'form[action="./user_new_exc.php"]');
    ensureHtmlSubmission(await postFormInSession(page, memberForm.action, {
      ...memberForm.fields,
      jmeno: 'Test',
      prijmeni: surname,
      reg: '0',
      si: '0',
      datum: '1.1.2000',
      email: recipient,
    }), 'Create email test member');

    await page.goto('./index.php?id=700&subid=1');
    const memberRow = page.locator('table.ctmc tr').filter({ hasText: surname });
    await expect(memberRow).toHaveCount(1);
    const editPath = await memberRow.locator('a[href*="user_edit.php"]').getAttribute('href');
    userId = editPath.match(/[?&]id=(\d+)/)[1];

    await page.goto(`./user_login_edit.php?id=${userId}&cb=700`);
    const accountForm = page.locator('form[action*="type=2"]');
    await expect(accountForm).toHaveCount(1);
    await accountForm.locator('input[name="action_type"][value="2"]').check();
    await accountForm.locator('input[name="login_g"]').fill(accountLogin);
    await accountForm.locator('input[name="podpis_g"]').fill('Test');
    await accountForm.locator('input[name="email"]').fill(recipient);
    await accountForm.locator('input[type="submit"]').click();
    await expect(page).toHaveURL(/index\.php\?id=700&subid=1&result=1011$/);

    await expect.poll(async () => (await searchMail()).length, {
      timeout: 10000,
      message: 'Account creation must deliver one email to the test recipient',
    }).toBe(1);
    const [summary] = await searchMail();
    messageId = summary.ID;
    expect(summary.Subject).toBe('přihláškový systém - informace o účtu');
    expect(summary.To.map((address) => address.Address)).toEqual([recipient]);

    const response = await request.get(`${mailpitURL}/api/v1/message/${messageId}`);
    expect(response.ok()).toBeTruthy();
    const message = await response.json();
    expect(message.Text).toContain('Byl vám vytvořen nový účet');
    expect(message.Text).toContain(`Jméno : Test ${surname}`);
    expect(message.Text).toContain(`Login : ${accountLogin}`);
    const password = message.Text.match(/^Heslo : (\S+)\s*$/m)?.[1];
    expect(password).toMatch(/^[A-Za-z0-9]{8}$/);

    const memberContext = await browser.newContext();
    try {
      const memberPage = await memberContext.newPage();
      await login(memberPage, accountLogin, password);
      await expect(memberPage.locator('input[name^="mbr_l_"]')).toHaveCount(0);
      await memberPage.goto('./index.php?id=200&subid=3');
      await expect(memberPage.locator(`form[action*="user_new_exc.php?update=${userId}"]`)).toHaveCount(1);
    } finally {
      await memberContext.close();
    }
  } finally {
    if (userId) {
      ensureHtmlSubmission(await postFormInSession(page, `./user_del_exc.php?id=${userId}`), 'Delete email test member');
    }
    // Delete only this test's message; other parallel tests may use Mailpit too.
    if (messageId) {
      const response = await request.delete(`${mailpitURL}/api/v1/messages`, {
        data: { IDs: [messageId] },
      });
      expect(response.ok()).toBeTruthy();
    }
  }
});
