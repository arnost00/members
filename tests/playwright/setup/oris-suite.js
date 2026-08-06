const { request } = require('@playwright/test');
const {
  getOrisMockSettings,
  setOrisMockSettings,
} = require('../helpers/oris-mock');

module.exports = async () => {
  const testSuite = process.env.MEMBERS_E2E_SUITE;
  if (testSuite !== 'no-oris' && testSuite !== 'no-oris-key') {
    return undefined;
  }

  const apiRequest = await request.newContext();
  const savedSettings = await getOrisMockSettings(apiRequest);

  await setOrisMockSettings(apiRequest, testSuite === 'no-oris'
    ? { mode: 'service_down', forceStatusCode: 503 }
    : { mode: 'normal' });

  await apiRequest.dispose();

  return async () => {
    const teardownRequest = await request.newContext();
    try {
      await setOrisMockSettings(teardownRequest, savedSettings);
    } finally {
      await teardownRequest.dispose();
    }
  };
};
