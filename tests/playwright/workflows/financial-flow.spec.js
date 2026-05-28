const { test, expect } = require('@playwright/test');
const {
  getFinanceDirectoryEntryByReg,
  stornoFirstMemberFinanceEntry,
  submitFinanceTransferByReg,
  submitMemberFinanceEntry,
  submitMemberTransferByReg,
  updateFirstMemberFinanceEntry,
} = require('../helpers/app-actions');
const {
  getFinalMemberBalance,
  getFinanceBalancesByReg,
  sumBalances,
} = require('../helpers/finance');
const {
  loginAs,
} = require('../helpers/browser');
const {
  createWorkflowRun,
} = require('../helpers/workflow-runtime');

test.describe('Balance Change Workflow', () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test('accountant can manually update a member balance', async ({ browser }) => {
    const run = createWorkflowRun('manual-accountant-flow');
    const reg = '9952';
    const notes = {
      payment: `pw-manual-payment-${run.runId}`,
      deposit: `pw-manual-deposit-${run.runId}`,
      storno: `pw-manual-storno-${run.runId}`,
      update: `pw-manual-update-${run.runId}`,
    };

    const memberContext = await browser.newContext();
    const accountantContext = await browser.newContext();
    const memberPage = await memberContext.newPage();
    const accountantPage = await accountantContext.newPage();

    let memberBalance = null;
    let memberUserId = null;
    let initialAccountantEntry = null;

    try {
      await loginAs(memberPage, 'member');

      memberBalance = await getFinalMemberBalance(memberPage, './index.php?id=200&subid=10');
      expect(memberBalance).not.toBeNull();

      await loginAs(accountantPage, 'accountant');

      initialAccountantEntry = await getFinanceDirectoryEntryByReg(accountantPage, reg, {
        path: './index.php?id=800&subid=1',
      });

      expect(initialAccountantEntry).toBeTruthy();
      expect(initialAccountantEntry.amount).toBe(memberBalance);
      expect(initialAccountantEntry.overviewPath).toContain('./user_finance_view.php?user_id=');

      memberUserId = initialAccountantEntry.userId;
      expect(memberUserId).toBeTruthy();

      await submitMemberFinanceEntry(accountantPage, memberUserId, 'out', {
        amount: 250,
        note: notes.payment,
      });

      const afterPaymentEntry = await getFinanceDirectoryEntryByReg(accountantPage, reg, {
        path: './index.php?id=800&subid=1',
      });

      expect(afterPaymentEntry).toBeTruthy();
      expect(afterPaymentEntry.amount).toBe(initialAccountantEntry.amount - 250);

      await submitMemberFinanceEntry(accountantPage, memberUserId, 'in', {
        amount: 100,
        note: notes.deposit,
      });

      const afterDepositEntry = await getFinanceDirectoryEntryByReg(accountantPage, reg, {
        path: './index.php?id=800&subid=1',
      });

      expect(afterDepositEntry).toBeTruthy();
      expect(afterDepositEntry.amount).toBe(afterPaymentEntry.amount + 100);
      expect(afterDepositEntry.amount).toBe(initialAccountantEntry.amount - 150);

      await stornoFirstMemberFinanceEntry(accountantPage, memberUserId, {
        note: notes.storno,
      });

      const afterStornoEntry = await getFinanceDirectoryEntryByReg(accountantPage, reg, {
        path: './index.php?id=800&subid=1',
      });

      expect(afterStornoEntry).toBeTruthy();
      expect(afterStornoEntry.amount).toBe(afterDepositEntry.amount - 100);
      expect(afterStornoEntry.amount).toBe(afterPaymentEntry.amount);

      await updateFirstMemberFinanceEntry(accountantPage, memberUserId, {
        amount: -350,
        note: notes.update,
      });

      const afterUpdateEntry = await getFinanceDirectoryEntryByReg(accountantPage, reg, {
        path: './index.php?id=800&subid=1',
      });

      expect(afterUpdateEntry).toBeTruthy();
      expect(afterUpdateEntry.amount).toBe(afterStornoEntry.amount - 100);
      expect(afterUpdateEntry.amount).toBe(initialAccountantEntry.amount - 350);

      state.baselineBalances = await getFinanceBalancesByReg(accountantPage, ['9952', '8357', '9513']);
    } finally {
      await memberContext.close();
      await accountantContext.close();
    }
  });

  test('member can transfer money after the accountant changes', async ({ browser }) => {
    const run = createWorkflowRun('manual-accountant-transfer');
    const notes = {
      transfer8357: `pw-manual-transfer-8357-${run.runId}`,
      transfer9513: `pw-manual-transfer-9513-${run.runId}`,
    };

    const memberContext = await browser.newContext();
    const memberPage = await memberContext.newPage();

    try {
      await loginAs(memberPage, 'member');

      const beforeTransfersBalance = await getFinalMemberBalance(memberPage, './index.php?id=200&subid=10');
      expect(beforeTransfersBalance).not.toBeNull();

      await submitMemberTransferByReg(memberPage, '8357', {
        amount: 150,
        note: notes.transfer8357,
      });

      await submitMemberTransferByReg(memberPage, '9513', {
        amount: 50,
        note: notes.transfer9513,
      });

      const afterTransfersBalance = await getFinalMemberBalance(memberPage, './index.php?id=200&subid=10');
      expect(afterTransfersBalance).toBe(beforeTransfersBalance - 200);
    } finally {
      await memberContext.close();
    }
  });

  test('accountant sees transfer totals preserved and can storno the latest 9952 transaction', async ({ browser }) => {
    const run = createWorkflowRun('manual-accountant-recheck');
    const notes = {
      storno: `pw-manual-post-transfer-storno-${run.runId}`,
    };

    const accountantContext = await browser.newContext();
    const accountantPage = await accountantContext.newPage();

    try {
      expect(state.baselineBalances).toBeTruthy();

      await loginAs(accountantPage, 'accountant');

      const beforeStornoBalances = await getFinanceBalancesByReg(accountantPage, ['9952', '8357', '9513']);

      expect(sumBalances(beforeStornoBalances)).toBe(sumBalances(state.baselineBalances));
      expect(beforeStornoBalances['9952'].amount).toBe(state.baselineBalances['9952'].amount - 200);
      expect(beforeStornoBalances['8357'].amount).toBe(state.baselineBalances['8357'].amount + 150);
      expect(beforeStornoBalances['9513'].amount).toBe(state.baselineBalances['9513'].amount + 50);

      await stornoFirstMemberFinanceEntry(accountantPage, beforeStornoBalances['9952'].userId, {
        note: notes.storno,
      });

      const afterStornoBalances = await getFinanceBalancesByReg(accountantPage, ['9952', '8357', '9513']);

      expect(afterStornoBalances['9952'].amount).toBe(beforeStornoBalances['9952'].amount + 50);
      expect(afterStornoBalances['8357'].amount).toBe(beforeStornoBalances['8357'].amount);
      expect(afterStornoBalances['9513'].amount).toBe(beforeStornoBalances['9513'].amount);
      expect(sumBalances(afterStornoBalances)).toBe(sumBalances(beforeStornoBalances) + 50);
    } finally {
      await accountantContext.close();
    }
  });

  test('small manager can transfer money between managed members', async ({ browser }) => {
    const run = createWorkflowRun('small-manager-transfer');
    const notes = {
      transfer: `pw-small-manager-transfer-${run.runId}`,
    };

    const smallManagerContext = await browser.newContext();
    const smallManagerPage = await smallManagerContext.newPage();

    try {
      await loginAs(smallManagerPage, 'smallManager');

      const beforeBalances = await getFinanceBalancesByReg(smallManagerPage, ['8357', '9952'], './index.php?id=600&subid=10');

      const sourceEntry = beforeBalances['8357'];
      const targetEntry = beforeBalances['9952'];

      expect(sourceEntry.overviewPath).toContain('./user_finance_view.php?user_id=');

      await submitFinanceTransferByReg(smallManagerPage, '9952', {
        path: sourceEntry.overviewPath,
        amount: 100,
        note: notes.transfer,
      });

      const sourceFinalBalance = await getFinalMemberBalance(smallManagerPage, sourceEntry.overviewPath);
      expect(sourceFinalBalance).toBe(sourceEntry.amount - 100);

      const afterBalances = await getFinanceBalancesByReg(smallManagerPage, ['8357', '9952'], './index.php?id=600&subid=10');

      expect(afterBalances['8357'].amount).toBe(sourceEntry.amount - 100);
      expect(afterBalances['9952'].amount).toBe(targetEntry.amount + 100);
    } finally {
      await smallManagerContext.close();
    }
  });
});
