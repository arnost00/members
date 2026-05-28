const { getFinanceDirectoryEntryByReg } = require('./app-actions');

const DEFAULT_FINANCE_DIRECTORY_PATH = './index.php?id=800&subid=1';
const DEFAULT_BANK_MOCK_TRANSACTIONS_URL = process.env.PLAYWRIGHT_BANK_MOCK_ADMIN_TRANSACTIONS_URL
  || 'http://127.0.0.1:10300/__admin/api/transactions';
const DEFAULT_BANK_MOCK_SETTINGS_URL = process.env.PLAYWRIGHT_BANK_MOCK_ADMIN_SETTINGS_URL
  || 'http://127.0.0.1:10300/__admin/api/settings';

async function getFinalMemberBalance(page, path) {
  if (path) {
    await page.goto(path);
  }

  const amountText = await page.locator('tr', {
    has: page.locator('td', { hasText: 'Konečný zůstatek' }),
  }).first().locator('span.amount, span.amountred, span.amountgreen').textContent();

  const match = String(amountText || '').trim().match(/-?\d+/);
  return match ? Number(match[0]) : null;
}

async function getFinanceBalancesByReg(page, regs, path = DEFAULT_FINANCE_DIRECTORY_PATH) {
  const balances = {};

  for (const [index, reg] of regs.entries()) {
    const entry = await getFinanceDirectoryEntryByReg(page, reg, index === 0 ? { path } : {});
    if (!entry) {
      throw new Error(`Could not find finance balance for reg ${reg}`);
    }

    balances[reg] = entry;
  }

  return balances;
}

async function getFinanceBalanceByReg(page, reg, path = DEFAULT_FINANCE_DIRECTORY_PATH) {
  const balances = await getFinanceBalancesByReg(page, [reg], path);
  return balances[reg];
}

async function getFinanceHistoryEntries(page, count = 1, path = './index.php?id=200&subid=10') {
  if (path) {
    await page.goto(path);
  }

  const rows = page.locator('tr[data-group]');
  const rowCount = await rows.count();
  if (rowCount === 0) {
    throw new Error('No finance history entries were found');
  }

  const entries = [];
  const limit = Math.min(count, rowCount);

  for (let index = 0; index < limit; index += 1) {
    const cells = await rows.nth(index).locator('td').evaluateAll((nodes) => (
      nodes.map((node) => (node.textContent || '').trim())
    ));

    entries.push({
      date: cells[0] || '',
      race: cells[1] || '',
      raceDate: cells[2] || '',
      amount: cells[3] || '',
      note: cells[4] || '',
      editor: cells[5] || '',
    });
  }

  return entries;
}

async function getFirstFinanceHistoryEntry(page, path = './index.php?id=200&subid=10') {
  const [entry] = await getFinanceHistoryEntries(page, 1, path);
  return entry;
}

function sumBalances(balances) {
  return Object.values(balances).reduce((sum, entry) => sum + entry.amount, 0);
}

async function openPopupMenuItem(page, label) {
  const [popup] = await Promise.all([
    page.waitForEvent('popup'),
    page.locator('a', { hasText: label }).first().click(),
  ]);

  await popup.waitForLoadState('domcontentloaded');
  return popup;
}

async function createMockBankTransaction(request, overrides = {}) {
  const response = await request.post(DEFAULT_BANK_MOCK_TRANSACTIONS_URL, {
    data: {
      amount: Number(overrides.amount ?? 1500),
      variableSymbol: overrides.variableSymbol ?? '8357',
      originatorMessage: overrides.originatorMessage ?? '',
      bookingDate: overrides.bookingDate ?? new Date().toISOString(),
      valueDate: overrides.valueDate ?? overrides.bookingDate ?? new Date().toISOString(),
      counterpartyName: overrides.counterpartyName ?? 'Playwright Bank Mock',
      ...overrides.fields,
    },
  });

  if (!response.ok()) {
    throw new Error(`Create mock bank transaction failed with HTTP ${response.status()}: ${await response.text()}`);
  }

  return response;
}

async function setBankMockSettings(request, overrides = {}) {
  const response = await request.post(DEFAULT_BANK_MOCK_SETTINGS_URL, {
    data: overrides,
  });

  if (!response.ok()) {
    throw new Error(`Update mock bank settings failed with HTTP ${response.status()}: ${await response.text()}`);
  }

  return response;
}

module.exports = {
  createMockBankTransaction,
  getFinalMemberBalance,
  getFinanceBalanceByReg,
  getFinanceBalancesByReg,
  getFirstFinanceHistoryEntry,
  getFinanceHistoryEntries,
  openPopupMenuItem,
  setBankMockSettings,
  sumBalances,
};
