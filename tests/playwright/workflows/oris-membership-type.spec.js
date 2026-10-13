const { test, expect } = require('@playwright/test');
const { loginAs, postFormInSession, readFormState } = require('../helpers/browser');
const { ensureClubMember } = require('../helpers/app-actions');
const { createOrisMockUser } = require('../helpers/oris-mock');

// Issue #79: ORIS comparison page tags the membership type and offers the SI push
// to ORIS ([<<] link) only for regular members.
const MEMBERS = [
  { reg: '9712', surname: 'Treninkovy', name: 'Tomas', type: 'T', si: '1112', orisSi: '2212', orisUserId: '719712', clubUserId: '619712' },
  { reg: '9713', surname: 'Radny', name: 'Rudolf', type: 'R', si: '1113', orisSi: '2213', orisUserId: '719713', clubUserId: '619713' },
];

test.describe('ORIS comparison page and membership type', () => {
  test('SI push link is offered only for regular members', async ({ page, request }) => {
    const year = new Date().getUTCFullYear();

    for (const member of MEMBERS) {
      await createOrisMockUser(request, {
        userId: member.orisUserId,
        clubUserId: member.clubUserId,
        regNo: `ZBM${member.reg}`,
        firstName: member.name,
        lastName: member.surname,
        si: member.orisSi,
        sport: 1,
        year,
      });
    }

    await page.context().clearCookies();
    await loginAs(page, 'clubAdmin');
    for (const member of MEMBERS) {
      const ensured = await ensureClubMember(page, {
        reg: member.reg,
        surname: member.surname,
        name: member.name,
        chip: member.si,
        updateExisting: true,
      });
      await page.goto(`./user_edit.php?id=${ensured.userId}&cb=700`);
      const form = await readFormState(page, 'form[action*="user_new_exc.php?update="]');
      const result = await postFormInSession(page, form.action, { ...form.fields, clenstvi: member.type });
      expect(result.ok).toBe(true);
    }

    await page.goto('./index.php?id=700&subid=2');
    const trainingRow = page.locator('table.ctmc tbody tr').filter({ has: page.locator('td', { hasText: /^9712$/ }) });
    await expect(trainingRow).toHaveCount(1);
    await expect(trainingRow.locator('.membership-tag')).toHaveText('T');
    await expect(trainingRow).toContainText('2212');
    await expect(trainingRow.locator('a[href*="ads_oris_si_sync.php"]')).toHaveCount(0);

    const regularRow = page.locator('table.ctmc tbody tr').filter({ has: page.locator('td', { hasText: /^9713$/ }) });
    await expect(regularRow).toHaveCount(1);
    await expect(regularRow.locator('a[href*="ads_oris_si_sync.php"]')).toHaveCount(1);
  });
});
