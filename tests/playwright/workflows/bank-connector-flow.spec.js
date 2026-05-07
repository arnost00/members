const { test, expect } = require('@playwright/test');
const { loginAs } = require('../helpers/browser');
const {
  createMockBankTransaction,
  getFinanceBalanceByReg,
  getFinanceHistoryEntries,
  getFirstFinanceHistoryEntry,
  openPopupMenuItem,
} = require('../helpers/finance');
const { addUtcDays, createWorkflowRun } = require('../helpers/workflow-runtime');

async function importMatchedBankTransaction(page, request, options = {}) {
  const reg = String(options.reg ?? '9111');
  const amount = Number(options.amount ?? 1500);
  const originatorMessage = options.originatorMessage ?? '';

  if (options.expectEmptyState) {
    const emptyImportPopup = await openPopupMenuItem(page, 'Import z banky');
    await expect(emptyImportPopup.getByRole('heading', { name: 'Import plateb z banky' })).toBeVisible();
    await expect(emptyImportPopup.locator('text=Nenalezeny žádné nové transakce.')).toBeVisible();
    await emptyImportPopup.close();
  }

  const balanceBeforeImport = await getFinanceBalanceByReg(page, reg);
  expect(balanceBeforeImport.amount).not.toBeNull();

  if (options.createTransaction !== false) {
    await createMockBankTransaction(request, {
      amount,
      variableSymbol: reg,
      originatorMessage,
    });
  }

  const importPopup = await openPopupMenuItem(page, 'Import z banky');
  const importForm = importPopup.locator('form[action="fin_bank_sync.php"]');
  const importRows = importForm.locator('tbody tr');
  const matchingTransactions = importRows.filter({
    has: importPopup.locator('td', { hasText: originatorMessage }),
  });
  const selectedTransactions = importForm.locator('input[type="checkbox"][name*="[import]"]');
  const transactionRow = matchingTransactions.first();
  const transactionCheckbox = transactionRow.locator('input[type="checkbox"][name*="[import]"]').first();
  const selectedCount = await selectedTransactions.count();

  await expect(matchingTransactions).toHaveCount(1);
  await expect(transactionRow).toContainText(reg);
  await expect(transactionRow).toContainText(String(amount));
  await expect(transactionRow).toContainText(originatorMessage);

  for (let index = 0; index < selectedCount; index += 1) {
    const checkbox = selectedTransactions.nth(index);
    const rowText = await checkbox.locator('xpath=ancestor::tr[1]').textContent();
    if (!String(rowText || '').includes(originatorMessage)) {
      await checkbox.uncheck();
    }
  }

  await expect(transactionCheckbox).toBeChecked();

  await Promise.all([
    importPopup.waitForLoadState('domcontentloaded'),
    importPopup.getByRole('button', { name: 'Provést import vybraných' }).click(),
  ]);

  await expect(importPopup.locator('.msg')).toContainText('Naimportováno: 1, Chyb: 0.');
  await expect(matchingTransactions).toHaveCount(0);
  await importPopup.close();

  const balanceAfterImport = await getFinanceBalanceByReg(page, reg);
  expect(balanceAfterImport.amount).toBe(balanceBeforeImport.amount + amount);

  return {
    amount,
    balanceAfterImport,
    balanceBeforeImport,
    originatorMessage,
    reg,
  };
}

async function openFinanceOverviewPopup(page, overviewPath) {
  const [popup] = await Promise.all([
    page.waitForEvent('popup'),
    page.evaluate((path) => {
      window.open(path, '', 'width=800,height=800');
    }, overviewPath),
  ]);

  await popup.waitForLoadState('domcontentloaded');
  return popup;
}

test.describe('Bank Connector Workflow', () => {
  test.describe.configure({ mode: 'serial' });

  test('accountant can import a matched bank transaction', async ({ page, request }) => {
    const run = createWorkflowRun('bank-connector-flow');
    await loginAs(page, 'accountant');

    await importMatchedBankTransaction(page, request, {
      amount: 1500,
      originatorMessage: `pw-bank-import-${run.runId}`,
      reg: '9111',
    });
  });

  test('registrar sees the imported bank transaction in finance history', async ({ browser, page, request }) => {
    const run = createWorkflowRun('bank-connector-member-flow');
    const accountantContext = await browser.newContext();
    const accountantPage = await accountantContext.newPage();

    try {
      await loginAs(accountantPage, 'accountant');

      await importMatchedBankTransaction(accountantPage, request, {
        amount: 1500,
        originatorMessage: `pw-bank-import-member-${run.runId}`,
        reg: '9111',
      });
    } finally {
      await accountantContext.close();
    }

    await loginAs(page, 'registrar');

    const firstTransaction = await getFirstFinanceHistoryEntry(page, './index.php?id=200&subid=10');
    expect(firstTransaction.note).toContain('VS 9111');
  });

  test('accountant can import matched payment and assign orphan payment for reg 8511', async ({ page, request }) => {
    const run = createWorkflowRun('bank-connector-batch-flow');
    const reg = '8511';
    const orphanVariableSymbol = '8899';
    const olderDate = addUtcDays(new Date(), -3).toISOString();
    const messages = {
      orphan: `pw-bank-batch-orphan-${run.runId}`,
      negative: `pw-bank-batch-negative-${run.runId}`,
      imported: `pw-bank-batch-import-${run.runId}`,
    };

    await loginAs(page, 'accountant');
    const initialTargetEntry = await getFinanceBalanceByReg(page, reg);
    const initialBalance = initialTargetEntry.amount;

    await createMockBankTransaction(request, {
      amount: 675,
      valueDate: olderDate,
      variableSymbol: orphanVariableSymbol,
      originatorMessage: messages.orphan,
    });

    await createMockBankTransaction(request, {
      amount: -1400,
      variableSymbol: reg,
      originatorMessage: messages.negative,
    });

    await createMockBankTransaction(request, {
      amount: 1500,
      variableSymbol: reg,
      originatorMessage: messages.imported,
    });

    await importMatchedBankTransaction(page, request, {
      amount: 1500,
      createTransaction: false,
      originatorMessage: messages.imported,
      reg,
    });

    const targetEntry = await getFinanceBalanceByReg(page, reg);
    expect(targetEntry.userId).toBeTruthy();
    expect(targetEntry.overviewPath).toContain('./user_finance_view.php?user_id=');

    await page.locator('a', { hasText: 'Nespárované bankovní platby' }).first().click();
    await expect(page.getByRole('heading', {
      level: 2,
      name: 'Nespárované bankovní platby',
    })).toBeVisible();

    const orphanRow = page.locator('table.ctmc tr', {
      has: page.locator('td', { hasText: messages.orphan }),
    }).first();
    await expect(orphanRow).toHaveCount(1);
    await expect(orphanRow).toContainText('675');

    const [assignPopup] = await Promise.all([
      page.waitForEvent('popup'),
      orphanRow.locator('a', { hasText: 'Přiřadit' }).click(),
    ]);
    await assignPopup.waitForLoadState('domcontentloaded');
    await assignPopup.locator('select#assign_user_id').selectOption(String(targetEntry.userId));
    await Promise.all([
      assignPopup.waitForEvent('close'),
      assignPopup.locator('button#submit').click(),
    ]);

    await page.locator('a', { hasText: 'Členská základna' }).first().click();
    const refreshedTargetEntry = await getFinanceBalanceByReg(page, reg, './index.php?id=800&subid=1');
    const overviewPopup = await openFinanceOverviewPopup(page, refreshedTargetEntry.overviewPath);
    const latestTransactions = await getFinanceHistoryEntries(overviewPopup, 2, null);

    expect(latestTransactions[0].amount).toBe('675');
    expect(latestTransactions[0].note).toContain(`VS ${orphanVariableSymbol}`);
    expect(latestTransactions[0].note).toContain(messages.orphan);
    expect(latestTransactions[1].amount).toBe('1500');
    expect(latestTransactions[1].note).toContain('VS 8511');
    expect(latestTransactions[1].note).toContain(messages.imported);

    await overviewPopup.close();

    expect(refreshedTargetEntry.amount).toBe(initialBalance + 2175);
  });
});
