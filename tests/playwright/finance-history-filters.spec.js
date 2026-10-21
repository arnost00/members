const { test, expect } = require('@playwright/test');
const { loginAs } = require('./helpers/browser');

const HISTORY_PATH = './index.php?id=800&subid=7';

function rangeHeading(page, range) {
  return page.locator(`tr[data-range-heading="${range}"] .time-range-expander`);
}

function rangeRows(page, range) {
  return page.locator(`tr[data-group="${range}"]`);
}

test.describe('Finance history filters and lazy ranges', () => {
  test('loads one month initially and caches a month loaded on demand', async ({ page }) => {
    await loginAs(page, 'accountant');
    await page.goto(HISTORY_PATH);

    const headings = page.locator('tr[data-range-heading]');
    await expect(headings).not.toHaveCount(0);
    expect(await headings.count()).toBeGreaterThan(1);
    await expect(page.locator('.time-range-expander[aria-expanded="true"]')).toHaveCount(1);

    const targetRange = '2020-10';
    const targetHeading = rangeHeading(page, targetRange);
    await expect(targetHeading).toBeVisible();
    await expect(targetHeading).toHaveAttribute('data-loaded', '0');
    await expect(rangeRows(page, targetRange)).toHaveCount(0);

    let requests = 0;
    page.on('request', (request) => {
      if (request.url().includes(`fin_history_range.php?range=${targetRange}`)) {
        requests += 1;
      }
    });

    await targetHeading.click();
    await expect(targetHeading).toHaveAttribute('aria-expanded', 'true');
    await expect(rangeRows(page, targetRange)).not.toHaveCount(0);
    await expect(rangeRows(page, targetRange).first()).toBeVisible();
    expect(requests).toBe(1);

    await targetHeading.click();
    await expect(targetHeading).toHaveAttribute('aria-expanded', 'false');
    await expect(rangeRows(page, targetRange).first()).toBeHidden();

    await targetHeading.click();
    await expect(targetHeading).toHaveAttribute('aria-expanded', 'true');
    await expect(rangeRows(page, targetRange).first()).toBeVisible();
    expect(requests).toBe(1);
  });

  test('applies normalized filters to ranges and rendered rows', async ({ page }) => {
    await loginAs(page, 'accountant');
    await page.goto(`${HISTORY_PATH}&date_from=2023-11-01&date_to=2023-11-30&member=7503&amount_from=100&amount_to=100&note=Cesta`);

    await expect(page.locator('tr[data-range-heading]')).toHaveCount(1);
    await expect(rangeHeading(page, '2023-11')).toHaveAttribute('aria-expanded', 'true');
    await expect(rangeRows(page, '2023-11')).toHaveCount(1);
    await expect(rangeRows(page, '2023-11')).toContainText('7503');
    await expect(rangeRows(page, '2023-11')).toContainText('100');
    await expect(rangeRows(page, '2023-11')).toContainText('Cesta');
    await expect(page.getByRole('link', { name: 'Zrušit filtr' })).toBeVisible();

    await page.goto(`${HISTORY_PATH}&claim_only=1`);
    await expect(page.locator('input[name="claim_only"]')).toBeChecked();
    const claimRange = page.locator('.time-range-expander').first();
    if (await claimRange.count()) {
      await expect(claimRange).toHaveAttribute('data-expand-url', /claim_only=1/);
    }

    await page.goto(`${HISTORY_PATH}&note=playwright-no-such-finance-history-entry`);
    await expect(page.getByText('Nebyly nalezeny žádné záznamy.')).toBeVisible();

    await page.getByRole('link', { name: 'Zrušit filtr' }).click();
    await expect(page.locator('input[name="date_from"]')).toHaveValue('');
    await expect(page.locator('input[name="date_to"]')).toHaveValue('');
    await expect(page.locator('tr[data-range-heading]')).not.toHaveCount(0);
  });

  test('protects and validates the range fragment endpoint', async ({ page, request }) => {
    const anonymous = await request.get('./fin_history_range.php?range=2020-10');
    expect(anonymous.status()).toBe(403);

    await loginAs(page, 'accountant');
    const malformed = await page.context().request.get('./fin_history_range.php?range=2020-99');
    expect(malformed.status()).toBe(400);

    const constrained = await page.context().request.get('./fin_history_range.php?range=2020-10&note=Cesta');
    expect(constrained.status()).toBe(404);

    const valid = await page.context().request.get('./fin_history_range.php?range=2020-10');
    expect(valid.status()).toBe(200);
    expect(await valid.text()).toContain('data-group="2020-10"');
  });

  test('preserves unmatched-bank-payment filters through prepared querying', async ({ page }) => {
    await loginAs(page, 'accountant');
    await page.goto('./index.php?id=800&subid=6');

    await expect(page.locator('input[name="date_from"]')).not.toHaveValue('');
    await expect(page.locator('input[name="date_to"]')).not.toHaveValue('');
    await page.locator('input[name="date_from"]').fill('');
    await page.locator('input[name="date_to"]').fill('');
    await page.getByRole('button', { name: 'Filtrovat' }).click();
    await expect(page.locator('input[name="date_from"]')).not.toHaveValue('');
    await expect(page.locator('input[name="date_to"]')).not.toHaveValue('');

    await page.locator('input[name="variable_symbol"]').fill('playwright-no-such-vs');
    await page.locator('input[name="amount_from"]').fill('123');
    await page.locator('input[name="amount_to"]').fill('456');
    await page.locator('input[name="message"]').fill('playwright-no-such-bank-message');
    await page.getByRole('button', { name: 'Filtrovat' }).click();

    await expect(page.locator('input[name="variable_symbol"]')).toHaveValue('playwright-no-such-vs');
    await expect(page.locator('input[name="amount_from"]')).toHaveValue('123');
    await expect(page.locator('input[name="amount_to"]')).toHaveValue('456');
    await expect(page.locator('input[name="message"]')).toHaveValue('playwright-no-such-bank-message');
    await expect(page.getByText('Nebyly nalezeny žádné nespárované platby v tomto období.')).toBeVisible();
  });
});
