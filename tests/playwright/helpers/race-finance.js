const { expect } = require('@playwright/test');
const {
  openPopup,
} = require('./browser');

function financeRow(page, memberName) {
  const row = page.locator('tr', {
    has: page.locator('a.adr_name', { hasText: memberName }),
  }).first();

  return {
    row,
    state: row.locator('.state'),
    amount: row.locator('input[data-col="amount"]'),
    note: row.locator('input[data-col="note"]'),
    entryFee: row.locator('[data-col="entryFee"]'),
    transport: row.locator('[data-col="transport"]'),
    accommodation: row.locator('[data-col="accommodation"]'),
  };
}

async function expectFinanceRowValues(finance, expected) {
  await expect(finance.row).toBeVisible();

  if (Object.prototype.hasOwnProperty.call(expected, 'state')) {
    await expect(finance.state).toHaveText(expected.state);
  }

  if (Object.prototype.hasOwnProperty.call(expected, 'amount')) {
    await expect(finance.amount).toHaveValue(expected.amount);
  }

  if (Object.prototype.hasOwnProperty.call(expected, 'note')) {
    await expect(finance.note).toHaveValue(expected.note);
  }

  if (Object.prototype.hasOwnProperty.call(expected, 'entryFee')) {
    await expect(finance.entryFee).toContainText(expected.entryFee);
  }

  if (Object.prototype.hasOwnProperty.call(expected, 'transport')) {
    await expect(finance.transport).toHaveText(expected.transport);
  }

  if (Object.prototype.hasOwnProperty.call(expected, 'accommodation')) {
    await expect(finance.accommodation).toHaveText(expected.accommodation);
  }
}

async function openRaceFinancePopup(page, raceId) {
  return openPopup(page, async () => {
    await page.evaluate((currentRaceId) => {
      window.open(`./race_finance_view.php?race_id=${currentRaceId}`, '', 'width=800,height=800');
    }, raceId);
  });
}

async function runRaceFinanceWizard(page) {
  await expect(page.locator('button[title="Vyplň platby podle pravidel"]')).toBeVisible();
  await page.waitForFunction(() => typeof window.fillTableFromInput === 'function');
  await page.evaluate(() => {
    window.fillTableFromInput('payrule', {
      preventDefault() {},
    });
  });
}

module.exports = {
  expectFinanceRowValues,
  financeRow,
  openRaceFinancePopup,
  runRaceFinanceWizard,
};
