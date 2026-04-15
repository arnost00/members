function pad(value) {
  return String(value).padStart(2, '0');
}

function formatCzDate(date) {
  return `${pad(date.getUTCDate())}.${pad(date.getUTCMonth() + 1)}.${date.getUTCFullYear()}`;
}

function addUtcDays(baseDate, days) {
  const next = new Date(baseDate.getTime());
  next.setUTCDate(next.getUTCDate() + days);
  return next;
}

function createWorkflowRun(name) {
  const now = new Date();
  const stamp = [
    now.getUTCFullYear(),
    pad(now.getUTCMonth() + 1),
    pad(now.getUTCDate()),
    '-',
    pad(now.getUTCHours()),
    pad(now.getUTCMinutes()),
    pad(now.getUTCSeconds()),
  ].join('');
  const suffix = Math.random().toString(36).slice(2, 8);
  const runId = `${stamp}-${suffix}`;

  return {
    name,
    runId,
  };
}

async function expandYear(page, year) {
  const normalizedYear = String(year);
  const expander = page.locator(
    `span.year-expander[onclick="toggle_expand_by_group('${normalizedYear}', this)"]`
  ).first();

  if (await expander.count() === 0) {
    return false;
  }

  const label = String(await expander.textContent() || '').replace(/\s+/g, ' ').trim();
  if (label === `▼ ${normalizedYear}`) {
    await expander.click();
    return true;
  }

  return false;
}

module.exports = {
  addUtcDays,
  createWorkflowRun,
  expandYear,
  formatCzDate,
};
