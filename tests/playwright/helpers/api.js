const { DEFAULT_PASSWORD } = require('../constants/auth');

async function postJson(request, path, payload) {
  const response = await request.post(path, {
    data: payload,
  });

  const text = await response.text();
  let json;

  try {
    json = JSON.parse(text || '{}');
  } catch (error) {
    throw new Error(`Invalid JSON response from ${path}: ${text.slice(0, 200)}`);
  }

  return { response, json };
}

function ensureApiSuccess(response, json, label) {
  if (!response.ok()) {
    throw new Error(`${label} failed with HTTP ${response.status()}: ${json.message || 'Unknown error'}`);
  }

  if (json.message && !json.token && !Array.isArray(json) && Object.keys(json).length === 1) {
    throw new Error(`${label} returned an application error: ${json.message}`);
  }

  return json;
}

async function loginViaApi(request, username, password = DEFAULT_PASSWORD) {
  const { response, json } = await postJson(request, './api/user.php', {
    action: 'login',
    username,
    password,
  });

  ensureApiSuccess(response, json, `API login for ${username}`);

  if (!json.token) {
    throw new Error(`API login for ${username} did not return a token`);
  }

  return json.token;
}

async function getCurrentUser(request, token) {
  const { response, json } = await postJson(request, './api/user.php', {
    action: 'user_data',
    token,
  });

  return ensureApiSuccess(response, json, 'Load current user');
}

async function getManagingUsers(request, token) {
  const { response, json } = await postJson(request, './api/user.php', {
    action: 'managing_users',
    token,
  });

  return ensureApiSuccess(response, json, 'Load managing users');
}

async function listRaces(request) {
  const { response, json } = await postJson(request, './api/race.php', {
    action: 'list',
  });

  return ensureApiSuccess(response, json, 'List races');
}

async function getRaceDetail(request, raceId) {
  const { response, json } = await postJson(request, './api/race.php', {
    action: 'detail',
    race_id: raceId,
  });

  return ensureApiSuccess(response, json, `Load race detail ${raceId}`);
}

async function findRaceByName(request, raceName) {
  const races = await listRaces(request);
  return races.find((race) => race.name === raceName) || null;
}

module.exports = {
  findRaceByName,
  getCurrentUser,
  getManagingUsers,
  getRaceDetail,
  listRaces,
  loginViaApi,
};
