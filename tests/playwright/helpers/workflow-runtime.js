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

module.exports = {
  addUtcDays,
  createWorkflowRun,
  formatCzDate,
};
