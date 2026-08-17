const { test, expect } = require('@playwright/test');
const { loginAs } = require('../helpers/browser');
const { ensureClubMember } = require('../helpers/app-actions');
const {
  createOrisMockUser,
  setOrisMockSettings,
} = require('../helpers/oris-mock');

const ORIS_CURRENT_SI_WORKFLOW = {
  name: 'Oris Current SI vs Registration Workflow',
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
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ browser, request }) => {
    // jmeno/prijmeni are varchar(20)/varchar(30) in the DB, so keep names short
    // and reg-derived rather than embedding a long unique run id.
    const reg = String(1000 + Math.floor(Math.random() * 8999));
    state.reg = reg;
    state.regNo = `ZBM${reg}`;
    state.orisUserId = `9${reg}`;
    state.orisClubUserId = `8${reg}`;
    state.name = `SiCheck${reg}`;

    const clubAdminContext = await browser.newContext();
    const clubAdminPage = await clubAdminContext.newPage();
    await loginAs(clubAdminPage, 'clubAdmin');
    const member = await ensureClubMember(clubAdminPage, {
      reg,
      surname: 'Testovska',
      name: state.name,
      chip: ORIS_CURRENT_SI_WORKFLOW.liveSi,
      requireUnique: true,
    });
    state.userId = member.userId;
    await clubAdminContext.close();

    await createOrisMockUser(request, {
      userId: state.orisUserId,
      clubUserId: state.orisClubUserId,
      regNo: state.regNo,
      firstName: state.name,
      lastName: 'Testovska',
      si: ORIS_CURRENT_SI_WORKFLOW.liveSi,
      regSi: ORIS_CURRENT_SI_WORKFLOW.staleRegSi,
      licence: 'C',
    });
  });

  test.afterAll(async ({ request }) => {
    await setOrisMockSettings(request, { clubUserListForbidden: false });
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

  test('page falls back to the registration SI when getClubUserList fails', async ({ page, request }) => {
    // getClubUserList can fail (network hiccup, ORIS-side issue, ...) independently
    // of other clubkey-protected calls. The page must degrade to its old
    // registration-based comparison rather than breaking.
    await setOrisMockSettings(request, { clubUserListForbidden: true });

    await loginAs(page, 'clubAdmin');
    await page.goto('./index.php?id=700&subid=2');

    const row = memberRow(page, state.reg);
    await expect(row).toContainText(ORIS_CURRENT_SI_WORKFLOW.staleRegSi);
    await expect(row.locator('a[href*="ads_oris_si_sync.php"]')).toHaveCount(1);
  });
});
