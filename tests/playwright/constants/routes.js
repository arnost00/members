const GROUP_IDS = {
  user: 200,
  admin: 300,
  registrator: 400,
  manager: 500,
  smallManager: 600,
  clubAdmin: 700,
  finance: 800,
};

function indexRoute(id, subid = 0, label) {
  const path = subid === 0 ? `index.php?id=${id}` : `index.php?id=${id}&subid=${subid}`;
  return { id, subid, label, path };
}

function uniqueRoutes(routes) {
  return [...new Map(routes.map((route) => [route.path, route])).values()];
}

const COMMON_ROUTES = {
  updates: indexRoute(4, 0, 'Aktualitky'),
  memberRegistrations: indexRoute(3, 0, 'Přihlášky členů'),
};

const ROUTE_GROUPS = {
  member: [
    indexRoute(GROUP_IDS.user, 2, 'Přihlášky na závody'),
    indexRoute(GROUP_IDS.user, 10, 'Finance'),
    indexRoute(GROUP_IDS.user, 1, 'Nastavení přístupu'),
    indexRoute(GROUP_IDS.user, 3, 'Nastavení zákl.údajů'),
    indexRoute(GROUP_IDS.user, 4, 'Upozorňování'),
  ],
  registrator: [
    indexRoute(GROUP_IDS.registrator, 1, 'Přihlášky na závody'),
    indexRoute(GROUP_IDS.registrator, 4, 'Editace závodů'),
    indexRoute(GROUP_IDS.registrator, 5, 'ORIS log'),
  ],
  manager: [
    indexRoute(GROUP_IDS.manager, 2, 'Přihlášky na závody'),
    indexRoute(GROUP_IDS.manager, 1, 'Členská základna'),
    indexRoute(GROUP_IDS.manager, 3, 'Přiřazení skupin členů'),
    indexRoute(GROUP_IDS.manager, 4, 'Přehled m.trenérů'),
    indexRoute(GROUP_IDS.manager, 10, 'Finance'),
  ],
  smallManager: [
    indexRoute(GROUP_IDS.smallManager, 2, 'Přihlášky na závody'),
    indexRoute(GROUP_IDS.smallManager, 1, 'Členská základna'),
    indexRoute(GROUP_IDS.smallManager, 10, 'Finance'),
  ],
  clubAdmin: [
    indexRoute(GROUP_IDS.clubAdmin, 1, 'Členská základna'),
    ...((process.env.MEMBERS_E2E_SUITE === 'no-oris' || process.env.MEMBERS_E2E_SUITE === 'no-oris-key')
      ? []
      : [indexRoute(GROUP_IDS.clubAdmin, 2, 'Členové vs. ORIS')]),
  ],
  finance: [
    indexRoute(GROUP_IDS.finance, 1, 'Členská základna'),
    indexRoute(GROUP_IDS.finance, 2, 'Přehled závodů'),
    indexRoute(GROUP_IDS.finance, 3, 'Přehled reklamací'),
    indexRoute(GROUP_IDS.finance, 4, 'Typy příspěvků'),
    indexRoute(GROUP_IDS.finance, 5, 'Pravidla plateb'),
    indexRoute(GROUP_IDS.finance, 6, 'Nespárované bankovní platby'),
    indexRoute(GROUP_IDS.finance, 7, 'Historie transakcí'),
  ],
  admin: [
    indexRoute(GROUP_IDS.admin, 1, 'Servisní menu'),
    indexRoute(GROUP_IDS.admin, 2, 'Přihlášky na závody'),
    indexRoute(GROUP_IDS.admin, 5, 'Editace závodů'),
    indexRoute(GROUP_IDS.admin, 4, 'Účty / Náhled'),
    indexRoute(GROUP_IDS.admin, 6, 'Výpis změn'),
    indexRoute(GROUP_IDS.admin, 7, 'Historie plateb'),
    indexRoute(GROUP_IDS.admin, 8, 'Email info'),
  ],
};

const LOGIN_EXPECTATIONS = {
  administrator: {
    landingRoute: ROUTE_GROUPS.admin[0],
    accessibleRoutes: [
      COMMON_ROUTES.memberRegistrations,
      ...ROUTE_GROUPS.registrator,
      ...ROUTE_GROUPS.manager,
      ...ROUTE_GROUPS.clubAdmin,
      ...ROUTE_GROUPS.finance,
      ...ROUTE_GROUPS.admin,
    ],
  },
  registrar: {
    landingRoute: COMMON_ROUTES.updates,
    accessibleRoutes: [
      COMMON_ROUTES.memberRegistrations,
      ...ROUTE_GROUPS.member,
      ...ROUTE_GROUPS.registrator,
    ],
  },
  manager: {
    landingRoute: COMMON_ROUTES.updates,
    accessibleRoutes: [
      COMMON_ROUTES.memberRegistrations,
      ...ROUTE_GROUPS.member,
      ...ROUTE_GROUPS.manager,
    ],
  },
  clubAdmin: {
    landingRoute: COMMON_ROUTES.updates,
    accessibleRoutes: [
      COMMON_ROUTES.memberRegistrations,
      ...ROUTE_GROUPS.member,
      ...ROUTE_GROUPS.registrator,
      ...ROUTE_GROUPS.manager,
      ...ROUTE_GROUPS.clubAdmin,
      ...ROUTE_GROUPS.finance,
    ],
  },
  smallManager: {
    landingRoute: COMMON_ROUTES.updates,
    accessibleRoutes: [
      COMMON_ROUTES.memberRegistrations,
      ...ROUTE_GROUPS.member,
      ...ROUTE_GROUPS.smallManager,
    ],
  },
  member: {
    landingRoute: COMMON_ROUTES.updates,
    accessibleRoutes: [COMMON_ROUTES.memberRegistrations, ...ROUTE_GROUPS.member],
  },
  accountant: {
    landingRoute: COMMON_ROUTES.updates,
    accessibleRoutes: [
      COMMON_ROUTES.memberRegistrations,
      ...ROUTE_GROUPS.member,
      ...ROUTE_GROUPS.finance,
    ],
  },
};

const ALL_PROTECTED_ROUTES = uniqueRoutes([
  COMMON_ROUTES.memberRegistrations,
  ...Object.values(ROUTE_GROUPS).flat(),
]);

module.exports = {
  ALL_PROTECTED_ROUTES,
  COMMON_ROUTES,
  GROUP_IDS,
  LOGIN_EXPECTATIONS,
  ROUTE_GROUPS,
};
