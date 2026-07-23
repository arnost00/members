import crypto from 'node:crypto';
import fs from 'node:fs/promises';
import path from 'node:path';
import express, { type Request, type Response } from 'express';
import mysql, { type Pool, type RowDataPacket } from 'mysql2/promise';

type JsonObject = Record<string, unknown>;

type RuntimeMode = 'normal' | 'force_client_error' | 'service_down' | 'delay' | 'hang' | 'close_connection';

type MockSettings = {
  mode: RuntimeMode;
  responseDelayMs: number;
  forceStatusCode: number;
};

type MockRow = RowDataPacket & {
  proxy_only: number;
  deleted: number;
};

type SettingsRow = RowDataPacket & {
  mode: RuntimeMode;
  response_delay_ms: number;
  force_status_code: number;
};

type EventRow = MockRow & {
  id: string;
  date: string | null;
  name: string | null;
  place: string | null;
  stages: number | null;
  sport_id: string | null;
  level_id: string | null;
  ranking: string | null;
  entry_date1: string | null;
  entry_date2: string | null;
  entry_date3: string | null;
  entry_koef2: number | null;
  entry_koef3: number | null;
  entry_start: string | null;
  org_abbr: string | null;
  region_id: string | null;
  cancelled: number | null;
};

type EventClassRow = MockRow & {
  event_id: string;
  class_id: string;
  name: string | null;
  fee: number | null;
};

type UserRow = MockRow & {
  user_id: string;
  club_user_id: string | null;
  reg_no: string | null;
  sport: number;
  year: number;
  first_name: string | null;
  last_name: string | null;
  si: string | null;
  club_id: string | null;
  licence: string | null;
};

type EntryRow = MockRow & {
  entry_id: string;
  event_id: string;
  club_user_id: string | null;
  reg_no: string | null;
  class_id: string | null;
  class_desc: string | null;
  name: string | null;
  rent_si: number | null;
  licence: string | null;
  fee: number | null;
  entry_stop: number | null;
  si: string | null;
  note: string | null;
};

type ServiceRow = MockRow & {
  id: number;
  service_id: string | null;
  event_id: string;
  club_user_id: string | null;
  name: string | null;
  amount: number | null;
  note: string | null;
};

const config = {
  host: process.env.ORIS_MOCK_HOST ?? '0.0.0.0',
  port: parseInt(process.env.ORIS_MOCK_PORT ?? '10301', 10),
  dbHost: process.env.ORIS_MOCK_DB_HOST ?? 'db',
  dbPort: parseInt(process.env.ORIS_MOCK_DB_PORT ?? '3306', 10),
  dbUser: process.env.ORIS_MOCK_DB_USER ?? 'root',
  dbPassword: process.env.ORIS_MOCK_DB_PASSWORD ?? 'dev4password',
  dbName: process.env.ORIS_MOCK_DB_NAME ?? 'oris_mock',
  upstreamBaseUrl: normalizeBaseUrl(process.env.ORIS_MOCK_UPSTREAM_BASE_URL ?? 'https://oris.ceskyorientak.cz/'),
  defaultClubId: process.env.ORIS_MOCK_DEFAULT_CLUB_ID ?? '205',
  defaultClubAbbr: process.env.ORIS_MOCK_DEFAULT_CLUB_ABBR ?? 'ZBM',
  apiLogFile: process.env.ORIS_MOCK_API_LOG_FILE ?? path.join(process.cwd(), 'logs', 'oris_mock_api.log'),
};

const TESTBENCH_BASE = '/__testbench';
const TESTBENCH_FAVICON_FILE = path.join(process.cwd(), 'mocks', 'oris', 'assets', 'testbench-favicon.svg');

const ORIS_SPORTS = [
  { id: '1', name: 'OB', description: 'Foot O' },
  { id: '2', name: 'LOB', description: 'Ski O' },
  { id: '3', name: 'MTBO', description: 'MTB O' },
  { id: '4', name: 'TRAIL', description: 'Trail' },
];

const ORIS_LEVELS = [
  { id: '1', name: 'MČR', description: 'Mistrovství ČR' },
  { id: '2', name: 'ŽA', description: 'Žebříček A' },
  { id: '3', name: 'ŽB', description: 'Žebříček B' },
  { id: '4', name: 'OŽ', description: 'Oblastní žebříček' },
  { id: '5', name: 'E', description: 'Jednotlivá etapa' },
  { id: '6', name: 'OST', description: 'Ostatní' },
  { id: '7', name: 'ČPŠ', description: 'Český pohár štafet' },
  { id: '8', name: 'ČP', description: 'Český pohár' },
  { id: '9', name: 'ČLK', description: 'Česká liga klubů' },
  { id: '10', name: 'SC', description: 'Sprint cup' },
  { id: '11', name: 'OM', description: 'Oblastní mistrovství' },
  { id: '12', name: 'WRE', description: 'World ranking' },
  { id: '13', name: 'ZL', description: 'Zimní liga' },
  { id: '14', name: 'OF', description: 'Ostatní oficiální' },
  { id: '15', name: 'SEM', description: 'Školení, schůze, semináře' },
  { id: '16', name: 'ET', description: 'Etapový závod' },
  { id: '17', name: 'VET', description: 'Veteraniáda ČR' },
  { id: '18', name: 'REG', description: 'Regionální závod' },
  { id: '19', name: 'STK', description: 'Stacionární tréninkový kemp' },
  { id: '20', name: 'PS', description: 'Přebor škol' },
  { id: '21', name: 'TC', description: 'Soustředění' },
  { id: '22', name: 'REPRE', description: 'Reprezentace OB' },
];

const ORIS_REGIONS = [
  { id: 'Č', name: 'Čechy' },
  { id: 'ČR', name: 'ČR' },
  { id: 'HA', name: 'Hanácká' },
  { id: 'JČ', name: 'Jihočeská' },
  { id: 'JE', name: 'Ještědská' },
  { id: 'JM', name: 'Jihomoravská' },
  { id: 'M', name: 'Morava' },
  { id: 'MSK', name: 'MS kraj' },
  { id: 'P', name: 'Pražská' },
  { id: 'StČ', name: 'Středočeská' },
  { id: 'VA', name: 'Valašská' },
  { id: 'VČ', name: 'Východočeská' },
  { id: 'VY', name: 'Vysočina' },
  { id: 'ZČ', name: 'Západočeská' },
];

let pool: Pool;

function normalizeBaseUrl(value: string): string {
  return value.replace(/\/API\/?$/i, '').replace(/\/?$/, '/');
}

function testbenchPath(suffix = ''): string {
  return `${TESTBENCH_BASE}${suffix}`;
}

function apiOk(data: unknown): JsonObject {
  return { Status: 'OK', Data: data };
}

function apiError(message: string): JsonObject {
  return { Status: 'ERROR', Data: message };
}

function apiClosedRegistration(method: string): JsonObject {
  return {
    Method: method,
    Format: 'json',
    Status: 'Mimo termín přihlášek',
    ExportCreated: new Date().toISOString().slice(0, 19).replace('T', ' '),
    Data: [],
  };
}

function isPlainObject(value: unknown): value is JsonObject {
  return value !== null && typeof value === 'object' && !Array.isArray(value);
}

function mergeJson(base: JsonObject, overlay: JsonObject): JsonObject {
  const merged: JsonObject = { ...base };
  for (const [key, value] of Object.entries(overlay)) {
    if (isPlainObject(value) && isPlainObject(merged[key])) {
      merged[key] = mergeJson(merged[key] as JsonObject, value);
      continue;
    }
    merged[key] = value;
  }
  return merged;
}

function asString(value: unknown, fallback = ''): string {
  if (value === undefined || value === null) return fallback;
  return String(value);
}

function asNumber(value: unknown, fallback: number): number {
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : fallback;
}

function asBool(value: unknown): boolean {
  return value === true || value === 'true' || value === '1' || value === 1;
}

function asRuntimeMode(value: unknown, fallback: RuntimeMode): RuntimeMode {
  const normalized = asString(value);
  if (
    normalized === 'normal'
    || normalized === 'force_client_error'
    || normalized === 'service_down'
    || normalized === 'delay'
    || normalized === 'hang'
    || normalized === 'close_connection'
  ) {
    return normalized;
  }
  return fallback;
}

function inputValue(input: JsonObject, ...keys: string[]): unknown {
  for (const key of keys) {
    if (!Object.prototype.hasOwnProperty.call(input, key)) continue;
    const value = input[key];
    if (value === undefined || value === null || value === '') continue;
    return value;
  }
  return null;
}

function nullableString(input: JsonObject, ...keys: string[]): string | null {
  const value = inputValue(input, ...keys);
  return value === null ? null : String(value);
}

function nullableNumber(input: JsonObject, ...keys: string[]): number | null {
  const value = inputValue(input, ...keys);
  if (value === null) return null;
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : null;
}

function nullableBool(input: JsonObject, ...keys: string[]): number | null {
  const value = inputValue(input, ...keys);
  return value === null ? null : asBool(value) ? 1 : 0;
}

function idList(value: unknown): string[] {
  if (Array.isArray(value)) {
    return Array.from(new Set(value.flatMap((item) => idList(item))));
  }
  if (isPlainObject(value)) {
    const directId = value.ID ?? value.id;
    if (directId !== undefined) return idList(directId);
    return Array.from(new Set(Object.values(value).flatMap((item) => idList(item))));
  }
  return asString(value)
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean);
}

function nullableIdList(input: JsonObject, ...keys: string[]): string | null {
  for (const key of keys) {
    if (!Object.prototype.hasOwnProperty.call(input, key)) continue;
    return idList(input[key]).join(',');
  }
  return null;
}

function sportName(value: unknown): string | null {
  const requested = isPlainObject(value) ? value.ID ?? value.id ?? value.NameCZ ?? value.name : value;
  const normalized = asString(requested).trim().toUpperCase();
  if (!normalized) return null;
  return ORIS_SPORTS.find((item) => item.id === normalized || item.name === normalized)?.name ?? null;
}

function levelNames(value: unknown): string[] {
  return Array.from(new Set(idList(value).flatMap((requested) => {
    const normalized = requested.toUpperCase();
    const level = ORIS_LEVELS.find((item) => item.id === normalized || item.name.toUpperCase() === normalized);
    return level ? [level.name] : [];
  })));
}

function nullableSportName(input: JsonObject, ...keys: string[]): string | null {
  for (const key of keys) {
    if (!Object.prototype.hasOwnProperty.call(input, key)) continue;
    return sportName(input[key]);
  }
  return null;
}

function nullableLevelNames(input: JsonObject, ...keys: string[]): string | null {
  for (const key of keys) {
    if (!Object.prototype.hasOwnProperty.call(input, key)) continue;
    return levelNames(input[key]).join(',');
  }
  return null;
}

function firstValue(value: unknown): string | undefined {
  if (Array.isArray(value)) return value.length > 0 ? asString(value[0]) : undefined;
  if (value === undefined) return undefined;
  return asString(value);
}

function requestValue(req: Request, ...keys: string[]): string | undefined {
  const body = isPlainObject(req.body) ? req.body : {};
  for (const key of keys) {
    const value = firstValue(req.query[key] ?? body[key]);
    if (value !== undefined && value !== '') return value;
  }
  return undefined;
}

function requestSearchParams(req: Request): URLSearchParams {
  const params = new URLSearchParams();
  const appendEntries = (source: unknown) => {
    if (!isPlainObject(source)) return;
    for (const [key, value] of Object.entries(source)) {
      const first = firstValue(value);
      if (first !== undefined) params.set(key, first);
    }
  };

  appendEntries(req.query);
  appendEntries(req.body);
  return params;
}

function registrationCandidates(regNo: string): string[] {
  const normalized = regNo.trim().toUpperCase();
  if (!normalized) return [];
  const withoutClub = normalized.replace(/^[A-Z]{3}/, '');
  const withClub = /^[A-Z]{3}/.test(normalized)
    ? normalized
    : `${config.defaultClubAbbr}${withoutClub.padStart(4, '0')}`;
  return Array.from(new Set([normalized, withClub, withoutClub]));
}

function todayPlus(days: number): string {
  const date = new Date();
  date.setUTCDate(date.getUTCDate() + days);
  return date.toISOString().slice(0, 10);
}

function dateTimeLocalPlus(days: number, hour = 20, minute = 0): string {
  const date = new Date();
  date.setUTCDate(date.getUTCDate() + days);
  date.setUTCHours(hour, minute, 0, 0);
  return date.toISOString().slice(0, 16);
}

function orisDateTimePlus(days: number, hour = 20, minute = 0): string {
  return normalizeDateTimeValue(dateTimeLocalPlus(days, hour, minute));
}

function normalizeDateTimeValue(value: unknown): string {
  const normalized = asString(value).trim();
  if (normalized === '') return '';
  if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(normalized)) {
    return `${normalized.replace('T', ' ')}:00`;
  }
  if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/.test(normalized)) {
    return normalized.replace('T', ' ');
  }
  return normalized;
}

function nullableDateTime(input: JsonObject, ...keys: string[]): string | null {
  const value = inputValue(input, ...keys);
  return value === null ? null : normalizeDateTimeValue(value);
}

function escapeHtml(value: unknown): string {
  return asString(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function jsonScript(value: unknown): string {
  return JSON.stringify(value).replace(/</g, '\\u003c');
}

function serializeLogParams(req: Request): string {
  const params = requestSearchParams(req);
  return params.toString();
}

async function appendApiLog(req: Request, statusCode: number, startedAt: number, closedEarly: boolean): Promise<void> {
  const methodName = asString(req.query.method ?? (isPlainObject(req.body) ? req.body.method : undefined), '-');
  const durationMs = Date.now() - startedAt;
  const line = [
    new Date().toISOString(),
    `status=${statusCode}`,
    `duration_ms=${durationMs}`,
    `http=${req.method}`,
    `method=${methodName || '-'}`,
    `closed=${closedEarly ? '1' : '0'}`,
    `params=${serializeLogParams(req)}`,
  ].join(' ');

  await fs.mkdir(path.dirname(config.apiLogFile), { recursive: true });
  await fs.appendFile(config.apiLogFile, `${line}\n`, 'utf8');
}

function attachApiLogger(req: Request, res: Response): void {
  const startedAt = Date.now();
  let logged = false;
  const logOnce = (closedEarly: boolean) => {
    if (logged) return;
    logged = true;
    void appendApiLog(req, res.statusCode || 499, startedAt, closedEarly).catch((error) => {
      console.error('appendApiLog failed:', error);
    });
  };

  res.on('finish', () => logOnce(false));
  res.on('close', () => {
    if (!res.writableEnded) logOnce(true);
  });
}

async function readApiLog(maxLines = 500): Promise<string> {
  try {
    const content = await fs.readFile(config.apiLogFile, 'utf8');
    const lines = content.trimEnd().split('\n');
    return lines.slice(Math.max(0, lines.length - maxLines)).join('\n');
  } catch (error) {
    const code = (error as NodeJS.ErrnoException).code;
    if (code === 'ENOENT') return '';
    throw error;
  }
}

async function clearApiLog(): Promise<void> {
  try {
    await fs.unlink(config.apiLogFile);
  } catch (error) {
    const code = (error as NodeJS.ErrnoException).code;
    if (code !== 'ENOENT') throw error;
  }
}

function overlayValue<T extends string | number | boolean | null | undefined>(
  value: T,
  callback: (value: NonNullable<T>) => void,
): void {
  if (value !== null && value !== undefined) callback(value as NonNullable<T>);
}

function overlayDateTimeValue(value: string | null | undefined, callback: (value: string) => void): void {
  callback(value === null || value === undefined ? '' : normalizeDateTimeValue(value));
}

async function ensureDatabase(): Promise<void> {
  const adminConnection = await mysql.createConnection({
    host: config.dbHost,
    port: config.dbPort,
    user: config.dbUser,
    password: config.dbPassword,
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
    dateStrings: true,
    waitForConnections: true,
    connectionLimit: 10,
  });

  await pool.query(`
    CREATE TABLE IF NOT EXISTS mock_events (
      id VARCHAR(32) NOT NULL PRIMARY KEY,
      date DATE NULL,
      name VARCHAR(255) NULL,
      place VARCHAR(255) NULL,
      stages INT NULL,
      sport_id ENUM('OB', 'LOB', 'MTBO', 'TRAIL') NULL,
      level_id VARCHAR(255) NULL,
      ranking VARCHAR(32) NULL,
      entry_date1 DATETIME NULL,
      entry_date2 DATETIME NULL,
      entry_date3 DATETIME NULL,
      entry_koef2 DECIMAL(8,2) NULL,
      entry_koef3 DECIMAL(8,2) NULL,
      entry_start DATETIME NULL,
      org_abbr VARCHAR(32) NULL,
      region_id VARCHAR(255) NULL,
      cancelled TINYINT(1) NULL,
      proxy_only TINYINT(1) NOT NULL DEFAULT 1,
      deleted TINYINT(1) NOT NULL DEFAULT 0,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_date (date),
      INDEX idx_deleted (deleted)
    )
  `);

  await pool.query(`
    CREATE TABLE IF NOT EXISTS mock_event_classes (
      event_id VARCHAR(32) NOT NULL,
      class_id VARCHAR(32) NOT NULL,
      name VARCHAR(64) NULL,
      fee DECIMAL(10,2) NULL,
      proxy_only TINYINT(1) NOT NULL DEFAULT 1,
      deleted TINYINT(1) NOT NULL DEFAULT 0,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (event_id, class_id),
      INDEX idx_event (event_id, deleted)
    )
  `);

  await pool.query(`
    CREATE TABLE IF NOT EXISTS mock_users (
      user_id VARCHAR(32) NOT NULL PRIMARY KEY,
      club_user_id VARCHAR(32) NULL,
      reg_no VARCHAR(16) NULL,
      sport INT NOT NULL DEFAULT 1,
      year INT NOT NULL DEFAULT 0,
      first_name VARCHAR(128) NULL,
      last_name VARCHAR(128) NULL,
      si VARCHAR(32) NULL,
      club_id VARCHAR(32) NULL,
      licence VARCHAR(32) NULL,
      proxy_only TINYINT(1) NOT NULL DEFAULT 1,
      deleted TINYINT(1) NOT NULL DEFAULT 0,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_reg_no (reg_no),
      INDEX idx_club_user (club_user_id),
      INDEX idx_registration (sport, year, deleted)
    )
  `);

  await pool.query(`
    CREATE TABLE IF NOT EXISTS mock_entries (
      entry_id VARCHAR(32) NOT NULL PRIMARY KEY,
      event_id VARCHAR(32) NOT NULL,
      club_user_id VARCHAR(32) NULL,
      reg_no VARCHAR(16) NULL,
      class_id VARCHAR(32) NULL,
      class_desc VARCHAR(64) NULL,
      name VARCHAR(255) NULL,
      rent_si TINYINT(1) NULL,
      licence VARCHAR(32) NULL,
      fee DECIMAL(10,2) NULL,
      entry_stop INT NULL,
      si VARCHAR(32) NULL,
      note VARCHAR(512) NULL,
      proxy_only TINYINT(1) NOT NULL DEFAULT 1,
      deleted TINYINT(1) NOT NULL DEFAULT 0,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_event (event_id, deleted),
      INDEX idx_club_user (club_user_id)
    )
  `);

  await pool.query(`
    CREATE TABLE IF NOT EXISTS mock_services (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      service_id VARCHAR(32) NULL,
      event_id VARCHAR(32) NOT NULL,
      club_user_id VARCHAR(32) NULL,
      name VARCHAR(255) NULL,
      amount DECIMAL(10,2) NULL,
      note VARCHAR(512) NULL,
      proxy_only TINYINT(1) NOT NULL DEFAULT 1,
      deleted TINYINT(1) NOT NULL DEFAULT 0,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_event (event_id, deleted)
    )
  `);

  await pool.query(`
    CREATE TABLE IF NOT EXISTS mock_settings (
      settings_key VARCHAR(64) NOT NULL PRIMARY KEY,
      mode VARCHAR(32) NOT NULL DEFAULT 'normal',
      response_delay_ms INT NOT NULL DEFAULT 0,
      force_status_code INT NOT NULL DEFAULT 503,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
  `);

  await pool.query(`
    ALTER TABLE mock_settings
    MODIFY mode VARCHAR(32) NOT NULL DEFAULT 'normal'
  `);

  await pool.query(`
    INSERT INTO mock_settings (settings_key)
    VALUES ('default')
    ON DUPLICATE KEY UPDATE settings_key = VALUES(settings_key)
  `);
}

async function getSettings(): Promise<MockSettings> {
  const [rows] = await pool.query<SettingsRow[]>(
    'SELECT mode, response_delay_ms, force_status_code FROM mock_settings WHERE settings_key = ? LIMIT 1',
    ['default'],
  );

  const row = rows[0];
  return {
    mode: asRuntimeMode(row?.mode, 'normal'),
    responseDelayMs: Number(row?.response_delay_ms ?? 0),
    forceStatusCode: Number(row?.force_status_code ?? 503),
  };
}

async function updateSettings(patch: Partial<MockSettings>): Promise<MockSettings> {
  const current = await getSettings();
  const next: MockSettings = {
    mode: asRuntimeMode(patch.mode, current.mode),
    responseDelayMs: Math.max(0, Math.min(600000, patch.responseDelayMs ?? current.responseDelayMs)),
    forceStatusCode: Math.max(400, Math.min(599, patch.forceStatusCode ?? current.forceStatusCode)),
  };

  await pool.query(
    `
      UPDATE mock_settings
      SET mode = ?, response_delay_ms = ?, force_status_code = ?
      WHERE settings_key = ?
    `,
    [next.mode, next.responseDelayMs, next.forceStatusCode, 'default'],
  );

  return next;
}

async function maybeApplyFaultMode(req: Request, res: Response): Promise<boolean> {
  const settings = await getSettings();

  if (settings.mode === 'delay' && settings.responseDelayMs > 0) {
    await new Promise((resolve) => setTimeout(resolve, settings.responseDelayMs));
  }

  if (settings.mode === 'service_down' || settings.mode === 'force_client_error') {
    res.status(settings.forceStatusCode).json({
      Status: 'ERROR',
      Data: settings.mode === 'service_down'
        ? 'Mock ORIS service down response.'
        : 'Mock ORIS forced error response.',
      mode: settings.mode,
      statusCode: settings.forceStatusCode,
    });
    return true;
  }

  if (settings.mode === 'hang') {
    req.socket.setTimeout(0);
    return new Promise<boolean>(() => {
      // Intentionally keep the request open to simulate an upstream ACK with no response body.
    });
  }

  if (settings.mode === 'close_connection') {
    res.status(settings.forceStatusCode);
    req.socket.destroy();
    return true;
  }

  return false;
}

async function fetchUpstream(params: URLSearchParams): Promise<JsonObject | null> {
  const url = `${config.upstreamBaseUrl}API/?${params.toString()}`;
  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 5000);
    const response = await fetch(url, { signal: controller.signal });
    clearTimeout(timeout);
    if (!response.ok) return null;
    return await response.json() as JsonObject;
  } catch (err) {
    console.error('fetchUpstream failed:', err);
    return null;
  }
}

async function fetchUpstreamEvent(id: string): Promise<JsonObject | null> {
  const upstream = await fetchUpstream(new URLSearchParams({ format: 'json', method: 'getEvent', id }));
  return upstream?.Status === 'OK' && isPlainObject(upstream.Data) ? upstream.Data as JsonObject : null;
}

function normalizeEventPayload(payload: JsonObject): JsonObject {
  const normalized = { ...payload };
  const id = asString(normalized.ID ?? normalized.id);
  normalized.ID = id;
  normalized.Stages = Math.max(1, asNumber(normalized.Stages ?? normalized.stages, 1));
  if (normalized.Stage1 === undefined || normalized.Stage1 === null || normalized.Stage1 === '') {
    normalized.Stage1 = id;
  }
  return normalized;
}

function classKey(cls: JsonObject): string {
	return asString(cls.ID ?? cls.id ?? cls.Name ?? cls.name);
}

function classList(value: unknown): JsonObject[] {
  if (Array.isArray(value)) return value.filter(isPlainObject) as JsonObject[];
  if (isPlainObject(value)) {
    return Object.entries(value).flatMap(([key, item]) => {
      if (!isPlainObject(item)) return [];
      const cls = { ...item };
      if (cls.ID === undefined && cls.id === undefined) cls.ID = key;
      return [cls];
    });
  }
  return [];
}

function defaultRegNo(userId: string): string {
  const suffix = userId.replace(/\D/g, '').slice(-4).padStart(4, '0');
  return `${config.defaultClubAbbr}${suffix}`;
}

async function getEventClasses(eventId: string): Promise<EventClassRow[]> {
  const [rows] = await pool.query<EventClassRow[]>(
    'SELECT * FROM mock_event_classes WHERE event_id = ?',
    [eventId],
  );
  return rows;
}

function composeClasses(baseClasses: unknown, rows: EventClassRow[]): JsonObject[] {
	const classes = new Map<string, JsonObject>();
	const base = classList(baseClasses);
	for (const item of base) {
		const key = asString(item.ID || item.Name);
		if (key) classes.set(key, item);
	}

	for (const row of rows) {
		const key = row.class_id;
		if (row.deleted) {
      classes.delete(key);
      continue;
    }
    const current = classes.get(key) ?? {};
    const overlay: JsonObject = { ID: row.class_id };
    overlayValue(row.name, (value) => { overlay.Name = value; });
    overlayValue(row.fee, (value) => { overlay.Fee = Number(value); });
    classes.set(key, mergeJson(current, overlay));
  }

  return Array.from(classes.values());
}

function composeSport(name: string): JsonObject {
  const sport = ORIS_SPORTS.find((item) => item.name === name);
  return {
    ID: sport?.id ?? '',
    ...(sport ? { NameCZ: sport.name, NameEN: sport.description } : {}),
  };
}

function composeLevel(value: string): JsonObject {
  const names = idList(value);
  const levels = names.map((name) => ORIS_LEVELS.find((item) => item.name === name)).filter(Boolean);
  return {
    ID: levels.map((item) => item?.id).join(','),
    ...(levels.length > 0 ? {
      ShortName: levels.map((item) => item?.name).join(', '),
      NameCZ: levels.map((item) => item?.description).join(', '),
    } : {}),
  };
}

function composeRegions(value: unknown): JsonObject {
  const regions: JsonObject = {};
  for (const id of idList(value)) {
    const region = ORIS_REGIONS.find((item) => item.id === id);
    regions[`Region_${id}`] = {
      ID: id,
      ...(region ? { Name: region.name } : {}),
    };
  }
  return regions;
}

function composeEvent(row: EventRow, classRows: EventClassRow[], upstreamEvent: JsonObject | null): JsonObject {
  const localOnly = !!row.proxy_only;
  const base: JsonObject = localOnly
    ? {
      ID: row.id,
      Date: todayPlus(30),
      Name: `Proxy ORIS ${row.id}`,
      Place: 'Proxy place',
      Stages: 1,
      Stage1: row.id,
      Sport: composeSport('OB'),
      Level: composeLevel('OŽ'),
      Ranking: '1',
      EntryDate1: orisDateTimePlus(20),
      EntryDate2: orisDateTimePlus(24),
      EntryDate3: orisDateTimePlus(27),
      EntryKoef2: 1,
      EntryKoef3: 1,
      EntryStart: '',
      Org1: { Abbr: config.defaultClubAbbr },
      Regions: composeRegions('JM'),
      Classes: [],
      Cancelled: 0,
    }
    : { ...upstreamEvent };

  const overlay: JsonObject = { ID: row.id };
  overlayValue(row.date, (value) => { overlay.Date = value; });
  overlayValue(row.name, (value) => { overlay.Name = value; });
  overlayValue(row.place, (value) => { overlay.Place = value; });
  overlayValue(row.stages, (value) => { overlay.Stages = Number(value); });
  overlayValue(row.sport_id, (value) => { overlay.Sport = composeSport(value); });
  overlayValue(row.level_id, (value) => { overlay.Level = composeLevel(value); });
  overlayValue(row.ranking, (value) => { overlay.Ranking = value; });
  overlayDateTimeValue(row.entry_date1, (value) => { overlay.EntryDate1 = value; });
  overlayDateTimeValue(row.entry_date2, (value) => { overlay.EntryDate2 = value; });
  overlayDateTimeValue(row.entry_date3, (value) => { overlay.EntryDate3 = value; });
  overlayValue(row.entry_koef2, (value) => { overlay.EntryKoef2 = Number(value); });
  overlayValue(row.entry_koef3, (value) => { overlay.EntryKoef3 = Number(value); });
  overlayDateTimeValue(row.entry_start, (value) => { overlay.EntryStart = value; });
  overlayValue(row.org_abbr, (value) => { overlay.Org1 = { Abbr: value }; });
  overlayValue(row.region_id, (value) => { overlay.Regions = composeRegions(value); });
  overlayValue(row.cancelled, (value) => { overlay.Cancelled = Number(value); });

  const merged = normalizeEventPayload(mergeJson(base, overlay));
  merged.Classes = composeClasses(merged.Classes, classRows);
  return merged;
}

function composeUser(row: UserRow, upstreamUser: JsonObject | null): JsonObject {
  const localOnly = !!row.proxy_only;
  const regNo = row.reg_no ?? defaultRegNo(row.user_id);
  const userId = row.user_id;
  const base: JsonObject = localOnly
    ? {
      UserID: userId,
      ID: userId,
      ClubUserID: row.club_user_id ?? userId,
      FirstName: 'Proxy',
      LastName: `User ${regNo}`,
      RegNo: regNo,
      SI: '',
      ClubID: config.defaultClubId,
      Licence: '',
    }
    : { ...upstreamUser };

  const overlay: JsonObject = { UserID: userId, ID: userId };
  overlayValue(row.club_user_id, (value) => { overlay.ClubUserID = value; });
  overlayValue(row.first_name, (value) => { overlay.FirstName = value; });
  overlayValue(row.last_name, (value) => { overlay.LastName = value; });
  overlayValue(row.reg_no, (value) => { overlay.RegNo = value; });
  overlayValue(row.si, (value) => { overlay.SI = value; });
  overlayValue(row.club_id, (value) => { overlay.ClubID = value; });
  overlayValue(row.licence, (value) => { overlay.Licence = value; });
  return mergeJson(base, overlay);
}

function composeClubUserPayload(user: JsonObject): JsonObject {
  return {
    ...user,
    ID: user.ClubUserID ?? user.UserID ?? user.ID,
    UserID: user.UserID ?? user.ID,
    ClubID: user.ClubID ?? config.defaultClubId,
  };
}

function composeEntry(row: EntryRow, upstreamEntry: JsonObject | null, event: JsonObject | null, user: JsonObject | null): JsonObject {
  const localOnly = !!row.proxy_only;
  const eventClasses = classList(event?.Classes);
  const foundClass = eventClasses.find((item) => asString(item.ID) === row.class_id || asString(item.Name) === row.class_desc);
  const classId = row.class_id ?? asString(foundClass?.ID, '');
  const classDesc = row.class_desc ?? asString(foundClass?.Name, classId || 'H21');
  const clubUserId = row.club_user_id ?? asString(user?.ClubUserID ?? user?.UserID, '');

  const base: JsonObject = localOnly
    ? {
      ID: row.entry_id,
      EventID: row.event_id,
      ClubUserID: clubUserId,
      RegNo: asString(user?.RegNo, ''),
      ClassDesc: classDesc,
      Class: { ID: classId || classDesc, Name: classDesc },
      Name: [user?.LastName, user?.FirstName].filter(Boolean).join(' ') || 'Proxy User',
      RentSI: false,
      Licence: asString(user?.Licence, ''),
      Fee: asNumber(foundClass?.Fee, 0),
      EntryStop: 1,
      SI: asString(user?.SI, ''),
      Note: '',
    }
    : { ...upstreamEntry };

  const overlay: JsonObject = { ID: row.entry_id, EventID: row.event_id };
  overlayValue(row.club_user_id, (value) => { overlay.ClubUserID = value; });
  overlayValue(row.reg_no, (value) => { overlay.RegNo = value; });
  if (row.class_id !== null || row.class_desc !== null) {
    overlay.ClassDesc = classDesc;
    overlay.Class = { ID: classId || classDesc, Name: classDesc };
  }
  overlayValue(row.name, (value) => { overlay.Name = value; });
  overlayValue(row.rent_si, (value) => { overlay.RentSI = !!value; });
  overlayValue(row.licence, (value) => { overlay.Licence = value; });
  overlayValue(row.fee, (value) => { overlay.Fee = Number(value); });
  overlayValue(row.entry_stop, (value) => { overlay.EntryStop = Number(value); });
  overlayValue(row.si, (value) => { overlay.SI = value; });
  overlayValue(row.note, (value) => { overlay.Note = value; });
  return mergeJson(base, overlay);
}

function composeService(row: ServiceRow, upstreamService: JsonObject | null): JsonObject {
  const base = row.proxy_only
    ? { ID: row.service_id ?? String(row.id), EventID: row.event_id }
    : { ...upstreamService };
  const overlay: JsonObject = { ID: row.service_id ?? String(row.id), EventID: row.event_id };
  overlayValue(row.club_user_id, (value) => { overlay.ClubUserID = value; });
  overlayValue(row.name, (value) => { overlay.Name = value; });
  overlayValue(row.amount, (value) => { overlay.Amount = Number(value); });
  overlayValue(row.note, (value) => { overlay.Note = value; });
  return mergeJson(base, overlay);
}

async function getMockEventInfo(id: string): Promise<EventRow | null> {
  const [rows] = await pool.query<EventRow[]>('SELECT * FROM mock_events WHERE id = ? LIMIT 1', [id]);
  return rows[0] ?? null;
}

async function getEvent(id: string, mockEventInfo?: EventRow | null): Promise<JsonObject | null> {
  const row = mockEventInfo === undefined ? await getMockEventInfo(id) : mockEventInfo;
  if (row?.deleted) return null;
  if (row?.proxy_only) {
    return composeEvent(row, await getEventClasses(id), null);
  }

  const upstreamEvent = await fetchUpstreamEvent(id);
  const upstreamClasses = classList(upstreamEvent?.Classes);
  if (upstreamClasses.length > 0) {
    await writeEventClasses(id, upstreamClasses, false);
  }
  if (row) {
    if (!upstreamEvent) return null;
    return composeEvent(row, await getEventClasses(id), upstreamEvent);
  }
  return upstreamEvent ? normalizeEventPayload(upstreamEvent) : null;
}

async function getUserByClubUserId(clubUserId: string): Promise<JsonObject | null> {
  const [rows] = await pool.query<UserRow[]>('SELECT * FROM mock_users WHERE club_user_id = ? AND deleted = 0 LIMIT 1', [clubUserId]);
  return rows[0] ? composeUser(rows[0], null) : null;
}

async function findEventByClass(classId: string): Promise<JsonObject | null> {
  const [classRows] = await pool.query<EventClassRow[]>(
    'SELECT * FROM mock_event_classes WHERE class_id = ? AND deleted = 0 LIMIT 1',
    [classId],
  );
  return classRows[0] ? getEvent(classRows[0].event_id) : null;
}

type EntryMutationValidation =
  | { type: 'ok' }
  | { type: 'closedRegistration' }
  | { type: 'error'; message: string };

function validateEntryMutation(event: JsonObject | null, classId: string): EntryMutationValidation {
  if (!event) {
    return { type: 'error', message: `Class ${classId || '(missing)'} is not defined for an event.` };
  }

  const classes = classList(event.Classes);
  if (!classes.some((item) => asString(item.ID) === classId)) {
    return { type: 'error', message: `Class ${classId || '(missing)'} is not defined for event ${asString(event.ID)}.` };
  }

  const deadline = normalizeDateTimeValue(event.EntryDate1).replace(' ', 'T');
  if (deadline) {
    const timestamp = Date.parse(`${deadline}Z`);
    if (Number.isFinite(timestamp) && timestamp < Date.now()) {
      return { type: 'closedRegistration' };
    }
  }

  return { type: 'ok' };
}

function normalizeInputClasses(eventId: string, value: unknown): JsonObject[] {
  const classes: JsonObject[] = typeof value === 'string'
    ? value.split(';').map((name) => ({ Name: name.trim() })).filter((item) => item.Name)
    : classList(value);
  const usedIds = new Set(classes.map((cls) => asString(cls.ID ?? cls.id)).filter(Boolean));
  let nextId = 1;

  return classes.map((cls) => {
    const normalized: JsonObject = { ...cls };
    if (!asString(normalized.ID ?? normalized.id)) {
      let classId = '';
      do {
        classId = `${eventId}${String(nextId).padStart(2, '0')}`;
        nextId += 1;
      } while (usedIds.has(classId));
      normalized.ID = classId;
      usedIds.add(classId);
    }
    if (normalized.Fee === undefined && normalized.fee === undefined) normalized.Fee = 150;
    return normalized;
  });
}

function inputEventClasses(eventId: string, input: JsonObject): JsonObject[] | null {
  if (Object.prototype.hasOwnProperty.call(input, 'classes')) return normalizeInputClasses(eventId, input.classes);
  if (Object.prototype.hasOwnProperty.call(input, 'Classes')) return normalizeInputClasses(eventId, input.Classes);
  return null;
}

async function writeEventClasses(eventId: string, classes: JsonObject[], proxyOnly: boolean): Promise<void> {
  await pool.query('DELETE FROM mock_event_classes WHERE event_id = ?', [eventId]);
  for (const cls of classes) {
    const classId = classKey(cls);
    if (!classId) continue;
    await pool.query(
      `
        INSERT INTO mock_event_classes (event_id, class_id, name, fee, proxy_only, deleted)
        VALUES (?, ?, ?, ?, ?, 0)
      `,
      [eventId, classId, nullableString(cls, 'Name', 'name'), nullableNumber(cls, 'Fee', 'fee'), proxyOnly ? 1 : 0],
    );
  }
}

async function eventClassesForSave(eventId: string, input: JsonObject, proxyOnly: boolean): Promise<JsonObject[] | null> {
  const inputClasses = inputEventClasses(eventId, input);
  if (proxyOnly) return inputClasses;

  const upstreamEvent = await fetchUpstreamEvent(eventId);
  const upstreamClasses = classList(upstreamEvent?.Classes);
  if (!inputClasses) return upstreamClasses.length > 0 ? upstreamClasses : null;

  const classesById = new Map<string, JsonObject>();
  for (const cls of upstreamClasses) {
    const classId = classKey(cls);
    if (classId) classesById.set(classId, cls);
  }
  for (const cls of inputClasses) {
    const classId = classKey(cls);
    if (!classId) continue;
    classesById.set(classId, mergeJson(classesById.get(classId) ?? {}, cls));
  }
  return Array.from(classesById.values());
}

async function replaceEventClasses(eventId: string, classes: JsonObject[], proxyOnly: boolean): Promise<void> {
  const baseClasses = proxyOnly ? [] : classList((await fetchUpstreamEvent(eventId))?.Classes);
  await writeEventClasses(eventId, classes, proxyOnly);

  const selectedIds = new Set(classes.map(classKey).filter(Boolean));
  for (const cls of baseClasses) {
    const classId = classKey(cls);
    if (!classId || selectedIds.has(classId)) continue;
    await pool.query(
      `
        INSERT INTO mock_event_classes (event_id, class_id, name, fee, proxy_only, deleted)
        VALUES (?, ?, ?, ?, ?, 1)
      `,
      [eventId, classId, nullableString(cls, 'Name', 'name'), nullableNumber(cls, 'Fee', 'fee'), proxyOnly ? 1 : 0],
    );
  }
}

async function saveEventClasses(eventId: string, input: JsonObject, proxyOnly: boolean): Promise<void> {
  const inputClasses = inputEventClasses(eventId, input);
  if (inputClasses !== null && asBool(input.replaceClasses ?? input.replace_classes)) {
    await replaceEventClasses(eventId, inputClasses, proxyOnly);
    return;
  }
  const classes = await eventClassesForSave(eventId, input, proxyOnly);
  if (!classes) return;
  await writeEventClasses(eventId, classes, proxyOnly);
}

async function generateEventId(): Promise<string> {
  const [rows] = await pool.query<RowDataPacket[]>(
    `
      SELECT DISTINCT CAST(id AS UNSIGNED) AS event_id
      FROM mock_events
      WHERE id REGEXP '^[0-9]+$'
        AND CAST(id AS UNSIGNED) BETWEEN 25000 AND 999999
      ORDER BY event_id
    `,
  );
  let candidate = 25000;
  for (const row of rows) {
    const eventId = Number(row.event_id);
    if (eventId < candidate) continue;
    if (eventId > candidate) break;
    candidate += 1;
  }
  if (candidate > 999999) {
    throw new Error('Could not generate unique event ID');
  }
  return String(candidate);
}

async function generateEntryId(): Promise<string> {
  const [rows] = await pool.query<RowDataPacket[]>(
    `
      SELECT MAX(CAST(entry_id AS UNSIGNED)) AS max_entry_id
      FROM mock_entries
      WHERE entry_id REGEXP '^[0-9]+$'
    `,
  );
  const currentMax = Number(rows[0]?.max_entry_id ?? 0);
  return String(Math.max(currentMax + 1, 250000));
}

async function generateUserId(): Promise<string> {
  const [rows] = await pool.query<RowDataPacket[]>(
    `
      SELECT MAX(CAST(user_id AS UNSIGNED)) AS max_user_id
      FROM mock_users
      WHERE user_id REGEXP '^[0-9]+$'
    `,
  );
  const currentMax = Number(rows[0]?.max_user_id ?? 0);
  return String(Math.max(currentMax + 1, 800000));
}

async function generateClubUserId(): Promise<string> {
  for (let attempt = 0; attempt < 20; attempt += 1) {
    const id = String(crypto.randomInt(900000, 999999));
    const [rows] = await pool.query<UserRow[]>('SELECT club_user_id FROM mock_users WHERE club_user_id = ? LIMIT 1', [id]);
    if (!rows[0]) return id;
  }
  throw new Error('Could not generate unique club user ID');
}

async function upsertEvent(input: JsonObject): Promise<JsonObject> {
  const requestedId = asString(input.id ?? input.ID).trim();
  const id = requestedId || await generateEventId();
  const generatedRace = !requestedId;
  const proxyOnly = asBool(input.proxyOnly ?? input.proxy_only ?? true);
  await pool.query(
    `
      INSERT INTO mock_events (
        id, date, name, place, stages, sport_id, level_id, ranking, entry_date1, entry_date2,
        entry_date3, entry_koef2, entry_koef3, entry_start, org_abbr, region_id, cancelled,
        proxy_only, deleted
      )
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
      ON DUPLICATE KEY UPDATE
        date = COALESCE(VALUES(date), date),
        name = COALESCE(VALUES(name), name),
        place = COALESCE(VALUES(place), place),
        stages = COALESCE(VALUES(stages), stages),
        sport_id = COALESCE(VALUES(sport_id), sport_id),
        level_id = COALESCE(VALUES(level_id), level_id),
        ranking = COALESCE(VALUES(ranking), ranking),
        entry_date1 = COALESCE(VALUES(entry_date1), entry_date1),
        entry_date2 = COALESCE(VALUES(entry_date2), entry_date2),
        entry_date3 = COALESCE(VALUES(entry_date3), entry_date3),
        entry_koef2 = COALESCE(VALUES(entry_koef2), entry_koef2),
        entry_koef3 = COALESCE(VALUES(entry_koef3), entry_koef3),
        entry_start = COALESCE(VALUES(entry_start), entry_start),
        org_abbr = COALESCE(VALUES(org_abbr), org_abbr),
        region_id = COALESCE(VALUES(region_id), region_id),
        cancelled = COALESCE(VALUES(cancelled), cancelled),
        proxy_only = VALUES(proxy_only),
        deleted = 0
    `,
    [
      id,
      nullableString(input, 'date', 'Date') ?? (generatedRace ? todayPlus(30) : null),
      nullableString(input, 'name', 'Name'),
      nullableString(input, 'place', 'Place'),
      nullableNumber(input, 'stages', 'Stages') ?? (proxyOnly ? 1 : null),
      nullableSportName(input, 'sport', 'sportId', 'Sport') ?? (generatedRace ? 'OB' : null),
      nullableLevelNames(input, 'levels', 'levelIds', 'levelId', 'Level') ?? (generatedRace ? 'OŽ' : null),
      nullableString(input, 'ranking', 'Ranking') ?? (generatedRace ? '1' : null),
      nullableDateTime(input, 'entryDate1', 'EntryDate1'),
      nullableDateTime(input, 'entryDate2', 'EntryDate2'),
      nullableDateTime(input, 'entryDate3', 'EntryDate3'),
      nullableNumber(input, 'entryKoef2', 'EntryKoef2'),
      nullableNumber(input, 'entryKoef3', 'EntryKoef3'),
      nullableDateTime(input, 'entryStart', 'EntryStart'),
      nullableString(input, 'org', 'Org1Abbr') ?? (generatedRace ? config.defaultClubAbbr : null),
      nullableIdList(input, 'regionIds', 'regionId', 'Regions') ?? (generatedRace ? 'JM' : null),
      nullableBool(input, 'cancelled', 'Cancelled'),
      proxyOnly ? 1 : 0,
    ],
  );
  await saveEventClasses(id, input, proxyOnly);
  return await getEvent(id) ?? composeEvent({ id, proxy_only: proxyOnly ? 1 : 0, deleted: 0 } as EventRow, [], null);
}

async function upsertUser(input: JsonObject): Promise<JsonObject> {
  const userId = asString(input.userId ?? input.userid ?? input.user ?? input.UserID ?? input.ID).trim() || await generateUserId();
  const proxyOnly = asBool(input.proxyOnly ?? input.proxy_only ?? true);
  const sport = asNumber(input.sport, 1);
  const year = asNumber(input.year, new Date().getUTCFullYear());
  await pool.query(
    `
      INSERT INTO mock_users (
        user_id, club_user_id, reg_no, sport, year, first_name, last_name, si, club_id, licence,
        proxy_only, deleted
      )
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
      ON DUPLICATE KEY UPDATE
        club_user_id = COALESCE(VALUES(club_user_id), club_user_id),
        reg_no = COALESCE(VALUES(reg_no), reg_no),
        sport = VALUES(sport),
        year = VALUES(year),
        first_name = COALESCE(VALUES(first_name), first_name),
        last_name = COALESCE(VALUES(last_name), last_name),
        si = COALESCE(VALUES(si), si),
        club_id = COALESCE(VALUES(club_id), club_id),
        licence = COALESCE(VALUES(licence), licence),
        proxy_only = VALUES(proxy_only),
        deleted = 0
    `,
    [
      userId,
      nullableString(input, 'clubUserId', 'clubuserid', 'clubuser', 'ClubUserID') ?? (proxyOnly ? userId : null),
      nullableString(input, 'regNo', 'regno', 'RegNo'),
      sport,
      year,
      nullableString(input, 'firstName', 'firstname', 'FirstName'),
      nullableString(input, 'lastName', 'lastname', 'LastName'),
      nullableString(input, 'si', 'SI'),
      nullableString(input, 'clubId', 'clubid', 'ClubID'),
      nullableString(input, 'licence', 'Licence'),
      proxyOnly ? 1 : 0,
    ],
  );
  const [rows] = await pool.query<UserRow[]>('SELECT * FROM mock_users WHERE user_id = ? LIMIT 1', [userId]);
  return composeUser(rows[0], null);
}

async function upsertClubUser(input: JsonObject): Promise<JsonObject> {
  const requestedUserId = asString(input.userId ?? input.userid ?? input.user ?? input.UserID).trim();
  const clubUserId = asString(input.clubUserId ?? input.clubuserid ?? input.clubuser ?? input.ID ?? input.id).trim() || await generateClubUserId();
  const [byClubUser] = await pool.query<UserRow[]>('SELECT * FROM mock_users WHERE club_user_id = ? AND deleted = 0 LIMIT 1', [clubUserId]);
  const userId = requestedUserId || byClubUser[0]?.user_id || await generateUserId();
  const user = await upsertUser({
    ...input,
    userId,
    clubUserId,
    clubId: input.clubId ?? input.clubid ?? input.ClubID ?? config.defaultClubId,
  });
  return composeClubUserPayload(user);
}

async function upsertEntry(input: JsonObject): Promise<JsonObject> {
  let event = input.eventId || input.eventid ? await getEvent(asString(input.eventId ?? input.eventid)) : null;
  if (!event && (input.classId || input.class)) {
    event = await findEventByClass(asString(input.classId ?? input.class));
  }
  const user = input.clubUserId || input.clubuser ? await getUserByClubUserId(asString(input.clubUserId ?? input.clubuser)) : null;
  const proxyOnly = asBool(input.proxyOnly ?? input.proxy_only ?? true);
  const requestedEntryId = asString(input.entryId ?? input.entryid ?? input.ID).trim();
  const entryId = requestedEntryId || await generateEntryId();
  const eventId = asString(input.eventId ?? input.eventid ?? event?.ID);
  if (!eventId) throw new Error('eventId or a class belonging to a stored event is required');

  const classId = nullableString(input, 'classId', 'class', 'ClassID') ?? (isPlainObject(input.Class) ? nullableString(input.Class, 'ID') : null);
  const classDesc = nullableString(input, 'classDesc', 'ClassDesc') ?? (isPlainObject(input.Class) ? nullableString(input.Class, 'Name') : null);
  await pool.query(
    `
      INSERT INTO mock_entries (
        entry_id, event_id, club_user_id, reg_no, class_id, class_desc, name, rent_si, licence,
        fee, entry_stop, si, note, proxy_only, deleted
      )
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
      ON DUPLICATE KEY UPDATE
        event_id = VALUES(event_id),
        club_user_id = COALESCE(VALUES(club_user_id), club_user_id),
        reg_no = COALESCE(VALUES(reg_no), reg_no),
        class_id = COALESCE(VALUES(class_id), class_id),
        class_desc = COALESCE(VALUES(class_desc), class_desc),
        name = COALESCE(VALUES(name), name),
        rent_si = COALESCE(VALUES(rent_si), rent_si),
        licence = COALESCE(VALUES(licence), licence),
        fee = COALESCE(VALUES(fee), fee),
        entry_stop = COALESCE(VALUES(entry_stop), entry_stop),
        si = COALESCE(VALUES(si), si),
        note = COALESCE(VALUES(note), note),
        proxy_only = VALUES(proxy_only),
        deleted = 0
    `,
    [
      entryId,
      eventId,
      nullableString(input, 'clubUserId', 'clubuser', 'ClubUserID'),
      nullableString(input, 'regNo', 'RegNo'),
      classId,
      classDesc,
      nullableString(input, 'name', 'Name'),
      nullableBool(input, 'rentSI', 'rent_si', 'RentSI'),
      nullableString(input, 'licence', 'Licence'),
      nullableNumber(input, 'fee', 'Fee'),
      nullableNumber(input, 'entryStop', 'EntryStop'),
      nullableString(input, 'si', 'SI'),
      nullableString(input, 'note', 'Note'),
      proxyOnly ? 1 : 0,
    ],
  );

  const [rows] = await pool.query<EntryRow[]>('SELECT * FROM mock_entries WHERE entry_id = ? LIMIT 1', [entryId]);
  return composeEntry(rows[0], null, event, user);
}

function mergeRowsByKey<T extends MockRow>(
  baseItems: JsonObject[],
  rows: T[],
  responseKey: string,
  rowKey: (row: T) => string,
  compose: (row: T, base: JsonObject | null) => JsonObject,
): JsonObject[] {
  const merged = new Map<string, JsonObject>();
  for (const item of baseItems) merged.set(asString(item[responseKey]), item);
  for (const row of rows) {
    const key = rowKey(row);
    if (!key) continue;
    if (row.deleted) {
      merged.delete(key);
      continue;
    }
    if (!row.proxy_only && !merged.has(key)) continue;
    merged.set(key, compose(row, merged.get(key) ?? null));
  }
  return Array.from(merged.values());
}

async function handleGetEvent(req: Request, res: Response): Promise<void> {
  const id = firstValue(req.query.id);
  if (!id) {
    res.json(apiError('Missing id'));
    return;
  }
  const event = await getEvent(id);
  res.json(event ? apiOk(event) : apiError('Event not found'));
}

async function handleGetEventList(req: Request, res: Response): Promise<void> {
  const dateFrom = firstValue(req.query.datefrom) ?? '0000-00-00';
  const dateTo = firstValue(req.query.dateto) ?? '9999-99-99';
  const upstream = await fetchUpstream(new URLSearchParams(req.query as Record<string, string>));
  const baseItems = upstream?.Status === 'OK' && Array.isArray(upstream.Data) ? upstream.Data as JsonObject[] : [];
  const [rows] = await pool.query<EventRow[]>('SELECT * FROM mock_events');
  const withClasses = await Promise.all(rows.map(async (row) => ({
    row,
    classes: await getEventClasses(row.id),
  })));
  const merged = new Map<string, JsonObject>();
  for (const item of baseItems) merged.set(asString(item.ID), item);
  for (const { row, classes } of withClasses) {
    if (row.deleted) {
      merged.delete(row.id);
      continue;
    }
    const hasBase = merged.has(row.id);
    const eventDate = asString(row.date);
    if (!hasBase && !row.proxy_only && eventDate === '') continue;
    if (!hasBase && eventDate !== '' && (eventDate < dateFrom || eventDate > dateTo)) continue;
    const base = merged.get(row.id) ?? null;
    merged.set(row.id, composeEvent(row, classes, base));
  }
  res.json(apiOk(Array.from(merged.values())));
}

async function handleGetRegistration(req: Request, res: Response): Promise<void> {
  const sport = asNumber(firstValue(req.query.sport), 1);
  const year = asNumber(firstValue(req.query.year), new Date().getUTCFullYear());
  const upstream = await fetchUpstream(new URLSearchParams(req.query as Record<string, string>));
  const baseItems = upstream?.Status === 'OK' && Array.isArray(upstream.Data) ? upstream.Data as JsonObject[] : [];
  const [rows] = await pool.query<UserRow[]>('SELECT * FROM mock_users WHERE sport = ? AND year = ?', [sport, year]);
  res.json(apiOk(mergeRowsByKey(baseItems, rows, 'RegNo', (row) => row.reg_no ?? asString(composeUser(row, null).RegNo), composeUser)));
}

async function handleGetUser(req: Request, res: Response): Promise<void> {
  const rgnum = requestValue(req, 'rgnum', 'regno', 'RegNo');
  const userId = requestValue(req, 'userid', 'userId', 'user', 'UserID', 'id', 'ID');
  let row: UserRow | undefined;

  if (rgnum) {
    const candidates = registrationCandidates(rgnum);
    const [rows] = await pool.query<UserRow[]>(
      `SELECT * FROM mock_users WHERE reg_no IN (${candidates.map(() => '?').join(',')}) LIMIT 1`,
      candidates,
    );
    row = rows[0];
  }

  if (!row && userId) {
    const [rows] = await pool.query<UserRow[]>('SELECT * FROM mock_users WHERE user_id = ? LIMIT 1', [userId]);
    row = rows[0];
  }

  if (row?.deleted) {
    res.json(apiError('User not found'));
    return;
  }

  if (row?.proxy_only) {
    res.json(apiOk(composeUser(row, null)));
    return;
  }

  const upstream = await fetchUpstream(requestSearchParams(req));
  const upstreamUser = upstream?.Status === 'OK' && isPlainObject(upstream.Data) ? upstream.Data as JsonObject : null;

  if (row) {
    if (!upstreamUser) {
      res.json(apiError('User not found'));
      return;
    }
    res.json(apiOk(composeUser(row, upstreamUser)));
    return;
  }

  res.json(upstream ?? apiError('User not found'));
}

async function handleGetClubUsers(req: Request, res: Response): Promise<void> {
  const userId = requestValue(req, 'user', 'userid', 'userId', 'UserID');
  const upstream = await fetchUpstream(requestSearchParams(req));
  const baseItems = upstream?.Status === 'OK' && Array.isArray(upstream.Data) ? upstream.Data as JsonObject[] : [];

  if (userId) {
    const [rows] = await pool.query<UserRow[]>('SELECT * FROM mock_users WHERE user_id = ? LIMIT 1', [userId]);
    if (rows[0]?.deleted) {
      res.json(apiOk(baseItems.filter((item) => asString(item.UserID) !== userId)));
      return;
    }
    if (rows[0]) {
      const current = baseItems.find((item) => asString(item.UserID) === userId) ?? null;
      if (!rows[0].proxy_only && !current) {
        res.json(apiOk(baseItems));
        return;
      }
      const user = composeUser(rows[0], current);
      const clubUser = { ...user, ID: user.ClubUserID ?? user.UserID, UserID: user.UserID };
      const without = baseItems.filter((item) => asString(item.ID) !== asString(clubUser.ID));
      res.json(apiOk([...without, current ? mergeJson(current, clubUser) : clubUser]));
      return;
    }
  }
  res.json(upstream ?? apiOk([]));
}

async function handleGetEventEntries(req: Request, res: Response): Promise<void> {
  const eventId = firstValue(req.query.eventid);
  if (!eventId) {
    res.json(apiError('Missing eventid'));
    return;
  }
  const mockEventInfo = await getMockEventInfo(eventId);
  const event = await getEvent(eventId, mockEventInfo);
  const upstream = mockEventInfo?.proxy_only
    ? null
    : await fetchUpstream(new URLSearchParams(req.query as Record<string, string>));
  const baseItems = upstream?.Status === 'OK' && Array.isArray(upstream.Data) ? upstream.Data as JsonObject[] : [];
  const [rows] = await pool.query<EntryRow[]>('SELECT * FROM mock_entries WHERE event_id = ?', [eventId]);
  const items: JsonObject[] = [];
  const merged = new Map<string, JsonObject>();
  for (const item of baseItems) merged.set(asString(item.ID), item);
  for (const row of rows) {
    if (row.deleted) {
      merged.delete(row.entry_id);
      continue;
    }
    const user = row.club_user_id ? await getUserByClubUserId(row.club_user_id) : null;
    const base = merged.get(row.entry_id) ?? null;
    if (!row.proxy_only && !base) continue;
    merged.set(row.entry_id, composeEntry(row, base, event, user));
  }
  items.push(...merged.values());
  res.json(apiOk(items));
}

async function handleGetEventServiceEntries(req: Request, res: Response): Promise<void> {
  const eventId = firstValue(req.query.eventid);
  if (!eventId) {
    res.json(apiError('Missing eventid'));
    return;
  }
  const mockEventInfo = await getMockEventInfo(eventId);
  const upstream = mockEventInfo?.proxy_only
    ? null
    : await fetchUpstream(new URLSearchParams(req.query as Record<string, string>));
  const baseItems = upstream?.Status === 'OK' && Array.isArray(upstream.Data) ? upstream.Data as JsonObject[] : [];
  const [rows] = await pool.query<ServiceRow[]>('SELECT * FROM mock_services WHERE event_id = ?', [eventId]);
  res.json(apiOk(mergeRowsByKey(baseItems, rows, 'ID', (row) => row.service_id ?? String(row.id), composeService)));
}

async function handleCreateEntry(req: Request, res: Response): Promise<void> {
  try {
    const classId = asString(req.body.class);
    const event = await findEventByClass(classId);
    const validation = validateEntryMutation(event, classId);
    if (validation.type !== 'ok') {
      res.json(validation.type === 'closedRegistration'
        ? apiClosedRegistration('createEntry')
        : apiError(validation.message));
      return;
    }

    const entry = await upsertEntry({
      ...req.body,
      clubUserId: req.body.clubuser,
      classId,
      rentSI: req.body.rent_si,
    });
    res.json(apiOk({ ID: entry.ID, Entry: entry }));
  } catch (error) {
    res.json(apiError(error instanceof Error ? error.message : 'createEntry failed'));
  }
}

async function handleCreatePerson(req: Request, res: Response): Promise<void> {
  try {
    const user = await upsertUser(req.body);
    res.json(apiOk(user));
  } catch (error) {
    res.json(apiError(error instanceof Error ? error.message : 'createPerson failed'));
  }
}

async function handleUpdateEntry(req: Request, res: Response): Promise<void> {
  const entryId = asString(req.body.entryid);
  const [rows] = await pool.query<EntryRow[]>('SELECT * FROM mock_entries WHERE entry_id = ? AND deleted = 0 LIMIT 1', [entryId]);
  if (!rows[0]) {
    res.json(apiError('Entry not found'));
    return;
  }
  const classId = asString(req.body.class ?? rows[0].class_id);
  const validation = validateEntryMutation(await getEvent(rows[0].event_id), classId);
  if (validation.type !== 'ok') {
    res.json(validation.type === 'closedRegistration'
      ? apiClosedRegistration('updateEntry')
      : apiError(validation.message));
    return;
  }
  const entry = await upsertEntry({
    ...req.body,
    entryId,
    eventId: rows[0].event_id,
    proxyOnly: !!rows[0].proxy_only,
    clubUserId: req.body.clubuser ?? rows[0].club_user_id,
    classId,
    rentSI: req.body.rent_si ?? rows[0].rent_si,
  });
  res.json(apiOk({ ID: entry.ID, Entry: entry }));
}

async function handleDeleteEntry(req: Request, res: Response): Promise<void> {
  const entryId = asString(req.body.entryid);
  const eventId = asString(req.body.eventid ?? req.body.eventId);
  const [existingRows] = await pool.query<EntryRow[]>(
    'SELECT * FROM mock_entries WHERE entry_id = ? AND deleted = 0 LIMIT 1',
    [entryId],
  );
  if (!existingRows[0]) {
    res.json(apiError('Entry not found'));
    return;
  }

  if (eventId) {
    await pool.query(
      `
        INSERT INTO mock_entries (entry_id, event_id, proxy_only, deleted)
        VALUES (?, ?, 0, 1)
        ON DUPLICATE KEY UPDATE deleted = 1
      `,
      [entryId, eventId],
    );
  } else {
    await pool.query('UPDATE mock_entries SET deleted = 1 WHERE entry_id = ?', [entryId]);
  }
  res.json(apiOk({ ID: entryId }));
}

async function handleEditPerson(req: Request, res: Response): Promise<void> {
  const userId = asString(req.body.userid ?? req.body.userId ?? req.body.user ?? req.body.UserID ?? req.body.ID);
  if (!userId) {
    res.json(apiError('Missing userid'));
    return;
  }
  const [rows] = await pool.query<UserRow[]>('SELECT * FROM mock_users WHERE user_id = ? AND deleted = 0 LIMIT 1', [userId]);
  const updated = await upsertUser({
    userId,
    firstName: req.body.firstname ?? req.body.firstName ?? rows[0]?.first_name,
    lastName: req.body.lastname ?? req.body.lastName ?? rows[0]?.last_name,
    si: req.body.si ?? req.body.SI ?? rows[0]?.si,
    regNo: rows[0]?.reg_no,
    clubId: rows[0]?.club_id,
    clubUserId: rows[0]?.club_user_id,
    sport: rows[0]?.sport ?? 1,
    year: rows[0]?.year ?? new Date().getUTCFullYear(),
    proxyOnly: rows[0] ? !!rows[0].proxy_only : false,
  });
  res.json(apiOk(updated));
}

async function handleCreateClubUser(req: Request, res: Response): Promise<void> {
  try {
    const clubUser = await upsertClubUser(req.body);
    res.json(apiOk(clubUser));
  } catch (error) {
    res.json(apiError(error instanceof Error ? error.message : 'createClubUser failed'));
  }
}

async function handleEditClubUser(req: Request, res: Response): Promise<void> {
  const clubUserId = asString(req.body.clubuserid ?? req.body.clubUserId ?? req.body.clubuser ?? req.body.ID ?? req.body.id);
  const userId = asString(req.body.userid ?? req.body.userId ?? req.body.user ?? req.body.UserID);
  if (!clubUserId && !userId) {
    res.json(apiError('Missing clubuserid or userid'));
    return;
  }

  const [rows] = clubUserId
    ? await pool.query<UserRow[]>('SELECT * FROM mock_users WHERE club_user_id = ? AND deleted = 0 LIMIT 1', [clubUserId])
    : await pool.query<UserRow[]>('SELECT * FROM mock_users WHERE user_id = ? AND deleted = 0 LIMIT 1', [userId]);
  const current = rows[0];
  if (!current) {
    res.json(apiError('Club user not found'));
    return;
  }

  const clubUser = await upsertClubUser({
    ...req.body,
    userId: userId || current.user_id,
    clubUserId: clubUserId || current.club_user_id,
    regNo: req.body.regno ?? req.body.regNo ?? current.reg_no,
    clubId: req.body.clubid ?? req.body.clubId ?? current.club_id,
    licence: req.body.licence ?? req.body.Licence ?? current.licence,
    firstName: req.body.firstname ?? req.body.firstName ?? current.first_name,
    lastName: req.body.lastname ?? req.body.lastName ?? current.last_name,
    si: req.body.si ?? req.body.SI ?? current.si,
    sport: req.body.sport ?? current.sport,
    year: req.body.year ?? current.year,
    proxyOnly: !!current.proxy_only,
  });
  res.json(apiOk(clubUser));
}

async function handleOrisApi(req: Request, res: Response): Promise<void> {
  attachApiLogger(req, res);

  if (await maybeApplyFaultMode(req, res)) return;

  const method = asString(req.query.method ?? req.body.method);
  switch (method) {
    case 'getEvent':
      await handleGetEvent(req, res);
      return;
    case 'getEventList':
      await handleGetEventList(req, res);
      return;
    case 'getRegistration':
      await handleGetRegistration(req, res);
      return;
    case 'getUser':
      await handleGetUser(req, res);
      return;
    case 'getClubUsers':
    case 'getClubUserList':
      await handleGetClubUsers(req, res);
      return;
    case 'getEventEntries':
      await handleGetEventEntries(req, res);
      return;
    case 'getEventServiceEntries':
      await handleGetEventServiceEntries(req, res);
      return;
    case 'createEntry':
      await handleCreateEntry(req, res);
      return;
    case 'updateEntry':
      await handleUpdateEntry(req, res);
      return;
    case 'deleteEntry':
      await handleDeleteEntry(req, res);
      return;
    case 'createPerson':
      await handleCreatePerson(req, res);
      return;
    case 'editPerson':
      await handleEditPerson(req, res);
      return;
    case 'createClubUser':
      await handleCreateClubUser(req, res);
      return;
    case 'editClubUser':
      await handleEditClubUser(req, res);
      return;
    default:
      res.json(await fetchUpstream(new URLSearchParams({ ...(req.query as Record<string, string>), ...(req.body as Record<string, string>) })) ?? apiError(`Unsupported method ${method}`));
  }
}

function renderTestbenchPage(
  settings: MockSettings,
  events: EventRow[],
  users: UserRow[],
  entries: EntryRow[],
  classesByEvent: Record<string, EventClassRow[]>,
  logText: string,
): string {
  const apiLink = (label: string, params: Record<string, string>) => (
    `<a href="/API/?${new URLSearchParams({ format: 'json', ...params }).toString()}" target="_blank" rel="noreferrer">${escapeHtml(label)}</a>`
  );
  const eventLabel = (eventId: string) => {
    const event = events.find((row) => row.id === eventId);
    return event ? `${event.id} ${event.name ?? ''}`.trim() : eventId;
  };
  const userByClubUserId = new Map(users.map((row) => [row.club_user_id ?? row.user_id, row]));
  const userLabel = (clubUserId: string | null) => {
    if (!clubUserId) return '';
    const user = userByClubUserId.get(clubUserId);
    if (!user) return clubUserId;
    return `${clubUserId} ${user.reg_no ?? ''} ${user.last_name ?? ''} ${user.first_name ?? ''}`.trim();
  };
  const eventRows = events.map((row) => (
    `<tr>
      <td>${escapeHtml(row.id)}</td>
      <td>${escapeHtml(row.date)}</td>
      <td>${escapeHtml(row.name)}</td>
      <td>${escapeHtml(row.place)}</td>
      <td class="links">
        ${apiLink('getEvent', { method: 'getEvent', id: row.id })}
        ${apiLink('getEventEntries', { method: 'getEventEntries', eventid: row.id })}
        ${apiLink('getEventServiceEntries', { method: 'getEventServiceEntries', eventid: row.id })}
      </td>
      <td class="actions">
        <button type="button" class="small edit-row" data-table="races" data-id="${escapeHtml(row.id)}">Edit</button>
        <button type="button" class="small danger delete-row" data-table="races" data-id="${escapeHtml(row.id)}">Delete</button>
      </td>
    </tr>`
  )).join('');
  const userRows = users.map((row) => (
    `<tr>
      <td>${escapeHtml(row.user_id)}</td>
      <td>${escapeHtml(row.club_user_id)}</td>
      <td>${escapeHtml(row.reg_no)}</td>
      <td>${escapeHtml(row.last_name)} ${escapeHtml(row.first_name)}</td>
      <td>${escapeHtml(row.si)}</td>
      <td class="links">
        ${apiLink('getUser', { method: 'getUser', userid: row.user_id })}
        ${row.reg_no ? apiLink('getUser reg', { method: 'getUser', rgnum: row.reg_no }) : ''}
        ${apiLink('getClubUsers', { method: 'getClubUsers', user: row.user_id })}
      </td>
      <td class="actions">
        <button type="button" class="small edit-row" data-table="users" data-id="${escapeHtml(row.user_id)}">Edit</button>
        <button type="button" class="small danger delete-row" data-table="users" data-id="${escapeHtml(row.user_id)}">Delete</button>
      </td>
    </tr>`
  )).join('');
  const entryRows = entries.map((row) => {
    const userId = row.club_user_id ?? '';
    return `<tr>
      <td>${escapeHtml(row.entry_id)}</td>
      <td>${escapeHtml(eventLabel(row.event_id))}</td>
      <td>${escapeHtml(userLabel(userId))}</td>
      <td>${escapeHtml(row.class_desc ?? row.class_id)}</td>
      <td class="links">
        ${apiLink('getEventEntries', { method: 'getEventEntries', eventid: row.event_id })}
        ${apiLink('getEvent', { method: 'getEvent', id: row.event_id })}
        ${row.reg_no ? apiLink('getUser', { method: 'getUser', rgnum: row.reg_no }) : ''}
      </td>
      <td class="actions">
        <button type="button" class="small edit-row" data-table="participants" data-id="${escapeHtml(row.entry_id)}">Edit</button>
        <button type="button" class="small danger delete-row" data-table="participants" data-id="${escapeHtml(row.entry_id)}">Delete</button>
      </td>
    </tr>`;
  }).join('');
  const selected = (mode: RuntimeMode) => (settings.mode === mode ? 'selected' : '');
  const testbenchData = {
    races: events,
    users,
    participants: entries,
    classesByEvent,
    sports: ORIS_SPORTS,
    levels: ORIS_LEVELS,
    regions: ORIS_REGIONS,
    defaults: {
      date: todayPlus(30),
      entryDate1: dateTimeLocalPlus(20),
      regNo: `${config.defaultClubAbbr}9999`,
      year: new Date().getUTCFullYear(),
    },
  };

  return `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ORIS Mock</title>
  <link rel="icon" type="image/svg+xml" href="${TESTBENCH_BASE}/favicon.svg" />
  <style>
    body { margin: 0; font-family: Arial, sans-serif; color: #202a36; background: #f7f9fc; }
    main { max-width: 1240px; margin: 0 auto; padding: 24px 16px 40px; }
    h1, h2 { margin: 0; }
    h1 { font-size: 28px; }
    h2 { font-size: 18px; }
    p { margin: 8px 0 16px; }
    code { background: #e9eef5; padding: 2px 5px; border-radius: 4px; }
    .settings { display: grid; grid-template-columns: repeat(4, minmax(150px, 1fr)); gap: 12px; align-items: end; margin: 0; }
    label { display: block; font-size: 12px; font-weight: 700; color: #52616f; }
    input, textarea, select, button { width: 100%; box-sizing: border-box; padding: 9px 10px; border-radius: 6px; border: 1px solid #bcccdc; font: inherit; }
    input, textarea, select { margin-top: 5px; background: #fff; color: #202a36; }
    textarea { min-height: 78px; resize: vertical; }
    button { border: 0; background: #0b5cad; color: #fff; font-weight: 700; cursor: pointer; }
    button.secondary { background: #52616f; }
    button.danger { background: #b42318; }
    button.small { width: auto; padding: 6px 10px; font-size: 12px; }
    .tabbar { display: flex; gap: 6px; border-bottom: 1px solid #ccd6e0; margin-top: 4px; }
    .tabbar button { width: auto; color: #202a36; background: transparent; border: 1px solid transparent; border-bottom: 0; border-radius: 6px 6px 0 0; padding: 10px 14px; }
    .tabbar button.right-tab { margin-left: auto; }
    .tabbar button.active { background: #fff; border-color: #ccd6e0; color: #0b5cad; }
    .tab { display: none; background: #fff; border: 1px solid #ccd6e0; border-top: 0; padding: 14px; }
    .tab.active { display: block; }
    .toolbar { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 12px; }
    .toolbar button, .toolbar form { width: auto; margin: 0; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 780px; }
    th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #e4e7eb; vertical-align: top; }
    th { background: #edf2f7; font-size: 12px; color: #52616f; }
    td.links { min-width: 210px; }
    .links a { display: inline-block; margin: 0 8px 4px 0; color: #0b5cad; }
    .actions { display: flex; gap: 6px; align-items: center; }
    pre.log { min-height: 420px; max-height: 620px; overflow: auto; margin: 0; padding: 12px; background: #111827; color: #e5e7eb; border-radius: 6px; white-space: pre-wrap; }
    dialog { width: min(720px, calc(100% - 28px)); border: 1px solid #bcccdc; border-radius: 8px; padding: 0; }
    dialog::backdrop { background: rgba(17, 24, 39, .45); }
    .dialog-head, .dialog-actions { display: flex; justify-content: space-between; gap: 12px; align-items: center; padding: 14px 16px; border-bottom: 1px solid #e4e7eb; }
    .dialog-actions { border-top: 1px solid #e4e7eb; border-bottom: 0; justify-content: flex-end; }
    .dialog-actions button { width: auto; min-width: 96px; }
    .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; padding: 16px; }
    .form-grid .wide { grid-column: 1 / -1; }
    fieldset.choice-field { margin: 0; padding: 0; border: 0; }
    fieldset.choice-field legend { margin-bottom: 6px; font-size: 12px; font-weight: 700; color: #52616f; }
    .choice-group { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 6px 12px; padding: 10px; border: 1px solid #bcccdc; border-radius: 6px; }
    .choice-group label { display: flex; gap: 7px; align-items: flex-start; font-size: 13px; font-weight: 400; color: #202a36; }
    .choice-group input { width: auto; margin: 2px 0 0; }
    @media (max-width: 720px) {
      .settings, .form-grid { grid-template-columns: 1fr; }
      .toolbar { align-items: flex-start; flex-direction: column; }
    }
  </style>
</head>
<body>
  <main>
    <h1>ORIS Mock</h1>
    <p>ORIS-compatible endpoint: <code>/API/</code>. Upstream fallback: <code>${escapeHtml(config.upstreamBaseUrl)}</code>.</p>

    <nav class="tabbar" aria-label="Testbench tables">
      <button type="button" data-tab="races" class="active">Races</button>
      <button type="button" data-tab="users">Users</button>
      <button type="button" data-tab="participants">Participants</button>
      <button type="button" data-tab="network" class="right-tab">Network</button>
      <button type="button" data-tab="log">Log</button>
    </nav>

    <section id="tab-races" class="tab active">
      <div class="toolbar"><h2>Races</h2><button type="button" class="create-row" data-table="races">Create</button></div>
      <div class="table-wrap"><table><thead><tr><th>ID</th><th>Date</th><th>Name</th><th>Place</th><th>API requests</th><th>Action</th></tr></thead><tbody>${eventRows}</tbody></table></div>
    </section>
    <section id="tab-users" class="tab">
      <div class="toolbar"><h2>Users</h2><button type="button" class="create-row" data-table="users">Create</button></div>
      <div class="table-wrap"><table><thead><tr><th>User ID</th><th>Club user ID</th><th>RegNo</th><th>Name</th><th>SI</th><th>API requests</th><th>Action</th></tr></thead><tbody>${userRows}</tbody></table></div>
    </section>
    <section id="tab-participants" class="tab">
      <div class="toolbar"><h2>Participants</h2><button type="button" class="create-row" data-table="participants">Create</button></div>
      <div class="table-wrap"><table><thead><tr><th>Entry ID</th><th>Race</th><th>User</th><th>Class</th><th>API requests</th><th>Action</th></tr></thead><tbody>${entryRows}</tbody></table></div>
    </section>
    <section id="tab-network" class="tab">
      <div class="toolbar"><h2>Network</h2></div>
      <form class="settings" method="post" action="${TESTBENCH_BASE}/settings">
        <label>Mode
          <select name="mode">
            <option value="normal" ${selected('normal')}>normal</option>
            <option value="force_client_error" ${selected('force_client_error')}>force_client_error</option>
            <option value="service_down" ${selected('service_down')}>service_down</option>
            <option value="delay" ${selected('delay')}>delay</option>
            <option value="hang" ${selected('hang')}>hang</option>
            <option value="close_connection" ${selected('close_connection')}>close_connection</option>
          </select>
        </label>
        <label>Response delay (ms)
          <input type="number" min="0" max="600000" name="responseDelayMs" value="${settings.responseDelayMs}" />
        </label>
        <label>Forced status
          <input type="number" min="400" max="599" name="forceStatusCode" value="${settings.forceStatusCode}" />
        </label>
        <button type="submit">Save settings</button>
      </form>
    </section>
    <section id="tab-log" class="tab">
      <div class="toolbar">
        <h2>API request log</h2>
        <form method="post" action="${TESTBENCH_BASE}/log/clear"><button type="submit" class="danger">Clear log</button></form>
      </div>
      <pre class="log">${escapeHtml(logText || 'No API requests logged yet.')}</pre>
    </section>

    <dialog id="editDialog">
      <form id="editForm">
        <div class="dialog-head">
          <h2 id="dialogTitle">Edit</h2>
          <button type="button" class="small secondary" id="closeDialog">Close</button>
        </div>
        <div id="dialogFields" class="form-grid"></div>
        <div class="dialog-actions">
          <button type="button" class="secondary" id="cancelDialog">Cancel</button>
          <button type="submit">Save</button>
        </div>
      </form>
    </dialog>
  </main>
  <script>
    const TESTBENCH_DATA = ${jsonScript(testbenchData)};
    const tableTitles = { races: 'Race', users: 'User', participants: 'Participant' };
    const rowKeys = { races: 'id', users: 'user_id', participants: 'entry_id' };
    const fieldConfig = {
      races: [
        { name: 'id', row: 'id', label: 'ID', readOnlyOnEdit: true },
        { name: 'proxyOnly', row: 'proxy_only', label: 'Proxy only', type: 'checkbox', defaultValue: true },
        { name: 'name', row: 'name', label: 'Name', defaultValue: 'Proxy ORIS race' },
        { name: 'date', row: 'date', label: 'Date', type: 'date', defaultName: 'date' },
        { name: 'place', row: 'place', label: 'Place', defaultValue: 'Proxy place' },
        { name: 'entryStart', row: 'entry_start', label: 'Entry start', type: 'datetime-local' },
        { name: 'entryDate1', row: 'entry_date1', label: 'Entry deadline 1', type: 'datetime-local', defaultName: 'entryDate1' },
        { name: 'entryDate2', row: 'entry_date2', label: 'Entry deadline 2', type: 'datetime-local' },
        { name: 'entryDate3', row: 'entry_date3', label: 'Entry deadline 3', type: 'datetime-local' },
        { name: 'stages', row: 'stages', label: 'Stages', type: 'number', defaultValue: 1 },
        { name: 'sport', row: 'sport_id', label: 'Sport', type: 'select', source: 'sports', defaultValue: 'OB' },
        { name: 'levels', row: 'level_id', label: 'Levels', type: 'checkboxGroup', source: 'levels', defaultValue: ['OŽ'], wide: true },
        { name: 'ranking', row: 'ranking', label: 'Ranking', defaultValue: '1' },
        { name: 'org', row: 'org_abbr', label: 'Org abbr' },
        { name: 'regionIds', row: 'region_id', label: 'Oblast', type: 'checkboxGroup', source: 'regions', defaultValue: ['JM'], wide: true },
        { name: 'classes', label: 'Classes', type: 'classList', defaultValue: 'H21;D21;H35', wide: true },
        { name: 'cancelled', row: 'cancelled', label: 'Cancelled', type: 'checkbox' },
      ],
      users: [
        { name: 'userId', row: 'user_id', label: 'User ID', readOnlyOnEdit: true },
        { name: 'proxyOnly', row: 'proxy_only', label: 'Proxy only', type: 'checkbox', defaultValue: true },
        { name: 'clubUserId', row: 'club_user_id', label: 'Club user ID' },
        { name: 'regNo', row: 'reg_no', label: 'RegNo', defaultName: 'regNo' },
        { name: 'firstName', row: 'first_name', label: 'First name', defaultValue: 'Proxy' },
        { name: 'lastName', row: 'last_name', label: 'Last name', defaultValue: 'Runner' },
        { name: 'si', row: 'si', label: 'SI' },
        { name: 'clubId', row: 'club_id', label: 'Club ID' },
        { name: 'licence', row: 'licence', label: 'Licence' },
        { name: 'sport', row: 'sport', label: 'Sport', type: 'number', defaultValue: 1 },
        { name: 'year', row: 'year', label: 'Year', type: 'number', defaultName: 'year' },
      ],
      participants: [
        { name: 'entryId', row: 'entry_id', label: 'Entry ID', readOnlyOnEdit: true },
        { name: 'proxyOnly', row: 'proxy_only', label: 'Proxy only', type: 'checkbox', defaultValue: true },
        { name: 'eventId', row: 'event_id', label: 'Race', type: 'select', source: 'races' },
        { name: 'clubUserId', row: 'club_user_id', label: 'User', type: 'select', source: 'users' },
        { name: 'classChoice', label: 'Class', type: 'select', source: 'classes', wide: true },
        { name: 'name', row: 'name', label: 'Name' },
        { name: 'rentSI', row: 'rent_si', label: 'Rent SI', type: 'checkbox' },
        { name: 'licence', row: 'licence', label: 'Licence' },
        { name: 'fee', row: 'fee', label: 'Fee', type: 'number' },
        { name: 'entryStop', row: 'entry_stop', label: 'Entry stop', type: 'number' },
        { name: 'si', row: 'si', label: 'SI' },
        { name: 'note', row: 'note', label: 'Note', type: 'textarea', wide: true },
      ],
    };

    const dialog = document.getElementById('editDialog');
    const form = document.getElementById('editForm');
    const fields = document.getElementById('dialogFields');
    const title = document.getElementById('dialogTitle');
    let currentTable = 'races';
    let currentMode = 'create';
    let currentRow = null;

    function localDateTime(value) {
      if (!value) return '';
      return String(value).replace(' ', 'T').slice(0, 16);
    }

    function rowValue(row, field) {
      if (!row) {
        if (field.defaultName) return TESTBENCH_DATA.defaults[field.defaultName] ?? '';
        return field.defaultValue ?? '';
      }
      if (field.name === 'classChoice') {
        return classChoiceValue(row);
      }
      if (field.type === 'classList') {
        const classRows = TESTBENCH_DATA.classesByEvent[row.id] || [];
        if (classRows.length === 0 && row.proxy_only) return field.defaultValue ?? '';
        return classRows.filter((item) => !item.deleted).map((item) => item.name || item.class_id).join(';');
      }
      if (field.type === 'checkboxGroup') {
        return String(row[field.row ?? field.name] || '').split(',').map((item) => item.trim()).filter(Boolean);
      }
      return row[field.row ?? field.name] ?? '';
    }

    function raceOptions() {
      return TESTBENCH_DATA.races.map((race) => ({
        value: race.id,
        label: [race.id, race.date, race.name].filter(Boolean).join(' - '),
      }));
    }

    function userOptions() {
      return TESTBENCH_DATA.users.map((user) => {
        const value = user.club_user_id || user.user_id;
        return {
          value,
          label: [value, user.reg_no, user.last_name, user.first_name].filter(Boolean).join(' - '),
        };
      });
    }

    function classOptions(eventId) {
      const classRows = TESTBENCH_DATA.classesByEvent[eventId] || [];
      return classRows
        .filter((item) => !item.deleted)
        .map((item) => ({
          value: JSON.stringify({ id: item.class_id, name: item.name || item.class_id }),
          label: [item.class_id, item.name].filter(Boolean).join(' - '),
        }));
    }

    function listOptions(source) {
      return (TESTBENCH_DATA[source] || []).map((item) => ({
        value: source === 'sports' || source === 'levels' ? item.name : item.id,
        label: source === 'sports' || source === 'levels'
          ? [item.name, item.description].filter(Boolean).join(' - ')
          : [item.id, item.name].filter(Boolean).join(' - '),
      }));
    }

    function classChoiceValue(row) {
      const currentId = String(row.class_id || '');
      const currentName = String(row.class_desc || '');
      for (const option of classOptions(row.event_id || '')) {
        const cls = JSON.parse(option.value);
        if (String(cls.id) === currentId || String(cls.name) === currentName || String(cls.name) === currentId) {
          return option.value;
        }
      }
      return JSON.stringify({ id: currentId, name: currentName });
    }

    function optionList(field, row) {
      if (field.source === 'races') return raceOptions();
      if (field.source === 'users') return userOptions();
      if (field.source === 'classes') {
        const eventId = document.querySelector('[name="eventId"]')?.value || row?.event_id || '';
        return classOptions(eventId);
      }
      if (field.source === 'sports' || field.source === 'levels' || field.source === 'regions') {
        return listOptions(field.source);
      }
      return [];
    }

    function renderField(field, row) {
      if (field.type === 'checkboxGroup') {
        const fieldset = document.createElement('fieldset');
        fieldset.className = 'choice-field' + (field.wide ? ' wide' : '');
        const legend = document.createElement('legend');
        legend.textContent = field.label;
        fieldset.appendChild(legend);
        const group = document.createElement('div');
        group.className = 'choice-group';
        const selectedValues = new Set(rowValue(row, field).map(String));
        for (const option of optionList(field, row)) {
          const itemLabel = document.createElement('label');
          const input = document.createElement('input');
          input.type = 'checkbox';
          input.name = field.name;
          input.value = option.value;
          input.checked = selectedValues.has(String(option.value));
          itemLabel.append(input, document.createTextNode(option.label));
          group.appendChild(itemLabel);
        }
        fieldset.appendChild(group);
        return fieldset;
      }

      const label = document.createElement('label');
      if (field.wide) label.className = 'wide';
      label.textContent = field.label;
      const value = rowValue(row, field);
      let control;

      if (field.type === 'textarea') {
        control = document.createElement('textarea');
        control.value = value;
      } else if (field.type === 'select') {
        control = document.createElement('select');
        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = '-';
        control.appendChild(empty);
        for (const option of optionList(field, row)) {
          const item = document.createElement('option');
          item.value = option.value;
          item.textContent = option.label;
          if (String(option.value) === String(value)) item.selected = true;
          control.appendChild(item);
        }
      } else {
        control = document.createElement('input');
        control.type = field.type === 'classList' ? 'text' : field.type || 'text';
        if (field.type === 'checkbox') {
          control.checked = value === true || value === 1 || value === '1';
        } else if (field.type === 'datetime-local') {
          control.value = localDateTime(value);
        } else {
          control.value = value;
        }
      }

      control.name = field.name;
      if (field.readOnlyOnEdit && currentMode === 'edit') control.readOnly = true;
      label.appendChild(control);
      return label;
    }

    function refreshClassSelect() {
      const select = document.querySelector('[name="classChoice"]');
      if (!select) return;
      const current = select.value;
      select.replaceChildren();
      const empty = document.createElement('option');
      empty.value = '';
      empty.textContent = '-';
      select.appendChild(empty);
      for (const option of classOptions(document.querySelector('[name="eventId"]')?.value || '')) {
        const item = document.createElement('option');
        item.value = option.value;
        item.textContent = option.label;
        if (option.value === current) item.selected = true;
        select.appendChild(item);
      }
    }

    function openEditor(table, mode, row = null) {
      currentTable = table;
      currentMode = mode;
      currentRow = row;
      title.textContent = (mode === 'create' ? 'Create ' : 'Edit ') + tableTitles[table];
      fields.replaceChildren(...fieldConfig[table].map((field) => renderField(field, row)));
      document.querySelector('[name="eventId"]')?.addEventListener('change', refreshClassSelect);
      dialog.showModal();
    }

    function tableRow(table, id) {
      const key = rowKeys[table];
      return TESTBENCH_DATA[table].find((row) => String(row[key]) === String(id)) || null;
    }

    function payloadFromForm() {
      const payload = {};
      for (const field of fieldConfig[currentTable]) {
        if (field.type === 'checkboxGroup') {
          payload[field.name] = Array.from(form.querySelectorAll('input[name="' + field.name + '"]:checked')).map((input) => input.value);
          continue;
        }
        const control = form.elements.namedItem(field.name);
        if (!control) continue;
        if (field.type === 'checkbox') {
          payload[field.name] = control.checked;
        } else {
          payload[field.name] = control.value;
        }
      }

      if (currentTable === 'participants') {
        const selectedUser = TESTBENCH_DATA.users.find((user) => (user.club_user_id || user.user_id) === payload.clubUserId);
        if (selectedUser) {
          payload.regNo = selectedUser.reg_no || '';
          if (!payload.name) payload.name = [selectedUser.last_name, selectedUser.first_name].filter(Boolean).join(' ');
          if (!payload.licence) payload.licence = selectedUser.licence || '';
          if (!payload.si) payload.si = selectedUser.si || '';
        }
        if (payload.classChoice) {
          const selectedClass = JSON.parse(payload.classChoice);
          payload.classId = selectedClass.id;
          payload.classDesc = selectedClass.name;
        }
        delete payload.classChoice;
      }

      if (currentTable === 'races') {
        const classRows = TESTBENCH_DATA.classesByEvent[currentRow?.id || ''] || [];
        const activeClasses = classRows.filter((item) => !item.deleted);
        payload.classes = String(payload.classes ?? '')
          .split(';')
          .map((name) => name.trim())
          .filter(Boolean)
          .map((name) => {
            const existing = activeClasses.find((item) => (item.name || item.class_id) === name);
            return existing
              ? { ID: existing.class_id, Name: name, Fee: existing.fee ?? 150 }
              : { Name: name, Fee: 150 };
          });
        payload.replaceClasses = true;
      }

      for (const [key, value] of Object.entries(payload)) {
        if (value === '') delete payload[key];
      }
      return payload;
    }

    function endpointFor(payload) {
      if (currentTable === 'races') {
        return currentMode === 'edit'
          ? { url: '${TESTBENCH_BASE}/api/races/' + encodeURIComponent(currentRow.id), method: 'PUT' }
          : { url: '${TESTBENCH_BASE}/api/races', method: 'POST' };
      }
      if (currentTable === 'users') {
        return currentMode === 'edit'
          ? { url: '${TESTBENCH_BASE}/api/users/' + encodeURIComponent(currentRow.user_id), method: 'PUT' }
          : { url: '${TESTBENCH_BASE}/api/users', method: 'POST' };
      }
      return currentMode === 'edit'
        ? { url: '${TESTBENCH_BASE}/api/entries/' + encodeURIComponent(currentRow.entry_id), method: 'PUT' }
        : { url: '${TESTBENCH_BASE}/api/races/' + encodeURIComponent(payload.eventId || '') + '/entries', method: 'POST' };
    }

    function deleteEndpointFor(table, row) {
      if (table === 'races') {
        return '${TESTBENCH_BASE}/api/races/' + encodeURIComponent(row.id);
      }
      if (table === 'users') {
        const params = new URLSearchParams();
        if (row.reg_no) params.set('regNo', row.reg_no);
        if (row.sport) params.set('sport', row.sport);
        if (row.year) params.set('year', row.year);
        const query = params.toString();
        return '${TESTBENCH_BASE}/api/users/' + encodeURIComponent(row.user_id) + (query ? '?' + query : '');
      }
      return '${TESTBENCH_BASE}/api/entries/' + encodeURIComponent(row.entry_id);
    }

    function rowTitle(table, row) {
      if (table === 'races') return [row.id, row.name].filter(Boolean).join(' - ');
      if (table === 'users') return [row.user_id, row.reg_no, row.last_name, row.first_name].filter(Boolean).join(' - ');
      return [row.entry_id, row.event_id, row.class_desc || row.class_id].filter(Boolean).join(' - ');
    }

    async function deleteRow(table, id) {
      const row = tableRow(table, id);
      if (!row) return;
      if (!confirm('Delete ' + tableTitles[table].toLowerCase() + ' ' + rowTitle(table, row) + '?')) return;
      const response = await fetch(deleteEndpointFor(table, row), { method: 'DELETE' });
      if (!response.ok) {
        alert('Delete failed: HTTP ' + response.status);
        return;
      }
      window.location.href = '${TESTBENCH_BASE}?tab=' + encodeURIComponent(table);
    }

    async function saveCurrent(event) {
      event.preventDefault();
      const payload = payloadFromForm();
      if (currentTable === 'participants' && !payload.eventId) {
        alert('Choose a race first.');
        return;
      }
      const endpoint = endpointFor(payload);
      const response = await fetch(endpoint.url, {
        method: endpoint.method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      if (!response.ok) {
        alert('Save failed: HTTP ' + response.status);
        return;
      }
      window.location.href = '${TESTBENCH_BASE}?tab=' + encodeURIComponent(currentTable);
    }

    function activateTab(tab) {
      document.querySelectorAll('.tabbar button').forEach((button) => button.classList.toggle('active', button.dataset.tab === tab));
      document.querySelectorAll('.tab').forEach((panel) => panel.classList.toggle('active', panel.id === 'tab-' + tab));
    }

    document.querySelectorAll('.tabbar button').forEach((button) => button.addEventListener('click', () => activateTab(button.dataset.tab)));
    document.querySelectorAll('.create-row').forEach((button) => button.addEventListener('click', () => openEditor(button.dataset.table, 'create')));
    document.querySelectorAll('.edit-row').forEach((button) => button.addEventListener('click', () => openEditor(button.dataset.table, 'edit', tableRow(button.dataset.table, button.dataset.id))));
    document.querySelectorAll('.delete-row').forEach((button) => button.addEventListener('click', () => deleteRow(button.dataset.table, button.dataset.id)));
    document.getElementById('closeDialog').addEventListener('click', () => dialog.close());
    document.getElementById('cancelDialog').addEventListener('click', () => dialog.close());
    form.addEventListener('submit', saveCurrent);
    activateTab(new URLSearchParams(window.location.search).get('tab') || 'races');
  </script>
</body>
</html>`;
}

async function main(): Promise<void> {
  await ensureDatabase();

  if (process.argv.includes('--init-only')) {
    await pool.end();
    return;
  }

  const app = express();
  app.use(express.json());
  app.use(express.urlencoded({ extended: true }));

  app.get('/health', async (_req, res) => {
    res.json({
      ok: true,
      database: config.dbName,
      upstreamBaseUrl: config.upstreamBaseUrl,
      settings: await getSettings(),
    });
  });

  app.all('/API/', handleOrisApi);
  app.all('/API', handleOrisApi);

  app.get(testbenchPath('/favicon.svg'), (_req, res) => {
    res.type('image/svg+xml').sendFile(TESTBENCH_FAVICON_FILE);
  });

  app.get(testbenchPath(), async (_req, res) => {
    const settings = await getSettings();
    const [events] = await pool.query<EventRow[]>('SELECT * FROM mock_events WHERE deleted = 0 ORDER BY updated_at DESC LIMIT 100');
    const [users] = await pool.query<UserRow[]>('SELECT * FROM mock_users WHERE deleted = 0 ORDER BY updated_at DESC LIMIT 100');
    const [entries] = await pool.query<EntryRow[]>('SELECT * FROM mock_entries WHERE deleted = 0 ORDER BY updated_at DESC LIMIT 100');
    const classPairs = await Promise.all(events.map(async (event) => [event.id, await getEventClasses(event.id)] as const));
    const classesByEvent = Object.fromEntries(classPairs);
    res.type('html').send(renderTestbenchPage(settings, events, users, entries, classesByEvent, await readApiLog()));
  });

  app.post(testbenchPath('/races'), async (req, res) => {
    await upsertEvent(req.body);
    res.redirect(TESTBENCH_BASE);
  });

  app.post(testbenchPath('/users'), async (req, res) => {
    await upsertUser(req.body);
    res.redirect(TESTBENCH_BASE);
  });

  app.post(testbenchPath('/settings'), async (req, res) => {
    await updateSettings({
      mode: req.body.mode as RuntimeMode | undefined,
      responseDelayMs: req.body.responseDelayMs === undefined ? undefined : asNumber(req.body.responseDelayMs, 0),
      forceStatusCode: req.body.forceStatusCode === undefined ? undefined : asNumber(req.body.forceStatusCode, 503),
    });
    res.redirect(`${TESTBENCH_BASE}?tab=network`);
  });

  app.post(testbenchPath('/log/clear'), async (_req, res) => {
    await clearApiLog();
    res.redirect(`${TESTBENCH_BASE}?tab=log`);
  });

  app.get(testbenchPath('/api/log'), async (_req, res) => {
    res.json({ log: await readApiLog() });
  });

  app.delete(testbenchPath('/api/log'), async (_req, res) => {
    await clearApiLog();
    res.json({ ok: true });
  });

  app.get(testbenchPath('/api/settings'), async (_req, res) => {
    res.json(await getSettings());
  });

  app.post(testbenchPath('/api/settings'), async (req, res) => {
    const settings = await updateSettings({
      mode: req.body.mode as RuntimeMode | undefined,
      responseDelayMs: req.body.responseDelayMs === undefined ? undefined : asNumber(req.body.responseDelayMs, 0),
      forceStatusCode: req.body.forceStatusCode === undefined ? undefined : asNumber(req.body.forceStatusCode, 503),
    });

    res.json(settings);
  });

  app.post(testbenchPath('/api/reset'), async (_req, res) => {
    await pool.query('DELETE FROM mock_entries');
    await pool.query('DELETE FROM mock_services');
    await pool.query('DELETE FROM mock_event_classes');
    await pool.query('DELETE FROM mock_events');
    await pool.query('DELETE FROM mock_users');
    await updateSettings({ mode: 'normal' });
    res.json({ ok: true });
  });

  app.get(testbenchPath('/api/races'), async (_req, res) => {
    const [rows] = await pool.query<EventRow[]>('SELECT * FROM mock_events WHERE deleted = 0 ORDER BY updated_at DESC');
    const races = await Promise.all(rows.map((row) => getEvent(row.id)));
    res.json({ races: races.filter(Boolean) });
  });

  app.post(testbenchPath('/api/races'), async (req, res) => {
    res.status(201).json({ race: await upsertEvent(req.body) });
  });

  app.put(testbenchPath('/api/races/:id'), async (req, res) => {
    res.json({ race: await upsertEvent({ ...req.body, id: req.params.id }) });
  });

  app.delete(testbenchPath('/api/races/:id'), async (req, res) => {
    await pool.query(
      `
        INSERT INTO mock_events (id, proxy_only, deleted)
        VALUES (?, 0, 1)
        ON DUPLICATE KEY UPDATE deleted = 1
      `,
      [req.params.id],
    );
    res.json({ ok: true });
  });

  app.post(testbenchPath('/api/users'), async (req, res) => {
    res.status(201).json({ user: await upsertUser(req.body) });
  });

  app.get(testbenchPath('/api/users'), async (_req, res) => {
    const [rows] = await pool.query<UserRow[]>('SELECT * FROM mock_users WHERE deleted = 0 ORDER BY updated_at DESC');
    res.json({ users: rows.map((row) => ({ ...composeUser(row, null), proxy_only: !!row.proxy_only })) });
  });

  app.put(testbenchPath('/api/users/:userId'), async (req, res) => {
    res.json({ user: await upsertUser({ ...req.body, userId: req.params.userId }) });
  });

  app.delete(testbenchPath('/api/users/:userId'), async (req, res) => {
    const sport = asNumber(firstValue(req.query.sport), 1);
    const year = asNumber(firstValue(req.query.year), new Date().getUTCFullYear());
    await pool.query(
      `
        INSERT INTO mock_users (user_id, reg_no, sport, year, proxy_only, deleted)
        VALUES (?, ?, ?, ?, 0, 1)
        ON DUPLICATE KEY UPDATE deleted = 1, reg_no = VALUES(reg_no), sport = VALUES(sport), year = VALUES(year)
      `,
      [req.params.userId, firstValue(req.query.regNo) ?? null, sport, year],
    );
    res.json({ ok: true });
  });

  app.get([testbenchPath('/api/entries'), testbenchPath('/api/participants')], async (_req, res) => {
    const [rows] = await pool.query<EntryRow[]>('SELECT * FROM mock_entries WHERE deleted = 0 ORDER BY updated_at DESC');
    res.json({ participants: rows });
  });

  app.get(testbenchPath('/api/races/:eventId/entries'), async (req, res) => {
    const eventId = asString(req.params.eventId);
    const event = await getEvent(eventId);
    const [rows] = await pool.query<EntryRow[]>('SELECT * FROM mock_entries WHERE event_id = ? AND deleted = 0 ORDER BY updated_at DESC', [eventId]);
    const entries = await Promise.all(rows.map(async (row) => {
      const user = row.club_user_id ? await getUserByClubUserId(row.club_user_id) : null;
      return { ...composeEntry(row, null, event, user), proxy_only: !!row.proxy_only };
    }));
    res.json({ entries });
  });

  app.post(testbenchPath('/api/races/:eventId/entries'), async (req, res) => {
    res.status(201).json({ entry: await upsertEntry({ ...req.body, eventId: req.params.eventId }) });
  });

  app.put(testbenchPath('/api/entries/:entryId'), async (req, res) => {
    try {
      res.json({ entry: await upsertEntry({ ...req.body, entryId: req.params.entryId }) });
    } catch (error) {
      res.status(400).json({ error: error instanceof Error ? error.message : 'Entry update failed' });
    }
  });

  app.put(testbenchPath('/api/participants/:entryId'), async (req, res) => {
    try {
      res.json({ participant: await upsertEntry({ ...req.body, entryId: req.params.entryId }) });
    } catch (error) {
      res.status(400).json({ error: error instanceof Error ? error.message : 'Participant update failed' });
    }
  });

  app.delete(testbenchPath('/api/entries/:entryId'), async (req, res) => {
    await pool.query('UPDATE mock_entries SET deleted = 1 WHERE entry_id = ?', [req.params.entryId]);
    res.json({ ok: true });
  });

  app.delete(testbenchPath('/api/races/:eventId/entries/:entryId'), async (req, res) => {
    await pool.query(
      `
        INSERT INTO mock_entries (entry_id, event_id, proxy_only, deleted)
        VALUES (?, ?, 0, 1)
        ON DUPLICATE KEY UPDATE deleted = 1
      `,
      [req.params.entryId, req.params.eventId],
    );
    res.json({ ok: true });
  });

  app.post(testbenchPath('/api/races/:eventId/services'), async (req, res) => {
    const serviceId = nullableString(req.body, 'serviceId', 'ID');
    await pool.query(
      'INSERT INTO mock_services (service_id, event_id, club_user_id, name, amount, note, proxy_only, deleted) VALUES (?, ?, ?, ?, ?, ?, 1, 0)',
      [
        serviceId,
        req.params.eventId,
        nullableString(req.body, 'clubUserId', 'ClubUserID'),
        nullableString(req.body, 'name', 'Name'),
        nullableNumber(req.body, 'amount', 'Amount'),
        nullableString(req.body, 'note', 'Note'),
      ],
    );
    res.status(201).json({ service: { ID: serviceId, EventID: req.params.eventId } });
  });

  app.listen(config.port, config.host, () => {
    console.log(`ORIS mock server listening on http://${config.host}:${config.port}`);
    console.log(`Testbench UI: http://127.0.0.1:${config.port}${TESTBENCH_BASE}`);
  });
}

main().catch((error) => {
  console.error('Failed to start ORIS mock server:', error);
  process.exit(1);
});
