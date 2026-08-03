const { test, expect } = require('@playwright/test');
const { loginAs } = require('./helpers/browser');
const { ensureClubMembers } = require('./helpers/app-actions');

test('ensure shared member 7203 exists exactly once', async ({ page }) => {
  await loginAs(page, 'clubAdmin');

  const [member] = await ensureClubMembers(page, ['7203'], {
    requireUnique: true,
  });

  expect(member.userId).toBeTruthy();
});
