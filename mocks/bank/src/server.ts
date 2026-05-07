import crypto from 'node:crypto';
import express, { type Request, type Response } from 'express';
import mysql, { type Pool, type PoolConnection, type RowDataPacket } from 'mysql2/promise';

type RuntimeMode = 'normal' | 'force_client_error' | 'delay' | 'hang' | 'close_connection';

type MockSettings = {
  mode: RuntimeMode;
  responseDelayMs: number;
  forceStatusCode: number;
  pageSize: number;
};

type TransactionRow = RowDataPacket & {
  id: number;
  entry_reference: string;
  account_number: string;
  currency: string;
  amount_value: string;
  booking_date: string;
  value_date: string;
  variable_symbol: string | null;
  constant_symbol: string | null;
  specific_symbol: string | null;
  originator_message: string | null;
  counterparty_account: string | null;
  counterparty_name: string | null;
  created_at: string;
};

type SettingsRow = RowDataPacket & {
  mode: RuntimeMode;
  response_delay_ms: number;
  force_status_code: number;
  page_size: number;
};

type CreateTransactionInput = {
  accountNumber?: string;
  amount: number;
  currency?: string;
  bookingDate?: string;
  valueDate?: string;
  variableSymbol?: string;
  constantSymbol?: string;
  specificSymbol?: string;
  originatorMessage?: string;
  counterpartyAccount?: string;
  counterpartyName?: string;
};

const config = {
  host: process.env.BANK_MOCK_HOST ?? '0.0.0.0',
  port: parseInt(process.env.BANK_MOCK_PORT ?? '10300', 10),
  dbHost: process.env.BANK_MOCK_DB_HOST ?? 'db',
  dbPort: parseInt(process.env.BANK_MOCK_DB_PORT ?? '3306', 10),
  dbUser: process.env.BANK_MOCK_DB_USER ?? 'root',
  dbPassword: process.env.BANK_MOCK_DB_PASSWORD ?? 'dev4password',
  dbName: process.env.BANK_MOCK_DB_NAME ?? 'rb_mock',
  defaultAccountNumber: process.env.BANK_MOCK_DEFAULT_ACCOUNT ?? '4067843369',
  defaultCurrency: process.env.BANK_MOCK_DEFAULT_CURRENCY ?? 'CZK',
  initialTransactionCount: parseInt(process.env.BANK_MOCK_INITIAL_TRANSACTION_COUNT ?? '14', 10),
  initialTransactionMinAgeDays: parseInt(process.env.BANK_MOCK_INITIAL_TRANSACTION_MIN_AGE_DAYS ?? '3', 10),
};

let pool: Pool;

function toDateTimeLocalValue(value?: string): string {
  const date = value ? new Date(value) : new Date();
  if (Number.isNaN(date.getTime())) {
    return new Date().toISOString().slice(0, 16);
  }

  const pad = (n: number) => String(n).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function escapeHtml(value: unknown): string {
  const normalized = value == null
    ? ''
    : value instanceof Date
      ? value.toISOString()
      : String(value);

  return normalized
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function buildEntryReference(date: Date): string {
  return `RBMOCK-${date.getUTCFullYear()}${String(date.getUTCMonth() + 1).padStart(2, '0')}${String(date.getUTCDate()).padStart(2, '0')}-${crypto.randomUUID().slice(0, 8).toUpperCase()}`;
}

function randomInt(min: number, max: number): number {
  return crypto.randomInt(min, max + 1);
}

function generateSeedVariableSymbol(): string {
  const prefix = ((randomInt(0, 80) + 45) % 100).toString().padStart(2, '0');
  const suffix = (randomInt(0, 12) + randomInt(0, 1) * 50).toString().padStart(2, '0');
  return `${prefix}${suffix}`;
}

function buildClientErrorPayload(statusCode: number): { body: Record<string, unknown>; headers?: Record<string, string> } {
  if (statusCode === 401) {
    return {
      body: {
        error: 'UNAUTHORIZED',
        message: 'Mock unauthorized response from the bank API.',
      },
    };
  }

  if (statusCode === 403) {
    return {
      body: {
        error: 'FORBIDDEN',
        message: 'Mock forbidden response from the bank API.',
      },
    };
  }

  if (statusCode === 404) {
    return {
      body: {
        error: 'NOT_FOUND',
        message: 'Mock resource not found response from the bank API.',
      },
    };
  }

  if (statusCode === 429) {
    return {
      headers: {
        'Retry-After': '1',
        'X-RateLimit-Limit-Second': '10',
        'X-RateLimit-Limit-Day': '5000',
        'X-RateLimit-Remaining-Second': '0',
        'X-RateLimit-Remaining-Day': '0',
      },
      body: {
        error: 'RATE_LIMIT_EXCEEDED',
        message: 'Mock rate limit exceeded response from the bank API.',
      },
    };
  }

  return {
    body: {
      error: 'BAD_REQUEST',
      message: 'Mock client error response from the bank API.',
    },
  };
}

async function ensureDatabase(): Promise<void> {
  const adminConnection = await mysql.createConnection({
    host: config.dbHost,
    port: config.dbPort,
    user: config.dbUser,
    password: config.dbPassword,
    multipleStatements: true,
  });

  try {
    await adminConnection.query(`CREATE DATABASE IF NOT EXISTS \`${config.dbName}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`);
  } finally {
    await adminConnection.end();
  }

  pool = mysql.createPool({
    host: config.dbHost,
    port: config.dbPort,
    user: config.dbUser,
    password: config.dbPassword,
    database: config.dbName,
    waitForConnections: true,
    connectionLimit: 10,
    namedPlaceholders: true,
  });

  await pool.query(`
    CREATE TABLE IF NOT EXISTS mock_settings (
      settings_key VARCHAR(64) NOT NULL PRIMARY KEY,
      mode VARCHAR(32) NOT NULL DEFAULT 'normal',
      response_delay_ms INT NOT NULL DEFAULT 0,
      force_status_code INT NOT NULL DEFAULT 400,
      page_size INT NOT NULL DEFAULT 50,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
  `);

  await pool.query(`
    ALTER TABLE mock_settings
    MODIFY mode VARCHAR(32) NOT NULL DEFAULT 'normal'
  `);

  await pool.query(`
    CREATE TABLE IF NOT EXISTS mock_transactions (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      entry_reference VARCHAR(64) NOT NULL UNIQUE,
      account_number VARCHAR(64) NOT NULL,
      currency VARCHAR(8) NOT NULL DEFAULT 'CZK',
      amount_value DECIMAL(12, 2) NOT NULL,
      booking_date DATETIME NOT NULL,
      value_date DATETIME NOT NULL,
      variable_symbol VARCHAR(32) NULL,
      constant_symbol VARCHAR(32) NULL,
      specific_symbol VARCHAR(32) NULL,
      originator_message VARCHAR(255) NULL,
      counterparty_account VARCHAR(64) NULL,
      counterparty_name VARCHAR(255) NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_account_booking (account_number, booking_date),
      INDEX idx_booking_date (booking_date)
    )
  `);

  await pool.query(`
    INSERT INTO mock_settings (settings_key)
    VALUES ('default')
    ON DUPLICATE KEY UPDATE settings_key = VALUES(settings_key)
  `);

  const [countRows] = await pool.query<RowDataPacket[]>('SELECT COUNT(*) AS count FROM mock_transactions');
  const currentCount = Number(countRows[0]?.count ?? 0);
  if (currentCount === 0) {
    await seedInitialTransactions();
  }
}

async function closeDatabase(): Promise<void> {
  if (pool) {
    await pool.end();
  }
}

async function seedInitialTransactions(): Promise<void> {
  const connection = await pool.getConnection();
  try {
    await connection.beginTransaction();

    for (let index = config.initialTransactionCount - 1; index >= 0; index -= 1) {
      const bookingDate = new Date();
      bookingDate.setUTCDate(bookingDate.getUTCDate() - (index + config.initialTransactionMinAgeDays));
      bookingDate.setUTCHours(8 + (index % 9), (index * 7) % 60, 0, 0);

      const amount = 150 + (config.initialTransactionCount - index) * 25;
      const variableSymbol = generateSeedVariableSymbol();
      const specificSymbol = index % 3 === 0 ? `10${index}` : '';
      const message = index % 2 === 0 ? `Initial mock payment ${config.initialTransactionCount - index}` : 'Membership fee';

      await insertTransaction(connection, {
        amount,
        accountNumber: config.defaultAccountNumber,
        currency: config.defaultCurrency,
        bookingDate: bookingDate.toISOString(),
        valueDate: bookingDate.toISOString(),
        variableSymbol,
        constantSymbol: '0558',
        specificSymbol,
        originatorMessage: message,
        counterpartyAccount: `210${String(index).padStart(6, '0')}/2010`,
        counterpartyName: `Mock Sender ${config.initialTransactionCount - index}`,
      });
    }

    await connection.commit();
  } catch (error) {
    await connection.rollback();
    throw error;
  } finally {
    connection.release();
  }
}

async function insertTransaction(connection: PoolConnection, input: CreateTransactionInput): Promise<void> {
  const bookingDate = input.bookingDate ? new Date(input.bookingDate) : new Date();
  const valueDate = input.valueDate ? new Date(input.valueDate) : bookingDate;
  const variableSymbol = input.variableSymbol?.trim() || generateSeedVariableSymbol();

  if (Number.isNaN(bookingDate.getTime())) {
    throw new Error('Invalid bookingDate');
  }

  if (Number.isNaN(valueDate.getTime())) {
    throw new Error('Invalid valueDate');
  }

  await connection.query(
    `
      INSERT INTO mock_transactions (
        entry_reference,
        account_number,
        currency,
        amount_value,
        booking_date,
        value_date,
        variable_symbol,
        constant_symbol,
        specific_symbol,
        originator_message,
        counterparty_account,
        counterparty_name
      )
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `,
    [
      buildEntryReference(bookingDate),
      input.accountNumber ?? config.defaultAccountNumber,
      input.currency ?? config.defaultCurrency,
      input.amount.toFixed(2),
      bookingDate.toISOString().slice(0, 19).replace('T', ' '),
      valueDate.toISOString().slice(0, 19).replace('T', ' '),
      variableSymbol,
      input.constantSymbol?.trim() || null,
      input.specificSymbol?.trim() || null,
      input.originatorMessage?.trim() || null,
      input.counterpartyAccount?.trim() || null,
      input.counterpartyName?.trim() || null,
    ],
  );
}

async function getSettings(): Promise<MockSettings> {
  const [rows] = await pool.query<SettingsRow[]>(
    'SELECT mode, response_delay_ms, force_status_code, page_size FROM mock_settings WHERE settings_key = ? LIMIT 1',
    ['default'],
  );

  const row = rows[0];
  return {
    mode: row?.mode ?? 'normal',
    responseDelayMs: Number(row?.response_delay_ms ?? 0),
    forceStatusCode: Number(row?.force_status_code ?? 400),
    pageSize: Number(row?.page_size ?? 50),
  };
}

async function updateSettings(patch: Partial<MockSettings>): Promise<MockSettings> {
  const current = await getSettings();
  const next: MockSettings = {
    mode: patch.mode ?? current.mode,
    responseDelayMs: Math.max(0, Math.min(600000, patch.responseDelayMs ?? current.responseDelayMs)),
    forceStatusCode: Math.max(400, Math.min(499, patch.forceStatusCode ?? current.forceStatusCode)),
    pageSize: Math.max(1, Math.min(500, patch.pageSize ?? current.pageSize)),
  };

  await pool.query(
    `
      UPDATE mock_settings
      SET mode = ?, response_delay_ms = ?, force_status_code = ?, page_size = ?
      WHERE settings_key = ?
    `,
    [next.mode, next.responseDelayMs, next.forceStatusCode, next.pageSize, 'default'],
  );

  return next;
}

function parseNumber(value: unknown, fallback: number): number {
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : fallback;
}

function normalizeTransactionInput(source: Record<string, unknown>): CreateTransactionInput {
  const amount = parseNumber(source.amount, NaN);
  if (!Number.isFinite(amount) || amount === 0) {
    throw new Error('Amount must be a non-zero number');
  }

  return {
    accountNumber: typeof source.accountNumber === 'string' ? source.accountNumber : undefined,
    amount,
    currency: typeof source.currency === 'string' ? source.currency : undefined,
    bookingDate: typeof source.bookingDate === 'string' ? source.bookingDate : undefined,
    valueDate: typeof source.valueDate === 'string' ? source.valueDate : undefined,
    variableSymbol: typeof source.variableSymbol === 'string' ? source.variableSymbol : undefined,
    constantSymbol: typeof source.constantSymbol === 'string' ? source.constantSymbol : undefined,
    specificSymbol: typeof source.specificSymbol === 'string' ? source.specificSymbol : undefined,
    originatorMessage: typeof source.originatorMessage === 'string' ? source.originatorMessage : undefined,
    counterpartyAccount: typeof source.counterpartyAccount === 'string' ? source.counterpartyAccount : undefined,
    counterpartyName: typeof source.counterpartyName === 'string' ? source.counterpartyName : undefined,
  };
}

function formatApiDate(value: string | Date): string {
  const date = value instanceof Date
    ? value
    : new Date(value.includes('T') ? value : value.replace(' ', 'T') + 'Z');

  return date.toISOString().slice(0, 10);
}

async function maybeApplyFaultMode(req: Request, res: Response): Promise<boolean> {
  const settings = await getSettings();

  if (settings.mode === 'delay' && settings.responseDelayMs > 0) {
    await new Promise((resolve) => setTimeout(resolve, settings.responseDelayMs));
  }

  if (settings.mode === 'force_client_error') {
    const { body, headers } = buildClientErrorPayload(settings.forceStatusCode);
    if (headers) {
      for (const [headerName, headerValue] of Object.entries(headers)) {
        res.setHeader(headerName, headerValue);
      }
    }

    res.status(settings.forceStatusCode).json({
      ...body,
      mode: settings.mode,
      statusCode: settings.forceStatusCode,
    });
    return true;
  }

  if (settings.mode === 'hang') {
    req.socket.setTimeout(0);
    return new Promise<boolean>(() => {
      // Intentionally keep the request open to simulate an upstream hang.
    });
  }

  if (settings.mode === 'close_connection') {
    req.socket.destroy();
    return true;
  }

  return false;
}

function renderAdminPage(settings: MockSettings, transactions: TransactionRow[]): string {
  const rows = transactions
    .map((transaction) => {
      const symbols = [
        transaction.variable_symbol ? `VS ${transaction.variable_symbol}` : null,
        transaction.constant_symbol ? `KS ${transaction.constant_symbol}` : null,
        transaction.specific_symbol ? `SS ${transaction.specific_symbol}` : null,
      ]
        .filter(Boolean)
        .join(' | ');

      return `
        <tr>
          <td>${escapeHtml(transaction.entry_reference)}</td>
          <td>${escapeHtml(transaction.account_number)}</td>
          <td>${escapeHtml(transaction.amount_value)} ${escapeHtml(transaction.currency)}</td>
          <td>${escapeHtml(transaction.booking_date)}</td>
          <td>${escapeHtml(symbols || '-')}</td>
          <td>${escapeHtml(transaction.originator_message ?? '-')}</td>
        </tr>
      `;
    })
    .join('');

  const selected = (mode: RuntimeMode) => (settings.mode === mode ? 'selected' : '');

  return `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Raiffeisen Mock Bank</title>
  <style>
    :root {
      --bg: #f7f2ea;
      --panel: rgba(255, 255, 255, 0.88);
      --ink: #1f2933;
      --muted: #52606d;
      --accent: #b23a48;
      --accent-dark: #7b1e2b;
      --line: rgba(31, 41, 51, 0.12);
      --shadow: 0 22px 40px rgba(84, 58, 20, 0.14);
    }

    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: Georgia, "Times New Roman", serif;
      color: var(--ink);
      background:
        radial-gradient(circle at top left, rgba(178, 58, 72, 0.16), transparent 30%),
        radial-gradient(circle at top right, rgba(174, 124, 57, 0.14), transparent 24%),
        linear-gradient(180deg, #f9f4ec, #efe5d7);
    }
    main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 32px 18px 48px;
    }
    h1, h2 {
      margin: 0 0 12px;
      font-weight: 600;
      letter-spacing: 0.02em;
    }
    p, label, input, select, button, textarea {
      font-size: 16px;
    }
    .hero {
      margin-bottom: 24px;
      padding: 28px;
      border-radius: 24px;
      background: linear-gradient(135deg, rgba(255,255,255,0.82), rgba(255,255,255,0.62));
      box-shadow: var(--shadow);
      border: 1px solid rgba(255,255,255,0.65);
    }
    .hero p { color: var(--muted); max-width: 760px; }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 18px;
      margin-bottom: 20px;
    }
    .card {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 22px;
      padding: 22px;
      box-shadow: var(--shadow);
      backdrop-filter: blur(10px);
    }
    form {
      display: grid;
      gap: 12px;
    }
    .two-col {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
    }
    input, select, textarea, button {
      width: 100%;
      border-radius: 14px;
      border: 1px solid rgba(31, 41, 51, 0.18);
      padding: 12px 14px;
      background: rgba(255,255,255,0.92);
      color: var(--ink);
    }
    button {
      border: none;
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      color: #fff;
      font-weight: 600;
      cursor: pointer;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: var(--panel);
      border-radius: 22px;
      overflow: hidden;
      box-shadow: var(--shadow);
    }
    th, td {
      text-align: left;
      padding: 12px 14px;
      border-bottom: 1px solid var(--line);
      vertical-align: top;
    }
    th {
      background: rgba(31, 41, 51, 0.04);
      font-size: 13px;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }
    .caption {
      margin: 0 0 16px;
      color: var(--muted);
    }
    code {
      background: rgba(31, 41, 51, 0.06);
      padding: 2px 6px;
      border-radius: 8px;
    }
    @media (max-width: 640px) {
      .two-col { grid-template-columns: 1fr; }
      table, thead, tbody, tr, td, th { display: block; }
      thead { display: none; }
      tr { border-bottom: 1px solid var(--line); }
      td { padding: 8px 14px; }
    }
  </style>
</head>
<body>
  <main>
    <section class="hero">
      <h1>Raiffeisen Premium API mock</h1>
      <p>
        Dev-only bank simulator for the members app. It persists transactions in MariaDB, exposes a
        Raiffeisen-compatible transaction endpoint, and lets us flip manual test behaviors without editing code.
      </p>
      <p>
        Public endpoint: <code>/rbcz/premium/api/accounts/${escapeHtml(config.defaultAccountNumber)}/${escapeHtml(config.defaultCurrency)}/transactions</code>
      </p>
    </section>

    <section class="grid">
      <article class="card">
        <h2>Create transaction</h2>
        <p class="caption">Omit the date to create a payment at the current time.</p>
        <form method="post" action="/__admin/transactions">
          <div class="two-col">
            <label>Amount
              <input type="number" step="0.01" name="amount" value="350.00" required />
            </label>
            <label>Account number
              <input type="text" name="accountNumber" value="${escapeHtml(config.defaultAccountNumber)}" />
            </label>
          </div>
          <div class="two-col">
            <label>Booking date
              <input type="datetime-local" name="bookingDate" value="${escapeHtml(toDateTimeLocalValue())}" />
            </label>
            <label>Value date
              <input type="datetime-local" name="valueDate" value="${escapeHtml(toDateTimeLocalValue())}" />
            </label>
          </div>
          <div class="two-col">
            <label>Variable symbol
              <input type="text" name="variableSymbol" value="" />
            </label>
            <label>Constant symbol
              <input type="text" name="constantSymbol" value="0558" />
            </label>
          </div>
          <div class="two-col">
            <label>Specific symbol
              <input type="text" name="specificSymbol" value="" />
            </label>
            <label>Counterparty account
              <input type="text" name="counterpartyAccount" value="" />
            </label>
          </div>
          <label>Counterparty name
            <input type="text" name="counterpartyName" value="" />
          </label>
          <label>Message
            <textarea rows="3" name="originatorMessage"></textarea>
          </label>
          <button type="submit">Create transaction</button>
        </form>
      </article>

      <article class="card">
        <h2>Fault injection</h2>
        <p class="caption">Use the API or this form to flip between normal responses and controlled failures.</p>
        <form method="post" action="/__admin/settings">
          <label>Mode
            <select name="mode">
              <option value="normal" ${selected('normal')}>normal</option>
              <option value="force_client_error" ${selected('force_client_error')}>force_client_error</option>
              <option value="delay" ${selected('delay')}>delay</option>
              <option value="hang" ${selected('hang')}>hang</option>
              <option value="close_connection" ${selected('close_connection')}>close_connection</option>
            </select>
          </label>
          <div class="two-col">
            <label>Response delay (ms)
              <input type="number" min="0" max="600000" name="responseDelayMs" value="${settings.responseDelayMs}" />
            </label>
            <label>Forced 4xx status
              <input type="number" min="400" max="499" name="forceStatusCode" value="${settings.forceStatusCode}" />
            </label>
          </div>
          <p class="caption">Common RB client-error presets: 400, 401, 403, 404, 429. Any status from 400 to 499 can be forced.</p>
          <label>Page size
            <input type="number" min="1" max="500" name="pageSize" value="${settings.pageSize}" />
          </label>
          <button type="submit">Save settings</button>
        </form>
      </article>
    </section>

    <section>
      <h2>Stored transactions</h2>
      <p class="caption">Latest transactions first. The mock API filters by account and date range, then paginates.</p>
      <table>
        <thead>
          <tr>
            <th>Entry reference</th>
            <th>Account</th>
            <th>Amount</th>
            <th>Booking date</th>
            <th>Symbols</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </section>
  </main>
</body>
</html>`;
}

async function main(): Promise<void> {
  await ensureDatabase();

  if (process.argv.includes('--init-only')) {
    await closeDatabase();
    return;
  }

  const app = express();
  app.use(express.json());
  app.use(express.urlencoded({ extended: true }));

  app.get('/health', async (_req, res) => {
    const settings = await getSettings();
    res.json({
      ok: true,
      database: config.dbName,
      settings,
    });
  });

  app.get('/__admin', async (_req, res) => {
    const settings = await getSettings();
    const [transactions] = await pool.query<TransactionRow[]>(
      `
        SELECT id, entry_reference, account_number, currency, amount_value, booking_date, value_date,
               variable_symbol, constant_symbol, specific_symbol, originator_message,
               counterparty_account, counterparty_name, created_at
        FROM mock_transactions
        ORDER BY booking_date DESC, id DESC
        LIMIT 100
      `,
    );
    res.type('html').send(renderAdminPage(settings, transactions));
  });

  app.get('/__admin/api/settings', async (_req, res) => {
    res.json(await getSettings());
  });

  app.post('/__admin/api/settings', async (req, res) => {
    const settings = await updateSettings({
      mode: req.body.mode as RuntimeMode | undefined,
      responseDelayMs: req.body.responseDelayMs === undefined ? undefined : parseNumber(req.body.responseDelayMs, 0),
      forceStatusCode: req.body.forceStatusCode === undefined ? undefined : parseNumber(req.body.forceStatusCode, 400),
      pageSize: req.body.pageSize === undefined ? undefined : parseNumber(req.body.pageSize, 50),
    });

    res.json(settings);
  });

  app.post('/__admin/settings', async (req, res) => {
    await updateSettings({
      mode: req.body.mode as RuntimeMode | undefined,
      responseDelayMs: parseNumber(req.body.responseDelayMs, 0),
      forceStatusCode: parseNumber(req.body.forceStatusCode, 400),
      pageSize: parseNumber(req.body.pageSize, 50),
    });

    res.redirect('/__admin');
  });

  app.get('/__admin/api/transactions', async (req, res) => {
    const limit = Math.max(1, Math.min(500, parseNumber(req.query.limit, 100)));
    const [transactions] = await pool.query<TransactionRow[]>(
      `
        SELECT id, entry_reference, account_number, currency, amount_value, booking_date, value_date,
               variable_symbol, constant_symbol, specific_symbol, originator_message,
               counterparty_account, counterparty_name, created_at
        FROM mock_transactions
        ORDER BY booking_date DESC, id DESC
        LIMIT ?
      `,
      [limit],
    );

    res.json({
      transactions,
      count: transactions.length,
    });
  });

  app.post('/__admin/api/transactions', async (req, res) => {
    const input = normalizeTransactionInput(req.body as Record<string, unknown>);
    const connection = await pool.getConnection();
    try {
      await insertTransaction(connection, input);
    } finally {
      connection.release();
    }

    res.status(201).json({ ok: true });
  });

  app.post('/__admin/transactions', async (req, res) => {
    const input = normalizeTransactionInput(req.body as Record<string, unknown>);
    const connection = await pool.getConnection();
    try {
      await insertTransaction(connection, input);
    } finally {
      connection.release();
    }

    res.redirect('/__admin');
  });

  app.get('/rbcz/premium/api/accounts/:accountNumber/CZK/transactions', async (req, res) => {
    if (await maybeApplyFaultMode(req, res)) {
      return;
    }

    const accountNumber = req.params.accountNumber;
    const from = typeof req.query.from === 'string' ? req.query.from : undefined;
    const to = typeof req.query.to === 'string' ? req.query.to : undefined;
    const page = Math.max(0, parseNumber(req.query.page, 0));
    const settings = await getSettings();

    if (!from || !to) {
      res.status(400).json({
        error: 'MISSING_DATE_RANGE',
        message: 'Query params "from" and "to" are required.',
      });
      return;
    }

    const fromDate = new Date(`${from}T00:00:00Z`);
    const toDate = new Date(`${to}T23:59:59Z`);
    if (Number.isNaN(fromDate.getTime()) || Number.isNaN(toDate.getTime())) {
      res.status(400).json({
        error: 'INVALID_DATE_RANGE',
        message: 'Query params "from" and "to" must be valid YYYY-MM-DD values.',
      });
      return;
    }

    const offset = page * settings.pageSize;
    const [rows] = await pool.query<TransactionRow[]>(
      `
        SELECT id, entry_reference, account_number, currency, amount_value, booking_date, value_date,
               variable_symbol, constant_symbol, specific_symbol, originator_message,
               counterparty_account, counterparty_name, created_at
        FROM mock_transactions
        WHERE account_number = ?
          AND booking_date >= ?
          AND booking_date <= ?
        ORDER BY booking_date DESC, id DESC
        LIMIT ? OFFSET ?
      `,
      [
        accountNumber,
        fromDate.toISOString().slice(0, 19).replace('T', ' '),
        toDate.toISOString().slice(0, 19).replace('T', ' '),
        settings.pageSize,
        offset,
      ],
    );

    const transactions = rows.map((row) => ({
      entryReference: row.entry_reference,
      bookingDate: formatApiDate(row.booking_date),
      valueDate: formatApiDate(row.value_date),
      amount: {
        value: row.amount_value,
        currency: row.currency,
      },
      account: {
        accountNumber: row.account_number,
      },
      entryDetails: {
        transactionDetails: {
          remittanceInformation: {
            creditorReferenceInformation: {
              variable: row.variable_symbol ?? '',
              constant: row.constant_symbol ?? '',
              specific: row.specific_symbol ?? '',
            },
            originatorMessage: row.originator_message ?? '',
          },
          relatedParties: {
            debtor: {
              name: row.counterparty_name ?? '',
            },
            debtorAccount: {
              identification: {
                other: row.counterparty_account ?? '',
              },
            },
          },
        },
      },
    }));

    res.json({
      pageNumber: page,
      pageSize: settings.pageSize,
      transactions,
      lastPage: transactions.length < settings.pageSize,
    });
  });

  app.listen(config.port, config.host, () => {
    console.log(`Bank mock server listening on http://${config.host}:${config.port}`);
    console.log(`Admin UI: http://127.0.0.1:${config.port}/__admin`);
  });
}

main().catch((error) => {
  console.error('Failed to start bank mock server:', error);
  process.exit(1);
});
