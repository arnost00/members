const { test, expect } = require('@playwright/test');
const { loginAs } = require('../helpers/browser');
const { ensureClubMember } = require('../helpers/app-actions');
const {
  createOrisMockUser,
} = require('../helpers/oris-mock');

const ORIS_CURRENT_SI_WORKFLOW = {
  name: 'Oris Current SI vs Registration Workflow',
  reg: '9956',
  regNo: 'ZBM9956',
  orisUserId: '29956',
  orisClubUserId: '39956',
  memberName: 'SiCheck9956',
  // The member's current ORIS state (getClubUserList) already has this SI - only the
  // year's registration snapshot (getRegistration) is stale, as if it predates a sync.
  liveSi: '2181929',
  staleRegSi: '999999',
};

function memberRow(page, reg) {
  return page
    .locator('td')
    .filter({ hasText: new RegExp(`^${reg}$`) })
    .first()
    .locator('xpath=ancestor::tr[1]');
}

test.describe(ORIS_CURRENT_SI_WORKFLOW.name, () => {
  const state = {};

  test.beforeAll(async ({ browser, request }) => {
    Object.assign(state, ORIS_CURRENT_SI_WORKFLOW);

    const clubAdminContext = await browser.newContext();
    const clubAdminPage = await clubAdminContext.newPage();
    await loginAs(clubAdminPage, 'clubAdmin');
    const member = await ensureClubMember(clubAdminPage, {
      reg: state.reg,
      surname: 'Testovska',
      name: state.memberName,
      chip: ORIS_CURRENT_SI_WORKFLOW.liveSi,
      requireUnique: true,
      updateExisting: true,
    });
    state.userId = member.userId;
    await clubAdminContext.close();

    await createOrisMockUser(request, {
      userId: state.orisUserId,
      clubUserId: state.orisClubUserId,
      regNo: state.regNo,
      firstName: state.memberName,
      lastName: 'Testovska',
      si: ORIS_CURRENT_SI_WORKFLOW.liveSi,
      regSi: ORIS_CURRENT_SI_WORKFLOW.staleRegSi,
      licence: 'C',
    });
  });

  test('club admin sees the live ORIS SI, not the stale per-year registration SI', async ({ page }) => {
    await loginAs(page, 'clubAdmin');
    await page.goto('./index.php?id=700&subid=2');

    const row = memberRow(page, state.reg);
    await expect(row).toContainText(ORIS_CURRENT_SI_WORKFLOW.liveSi);
    await expect(row).not.toContainText(ORIS_CURRENT_SI_WORKFLOW.staleRegSi);
    // Live SI matches the local chip, so no mismatch and no sync link should be shown.
    await expect(row.locator('a[href*="ads_oris_si_sync.php"]')).toHaveCount(0);
  });
});
