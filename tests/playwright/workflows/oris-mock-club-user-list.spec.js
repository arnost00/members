const { test, expect } = require('@playwright/test');
const {
  createOrisMockUser,
  getOrisApiClubUserList,
  getOrisApiRegistration,
  setOrisMockSettings,
} = require('../helpers/oris-mock');

// getClubUserList (full club roster, current member state) and getRegistration
// (per sport/year registration snapshot) are easy to conflate: ORIS's own docs
// don't spell out that they read from different records, or that getClubUserList's
// response shape differs from every other read method here (members nested under
// Data.ClubMembers, keyed by "RegNum" instead of "RegNo" - verified against a real
// ORIS club roster). These tests pin down that behaviour against the mock so it
// can't silently regress.
const ORIS_CLUB_USER_LIST_WORKFLOW = {
  name: 'Oris mock getClubUserList vs getRegistration',
};

test.describe(ORIS_CLUB_USER_LIST_WORKFLOW.name, () => {
  test.describe.configure({ mode: 'serial' });

  const state = {};

  test.beforeAll(async ({ request }) => {
    const stamp = Date.now().toString().slice(-6);
    state.year = new Date().getUTCFullYear();
    state.userA = {
      userId: `71${stamp}`,
      clubUserId: `61${stamp}`,
      regNo: `ZBM${stamp}A`,
      si: '111111',
      regSi: '222222',
    };
    state.userB = {
      userId: `72${stamp}`,
      clubUserId: `62${stamp}`,
      regNo: `ZBM${stamp}B`,
      si: '333333',
    };

    await createOrisMockUser(request, {
      userId: state.userA.userId,
      clubUserId: state.userA.clubUserId,
      regNo: state.userA.regNo,
      firstName: 'Alfa',
      lastName: 'Roster',
      si: state.userA.si,
      regSi: state.userA.regSi,
      sport: 1,
      year: state.year,
    });
    await createOrisMockUser(request, {
      userId: state.userB.userId,
      clubUserId: state.userB.clubUserId,
      regNo: state.userB.regNo,
      firstName: 'Bravo',
      lastName: 'Roster',
      si: state.userB.si,
      sport: 1,
      year: state.year,
    });
  });

  test.afterAll(async ({ request }) => {
    await setOrisMockSettings(request, { clubUserListForbidden: false });
  });

  test('getClubUserList returns the whole roster with current SI under Data.ClubMembers, keyed by RegNum', async ({ request }) => {
    const { httpStatus, body } = await getOrisApiClubUserList(request, 'mockClubKey');
    expect(httpStatus).toBe(200);
    expect(body.Status).toBe('OK');

    const members = Object.values(body.Data.ClubMembers);
    const byRegNum = Object.fromEntries(members.map((entry) => [entry.RegNum, entry]));
    expect(byRegNum[state.userA.regNo].SI).toBe(state.userA.si);
    expect(byRegNum[state.userA.regNo].Valid).toBe(1);
    expect(byRegNum[state.userB.regNo].SI).toBe(state.userB.si);
  });

  test('getRegistration returns the stale per-year snapshot SI, not the current SI', async ({ request }) => {
    const { httpStatus, body } = await getOrisApiRegistration(request, 1, state.year);
    expect(httpStatus).toBe(200);
    expect(body.Status).toBe('OK');

    const entry = body.Data.find((item) => item.RegNo === state.userA.regNo);
    expect(entry).toBeTruthy();
    expect(entry.SI).toBe(state.userA.regSi);
    expect(entry.SI).not.toBe(state.userA.si);
  });

  test('getClubUserList failure can be simulated independently of other clubkey-protected methods', async ({ request }) => {
    await setOrisMockSettings(request, { clubUserListForbidden: true });

    const { httpStatus, body } = await getOrisApiClubUserList(request, 'mockClubKey');
    expect(httpStatus).toBe(200);
    expect(body.Status).toBe('Key not valid');
    expect(body.Data).toEqual([]);

    await setOrisMockSettings(request, { clubUserListForbidden: false });
    const restored = await getOrisApiClubUserList(request, 'mockClubKey');
    expect(restored.body.Status).toBe('OK');
  });
});
