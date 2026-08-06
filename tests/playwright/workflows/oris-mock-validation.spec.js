const { test, expect } = require('@playwright/test');

const ORIS_MOCK_API_URL = process.env.PLAYWRIGHT_ORIS_MOCK_API_URL
  || 'http://127.0.0.1:10301/API/';

const CLUB_KEY_REQUIRED_METHODS = [
  'createEntry',
  'updateEntry',
  'deleteEntry',
  'createServiceEntry',
  'updateServiceEntry',
  'deleteServiceEntry',
  'getClubEntryRights',
  'setClubEntryRights',
  'getClubUserList',
  'createPerson',
  'editPerson',
  'createClubUser',
  'editClubUser',
  'createUserLogin',
];

async function expectClubKeyError(request, method, clubkey, expectedStatus) {
  const data = { method, format: 'json' };
  if (clubkey !== undefined) {
    data.clubkey = clubkey;
  }

  const response = await request.post(ORIS_MOCK_API_URL, { form: data });
  expect(response.status()).toBe(200);
  await expect(response.json()).resolves.toMatchObject({
    Method: method,
    Format: 'json',
    Status: expectedStatus,
    Data: [],
  });
}

test.describe('ORIS mock club-key validation', () => {
  test('all protected methods reject a missing club key', async ({ request }) => {
    for (const method of CLUB_KEY_REQUIRED_METHODS) {
      await expectClubKeyError(
        request,
        method,
        undefined,
        'Zadejte všechny požadované informace'
      );
    }
  });

  test('all protected methods reject an invalid club key', async ({ request }) => {
    for (const method of CLUB_KEY_REQUIRED_METHODS) {
      await expectClubKeyError(request, method, 'notMockClubKey', 'Key not valid');
    }
  });
});
