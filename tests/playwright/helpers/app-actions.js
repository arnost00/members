const { findRaceByName } = require('./api');
const { getTestMemberFixture } = require('../constants/members');
const {
  ensureHtmlSubmission,
  postFormInSession,
  readFormState,
} = require('./browser');

async function createPaymentRule(page, overrides = {}) {
  await page.goto('./fin_payrule_edit.php');

  const financeTypeInputs = page.locator('input[name="finance_type[]"][data-role="one"]');
  const financeType = overrides.financeType || (
    await financeTypeInputs.count()
      ? await financeTypeInputs.first().getAttribute('value')
      : null
  );

  const financeFields = financeType
    ? { 'finance_type[]': [financeType] }
    : { finance_type_all: '1' };

  const result = await postFormInSession(page, './fin_payrule_edit_exc.php', {
    'typ[]': [],
    'typ0[]': ['T'],
    'termin[]': [],
    zebricek_all: '1',
    payment_type: 'P',
    amount: '45',
    'uctovano[]': ['1'],
    ...financeFields,
    ...overrides.fields,
  });

  return ensureHtmlSubmission(result, 'Create payment rule');
}

function formatClubReg(reg) {
  return String(reg || 0).padStart(4, '0');
}

function getMemberDirectoryPath(role = 'clubAdmin') {
  switch (role) {
    case 'manager':
      return './index.php?id=500&subid=1';
    case 'clubAdmin':
      return './index.php?id=700&subid=1';
    default:
      throw new Error(`Unsupported member-directory role: ${role}`);
  }
}

async function findClubMemberByReg(page, reg, role) {
  await page.goto(getMemberDirectoryPath(role));

  return page.evaluate((formattedReg) => {
    const rows = Array.from(document.querySelectorAll('table.ctmc tbody tr'));

    for (const row of rows) {
      const cells = row.querySelectorAll('td');
      if (cells.length < 5) {
        continue;
      }

      const regText = cells[3].textContent.trim();
      if (regText !== formattedReg) {
        continue;
      }

      const editLink = cells[4].querySelector('a[href*="./user_edit.php"], a[href*="user_edit.php"]');
      return {
        reg: regText,
        editPath: editLink ? editLink.getAttribute('href') : null,
      };
    }

    return null;
  }, formatClubReg(reg));
}

async function ensureClubMember(page, overrides = {}) {
  const role = overrides.role || 'clubAdmin';
  const existingMember = await findClubMemberByReg(page, overrides.reg, role);

  if (existingMember) {
    return {
      created: false,
      reg: existingMember.reg,
      editPath: existingMember.editPath,
    };
  }

  const form = await readFormState(page, 'form[action^="./user_new_exc.php"]');

  const result = await postFormInSession(page, form.action, {
    ...form.fields,
    prijmeni: overrides.surname,
    jmeno: overrides.name,
    reg: overrides.reg || '0',
    si: overrides.chip || '0',
    datum: overrides.birthDate || '',
    adresa: overrides.address || '',
    mesto: overrides.city || '',
    psc: overrides.postalCode || '',
    email: overrides.email || '',
    domu: overrides.phoneHome || '',
    zam: overrides.phoneWork || '',
    mobil: overrides.phoneMobile || '',
    bank_account: overrides.bankAccount || '',
    poh: overrides.gender || form.fields.poh,
    lic: overrides.licenceOb || form.fields.lic,
    lic_mtbo: overrides.licenceMtbo || form.fields.lic_mtbo,
    lic_lob: overrides.licenceLob || form.fields.lic_lob,
    narodnost: overrides.nationality || form.fields.narodnost,
    rc: overrides.birthNumber || '',
  });

  ensureHtmlSubmission(result, 'Ensure club member');

  const createdMember = await findClubMemberByReg(page, overrides.reg, role);
  if (!createdMember) {
    throw new Error(`Club member with reg ${formatClubReg(overrides.reg)} was not found after ensure`);
  }

  return {
    created: true,
    reg: createdMember.reg,
    editPath: createdMember.editPath,
  };
}

async function ensureClubMembers(page, registrationIds, options = {}) {
  const role = options.role || 'clubAdmin';
  const ensuredMembers = [];

  for (const registrationId of registrationIds) {
    const fixture = getTestMemberFixture(registrationId);

    if (!fixture) {
      throw new Error(`No test member fixture is defined for registration id ${registrationId}`);
    }

    const member = await ensureClubMember(page, {
      role,
      ...fixture,
    });

    ensuredMembers.push({
      ...member,
      fixture,
    });
  }

  return ensuredMembers;
}

async function findRaceUserIdByReg(page, raceId, options = {}) {
  const groupId = options.groupId || 500;

  await page.goto(`./race_regs_1.php?gr_id=${groupId}&id=${raceId}&show_ed=1`);

  return page.evaluate((formattedReg) => {
    const select = document.querySelector('select[name="user_id"]');
    if (!select) {
      return null;
    }

    for (const option of Array.from(select.options)) {
      if (option.textContent.includes(`[${formattedReg}]`)) {
        return option.value;
      }
    }

    return null;
  }, formatClubReg(options.reg));
}

async function createRace(page, request, overrides = {}) {
  await page.goto('./race_new.php?type=0');

  const result = await postFormInSession(page, './race_new_exc.php?rtype=0', {
    ext_id: '',
    datum: overrides.date,
    nazev: overrides.name,
    misto: overrides.place || 'Brno',
    oddil: overrides.club || 'TST',
    typ0: overrides.eventType || 'Z',
    typ: overrides.sport || 'ob',
    ranking: overrides.ranking || '1',
    transport: overrides.transport || '0',
    accommodation: overrides.accommodation || '0',
    kapacita: overrides.capacity || '20',
    odkaz: overrides.url || '',
    etap: '1',
    poznamka: overrides.note || '',
    prihlasky1: overrides.entryDate1,
    prihlasky2: overrides.entryDate2 || '',
    prihlasky3: '',
    prihlasky4: '',
    prihlasky5: '',
    kategorie: overrides.categories || 'H21;D21',
    ...overrides.fields,
  });

  ensureHtmlSubmission(result, 'Create race');

  const race = await findRaceByName(request, overrides.name);
  if (!race) {
    throw new Error(`Created race "${overrides.name}" was not found in API list`);
  }

  return {
    ...race,
    id: race.race_id,
  };
}

async function updateRace(page, raceId, overrides = {}) {
  await page.goto(`./race_edit.php?id=${raceId}`);

  const form = await readFormState(page, 'form[name="form2"]');
  const result = await postFormInSession(page, form.action, {
    ...form.fields,
    ...overrides,
  });

  return ensureHtmlSubmission(result, `Update race ${raceId}`);
}

async function submitMemberRaceRegistration(page, fields) {
  const result = await postFormInSession(page, './us_race_regon_exc.php', fields);
  return ensureHtmlSubmission(result, 'Submit member race registration');
}

async function submitManagedRaceRegistration(page, raceId, fields) {
  const groupId = fields.groupId || 600;
  const postFields = { ...fields };
  delete postFields.groupId;

  const result = await postFormInSession(
    page,
    `./race_regs_1_exc.php?gr_id=${groupId}&id=${raceId}&show_ed=1`,
    postFields
  );

  return ensureHtmlSubmission(result, 'Submit small-manager race registration');
}

module.exports = {
  ensureClubMember,
  ensureClubMembers,
  findRaceUserIdByReg,
  createPaymentRule,
  createRace,
  submitManagedRaceRegistration,
  submitMemberRaceRegistration,
  updateRace,
};
