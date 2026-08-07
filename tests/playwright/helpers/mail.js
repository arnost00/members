const MAILPIT_BASE_URL = process.env.MAILPIT_URL || 'http://mailpit:8025';

async function clearMailbox(request) {
  const response = await request.delete(`${MAILPIT_BASE_URL}/api/v1/messages`);

  if (!response.ok()) {
    throw new Error(`Failed to clear mailpit mailbox: HTTP ${response.status()}`);
  }
}

async function findMessageSummary(request, recipient, subject) {
  const response = await request.get(`${MAILPIT_BASE_URL}/api/v1/messages`);

  if (!response.ok()) {
    throw new Error(`Failed to list mailpit messages: HTTP ${response.status()}`);
  }

  const { messages } = await response.json();

  return messages.find((message) => {
    const matchesRecipient = message.To.some((addr) => addr.Address === recipient);
    const matchesSubject = subject === undefined || message.Subject === subject;
    return matchesRecipient && matchesSubject;
  });
}

async function fetchFullMessage(request, id) {
  const response = await request.get(`${MAILPIT_BASE_URL}/api/v1/message/${id}`);

  if (!response.ok()) {
    throw new Error(`Failed to load mailpit message ${id}: HTTP ${response.status()}`);
  }

  const message = await response.json();

  return {
    subject: message.Subject,
    text: message.Text,
    html: message.HTML,
    to: message.To,
    from: message.From,
  };
}

async function waitForEmailTo(request, recipient, { subject, timeoutMs = 5000 } = {}) {
  const deadline = Date.now() + timeoutMs;

  while (Date.now() < deadline) {
    const summary = await findMessageSummary(request, recipient, subject);

    if (summary) {
      return fetchFullMessage(request, summary.ID);
    }

    await new Promise((resolve) => setTimeout(resolve, 250));
  }

  throw new Error(
    `No email to '${recipient}'${subject ? ` with subject '${subject}'` : ''} arrived within ${timeoutMs}ms`,
  );
}

module.exports = {
  clearMailbox,
  waitForEmailTo,
};
