const DEFAULT_ORIS_MOCK_ADMIN_URL = process.env.PLAYWRIGHT_ORIS_MOCK_ADMIN_URL
  || 'http://127.0.0.1:10301/__testbench/api';
const DEFAULT_ORIS_MOCK_API_URL = process.env.PLAYWRIGHT_ORIS_MOCK_API_URL
  || 'http://127.0.0.1:10301/API/';

function orisDateTimePlus(days, hour = 20, minute = 0) {
  const date = new Date();
  date.setUTCDate(date.getUTCDate() + days);
  date.setUTCHours(hour, minute, 0, 0);
  return date.toISOString().slice(0, 19).replace('T', ' ');
}

async function postJson(request, path, data = {}) {
  const response = await request.post(`${DEFAULT_ORIS_MOCK_ADMIN_URL}${path}`, { data });
  if (!response.ok()) {
    throw new Error(`ORIS mock POST ${path} failed with HTTP ${response.status()}: ${await response.text()}`);
  }
  return response.json();
}

async function putJson(request, path, data = {}) {
  const response = await request.put(`${DEFAULT_ORIS_MOCK_ADMIN_URL}${path}`, { data });
  if (!response.ok()) {
    throw new Error(`ORIS mock PUT ${path} failed with HTTP ${response.status()}: ${await response.text()}`);
  }
  return response.json();
}

async function deleteJson(request, path) {
  const response = await request.delete(`${DEFAULT_ORIS_MOCK_ADMIN_URL}${path}`);
  if (!response.ok()) {
    throw new Error(`ORIS mock DELETE ${path} failed with HTTP ${response.status()}: ${await response.text()}`);
  }
  return response.json();
}

async function getJson(request, path) {
  const response = await request.get(`${DEFAULT_ORIS_MOCK_ADMIN_URL}${path}`);
  if (!response.ok()) {
    throw new Error(`ORIS mock GET ${path} failed with HTTP ${response.status()}: ${await response.text()}`);
  }
  return response.json();
}

async function resetOrisMock(request) {
  return postJson(request, '/reset');
}

async function setOrisMockSettings(request, overrides = {}) {
  return postJson(request, '/settings', overrides);
}

async function getOrisMockSettings(request) {
  return getJson(request, '/settings');
}

async function createOrisMockRace(request, overrides = {}) {
  const hasClasses = overrides.classes !== undefined || overrides.Classes !== undefined;
  const defaults = {
    name: 'Playwright ORIS mock race',
    place: 'Playwright place',
    entryDate1: orisDateTimePlus(20),
  };

  if ( !hasClasses) {
    defaults.classes = [
      { Name: 'H21C', Fee: 150 },
      { Name: 'D21C', Fee: 150 },
    ];
  }

  return postJson(request, '/races', {
    ...defaults,
    ...overrides,
  });
}

async function updateOrisMockRace(request, eventId, overrides = {}) {
  return putJson(request, `/races/${eventId}`, {
    ...overrides,
    id: String(eventId),
  });
}

async function createOrisMockUser(request, overrides = {}) {
  return postJson(request, '/users', {
    firstName: 'Playwright',
    lastName: 'Runner',
    regNo: 'ZBM9999',
    clubId: '205',
    ...overrides,
  });
}

async function createOrisMockEntry(request, eventId, overrides = {}) {
  return postJson(request, `/races/${eventId}/entries`, overrides);
}

async function getOrisMockRaceEntries(request, eventId) {
  return getJson(request, `/races/${eventId}/entries`);
}

async function getOrisMockParticipants(request) {
  return getJson(request, '/participants');
}

async function deleteOrisMockRaceEntry(request, eventId, entryId) {
  return deleteJson(request, `/races/${eventId}/entries/${entryId}`);
}

async function getOrisApiEvent(request, eventId) {
  const params = new URLSearchParams({
    method: 'getEvent',
    format: 'json',
    id: String(eventId),
  });
  const response = await request.get(`${DEFAULT_ORIS_MOCK_API_URL}?${params.toString()}`);
  const json = await response.json();

  if (!response.ok() || json.Status !== 'OK') {
    throw new Error(`ORIS API getEvent failed with HTTP ${response.status()}: ${JSON.stringify(json)}`);
  }

  return json.Data;
}

async function getOrisApiEventEntries(request, eventId) {
  const params = new URLSearchParams({
    method: 'getEventEntries',
    format: 'json',
    eventid: String(eventId),
  });
  const response = await request.get(`${DEFAULT_ORIS_MOCK_API_URL}?${params.toString()}`);
  const json = await response.json();

  if (!response.ok() || json.Status !== 'OK') {
    throw new Error(`ORIS API getEventEntries failed with HTTP ${response.status()}: ${JSON.stringify(json)}`);
  }

  return json.Data;
}

async function createOrisApiEntry(request, overrides = {}) {
  const response = await request.post(DEFAULT_ORIS_MOCK_API_URL, {
    form: {
      method: 'createEntry',
      format: 'json',
      ...overrides,
    },
  });

  return {
    httpStatus: response.status(),
    body: await response.json(),
  };
}

async function callOrisApi(request, params, { post = false } = {}) {
  const query = { format: 'json', ...params };
  const response = post
    ? await request.post(DEFAULT_ORIS_MOCK_API_URL, { form: query })
    : await request.get(`${DEFAULT_ORIS_MOCK_API_URL}?${new URLSearchParams(query).toString()}`);

  return {
    httpStatus: response.status(),
    body: await response.json(),
  };
}

function getOrisApiClubUserList(request, clubkey) {
  return callOrisApi(request, { method: 'getClubUserList', clubkey }, { post: true });
}

function getOrisApiRegistration(request, sport, year) {
  return callOrisApi(request, { method: 'getRegistration', sport, year });
}

module.exports = {
  callOrisApi,
  createOrisApiEntry,
  createOrisMockEntry,
  createOrisMockRace,
  createOrisMockUser,
  deleteOrisMockRaceEntry,
  getOrisApiClubUserList,
  getOrisApiEvent,
  getOrisApiEventEntries,
  getOrisApiRegistration,
  getOrisMockParticipants,
  getOrisMockRaceEntries,
  getOrisMockSettings,
  resetOrisMock,
  setOrisMockSettings,
  updateOrisMockRace,
};
