const { test, expect } = require('@playwright/test');
const {
  createOrisApiEntry,
  createOrisMockRace,
  getOrisApiEvent,
  getOrisMockRaceEntries,
} = require('./helpers/oris-mock');

const RELAY_RACE_WORKFLOW = {
  name: 'Relay Race',
  levelIds: '7',
  categories: ['H21', 'D21', 'H135', 'D135'],
  registrations: [
    { label: 'member', clubUserId: 'relay-member', category: 'D21' },
    { label: 'participant 8001', clubUserId: '8001', category: 'H135' },
    { label: 'participant 6107', clubUserId: '6107', category: 'H135' },
  ],
};

test.describe(RELAY_RACE_WORKFLOW.name, () => {
  test.describe.configure({ mode: 'serial' });

  test('rejects member and participant registrations without mock ORIS users', async ({ request }) => {
    const mockRace = await createOrisMockRace(request, {
      name: 'Playwright ORIS mock Relay Race',
      levelIds: RELAY_RACE_WORKFLOW.levelIds,
      classes: RELAY_RACE_WORKFLOW.categories.map((Name) => ({ Name, Fee: 150 })),
    });
    const eventId = String(mockRace.race.ID);
    const event = await getOrisApiEvent(request, eventId);
    const classIds = Object.fromEntries(event.Classes.map((raceClass) => [
      raceClass.Name,
      String(raceClass.ID),
    ]));

    expect(event.Level.ID).toBe(RELAY_RACE_WORKFLOW.levelIds);
    expect(Object.keys(classIds).sort()).toEqual([...RELAY_RACE_WORKFLOW.categories].sort());

    for (const registration of RELAY_RACE_WORKFLOW.registrations) {
      const result = await createOrisApiEntry(request, {
        clubuser: registration.clubUserId,
        class: classIds[registration.category],
      });

      expect(result.httpStatus, registration.label).toBe(200);
      expect(result.body, registration.label).toMatchObject({
        Method: 'createEntry',
        Format: 'json',
        Status: 'Mimo termín přihlášek',
        Data: [],
      });
    }

    expect(await getOrisMockRaceEntries(request, eventId)).toEqual({ entries: [] });
  });

  test('createEntry accepts a non-relay race while its registration is open', async ({ request }) => {
    const mockRace = await createOrisMockRace(request, {
      name: 'Playwright ORIS mock individual race',
      levelIds: '4',
      classes: [{ Name: 'H21', Fee: 150 }],
    });
    const eventId = String(mockRace.race.ID);
    const classId = String(mockRace.race.Classes[0].ID);

    const result = await createOrisApiEntry(request, {
      clubuser: 'individual-test-user',
      class: classId,
    });

    expect(result.httpStatus).toBe(200);
    expect(result.body.Status).toBe('OK');
    const { entries } = await getOrisMockRaceEntries(request, eventId);
    expect(entries).toHaveLength(1);
    expect(entries[0].Class.ID).toBe(classId);
  });
});
