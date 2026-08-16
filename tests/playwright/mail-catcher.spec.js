const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');
const { clearMailbox, waitForEmailTo } = require('./helpers/mail');

test('captures an email sent via PHP mail() through msmtp into mailpit', async ({ request }) => {
  await clearMailbox(request);

  const recipient = 'mail-catcher-smoke@example.test';
  const subject = `Mailpit smoke test ${Date.now()}`;
  const body = 'This message proves the mail catcher pipeline works end-to-end.';

  execSync(
    `php -r 'if (!mail(${JSON.stringify(recipient)}, ${JSON.stringify(subject)}, ${JSON.stringify(body)})) { exit(1); }'`,
    { cwd: '/var/www/html/members' },
  );

  const message = await waitForEmailTo(request, recipient, { subject });

  expect(message.subject).toBe(subject);
  expect(message.text.trim()).toBe(body);
  expect(message.to.some((addr) => addr.Address === recipient)).toBe(true);
});
