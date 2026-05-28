const { test, expect } = require('@playwright/test');
const { loginAs } = require('./helpers/browser');
const {
  createMockBankTransaction,
  openPopupMenuItem,
  setBankMockSettings,
} = require('./helpers/finance');
const { createWorkflowRun } = require('./helpers/workflow-runtime');

function importRowsByMessage(popup, message) {
  return popup
    .locator('form[action="fin_bank_sync.php"] tbody tr')
    .filter({ has: popup.locator('td', { hasText: message }) });
}

async function seedMatchedTransaction(request, label) {
  await createMockBankTransaction(request, {
    amount: 1111,
    originatorMessage: label,
    variableSymbol: '8888',
  });
}

async function openImportPopupTimed(page) {
  const startedAt = Date.now();
  const popup = await openPopupMenuItem(page, 'Import z banky');

  return {
    popup,
    elapsedMs: Date.now() - startedAt,
  };
}

async function expectSeededTransactionHidden(page, request, label) {
  await loginAs(page, 'accountant');
  const { popup } = await openImportPopupTimed(page);
  await expect(importRowsByMessage(popup, label)).toHaveCount(0);
  await popup.close();
}

async function importSeededTransaction(popup, label) {
  const row = importRowsByMessage(popup, label).first();
  const checkbox = row.locator('input[type="checkbox"][name*="[import]"]').first();

  await expect(checkbox).toBeChecked();

  const popupClosed = popup.waitForEvent('close').then(() => 'closed').catch(() => null);
  const popupReloaded = popup.waitForLoadState('domcontentloaded').then(() => 'loaded').catch(() => null);

  await popup.getByRole('button', { name: 'Provést import vybraných' }).click();
  await Promise.race([popupClosed, popupReloaded]);

  if (!popup.isClosed()) {
    await expect(importRowsByMessage(popup, label)).toHaveCount(0);
  }
}

test.describe('Bank Connector Errors', () => {
  test.describe.configure({ mode: 'serial' });
  let seededLabel;

  test.beforeAll(async ({ request }) => {
    const run = createWorkflowRun('bank-connector-errors');
    seededLabel = `pw-bank-error-${run.runId}`;
    await seedMatchedTransaction(request, seededLabel);
  });

  test.afterEach(async ({ request }) => {
    await setBankMockSettings(request, { mode: 'normal' });
  });

  test('import waits about 30 seconds when the bank mock hangs on first attempt', async ({ page, request }) => {
    test.slow();
    await setBankMockSettings(request, { mode: 'hang' });
    await loginAs(page, 'accountant');

    const { popup, elapsedMs } = await openImportPopupTimed(page);

    expect(elapsedMs).toBeGreaterThanOrEqual(29000);
    await expect(importRowsByMessage(popup, seededLabel)).toHaveCount(0);
    await popup.close();
  });

  test('import waits about 30 seconds when the bank mock hangs on second attempt', async ({ page, request }) => {
    test.slow();
    await setBankMockSettings(request, { mode: 'hang' });
    await loginAs(page, 'accountant');

    const { popup, elapsedMs } = await openImportPopupTimed(page);

    expect(elapsedMs).toBeGreaterThanOrEqual(29000);
    await expect(importRowsByMessage(popup, seededLabel)).toHaveCount(0);
    await popup.close();
  });

  test('import hides the seeded transaction when the bank mock closes the connection', async ({ page, request }) => {
    await setBankMockSettings(request, { mode: 'close_connection' });

    await expectSeededTransactionHidden(page, request, seededLabel);
  });

  for (const statusCode of [400, 401, 403, 404, 429]) {
    test(`import hides the seeded transaction for client error ${statusCode}`, async ({ page, request }) => {
      await setBankMockSettings(request, {
        forceStatusCode: statusCode,
        mode: 'force_client_error',
      });

      await expectSeededTransactionHidden(page, request, seededLabel);
    });
  }

  test('import shows the seeded transaction again after the bank mock returns to normal mode', async ({ page, request }) => {
    await setBankMockSettings(request, { mode: 'normal' });
    await loginAs(page, 'accountant');

    const { popup } = await openImportPopupTimed(page);
    const row = importRowsByMessage(popup, seededLabel);

    await expect(row).toHaveCount(1);
    await expect(row.first()).toContainText('1111');
    await expect(row.first()).toContainText('8888');
    await importSeededTransaction(popup, seededLabel);
    await popup.close();
  });
});
