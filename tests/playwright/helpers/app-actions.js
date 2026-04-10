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
  const defaultFinanceType = await financeTypeInputs.count()
    ? await financeTypeInputs.first().getAttribute('value')
    : null;

  const normalizeValues = (value, fallback = []) => {
    const resolved = value === undefined ? fallback : value;
    if (resolved === null) {
      return [];
    }

    return Array.isArray(resolved)
      ? resolved.map((item) => String(item))
      : [String(resolved)];
  };

  const financeTypes = normalizeValues(
    overrides.financeTypes ?? overrides.financeType ?? defaultFinanceType,
    []
  );

  const financeFields = financeTypes.length
    ? { 'finance_type[]': financeTypes }
    : { finance_type_all: '1' };

  const result = await postFormInSession(page, './fin_payrule_edit_exc.php', {
    'typ[]': normalizeValues(overrides.sports ?? overrides.sport, []),
    'typ0[]': normalizeValues(overrides.eventTypes ?? overrides.eventType, []),
    'termin[]': normalizeValues(overrides.terms ?? overrides.term, []),
    'zebricek[]': normalizeValues(overrides.rankings ?? overrides.ranking, []),
    payment_type: String(overrides.paymentType ?? 'P'),
    amount: String(overrides.amount ?? '1'),
    'uctovano[]': normalizeValues(overrides.chargedItems ?? overrides.chargedItem, ['1']),
    ...financeFields,
    ...overrides.fields,
  });

  return ensureHtmlSubmission(result, 'Create payment rule');
}

function formatClubReg(reg) {
  return String(reg || 0).padStart(4, '0');
}

function getUserIdFromEditPath(editPath) {
  if (!editPath) {
    return null;
  }

  const match = editPath.match(/[?&]id=(\d+)/);
  return match ? match[1] : null;
}

function getUserIdFromFinancePath(path) {
  if (!path) {
    return null;
  }

  const match = path.match(/[?&]user_id=(\d+)/);
  return match ? match[1] : null;
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

      const editLink = row.querySelector('a[href*="./user_edit.php"], a[href*="user_edit.php"]');
      return {
        reg: regText,
        editPath: editLink ? editLink.getAttribute('href') : null,
      };
    }

    return null;
  }, formatClubReg(reg));
}

async function getFinanceDirectoryEntryByReg(page, reg, options = {}) {
  if (options.path) {
    await page.goto(options.path);
  }

  const entry = await page.evaluate((formattedReg) => {
    const rows = Array.from(document.querySelectorAll('table.ctmc tr'));

    function extractOverviewPath(href) {
      if (!href) {
        return null;
      }

      const popupMatch = href.match(/open_win(?:_ex)?\('([^']+)'/);
      if (popupMatch) {
        return popupMatch[1];
      }

      return href.startsWith('javascript:') ? null : href;
    }

    function parseAmountValue(text) {
      const normalized = String(text || '').replace(/\s+/g, '');
      const match = normalized.match(/-?\d+/);

      return {
        text: normalized,
        value: match ? Number(match[0]) : null,
      };
    }

    for (const row of rows) {
      const cells = Array.from(row.querySelectorAll('td'));
      if (cells.length < 5) {
        continue;
      }

      if (cells[3].textContent.trim() !== formattedReg) {
        continue;
      }

      const amountSpan = row.querySelector('span.amount, span.amountred, span.amountgreen');
      const overviewLink = Array.from(row.querySelectorAll('a')).find((link) => (
        link.textContent.includes('Přehled')
      ));
      const overviewPath = extractOverviewPath(overviewLink ? overviewLink.getAttribute('href') : null);
      const parsedAmount = parseAmountValue(amountSpan ? amountSpan.textContent : '');

      let userId = null;
      if (overviewPath) {
        try {
          const url = new URL(overviewPath, window.location.href);
          userId = url.searchParams.get('user_id');
        } catch (error) {
          userId = null;
        }
      }

      return {
        reg: formattedReg,
        amount: parsedAmount.value,
        amountText: parsedAmount.text,
        overviewPath,
        userId,
      };
    }

    return null;
  }, formatClubReg(reg));

  if (!entry) {
    return null;
  }

  return {
    ...entry,
    userId: entry.userId || getUserIdFromFinancePath(entry.overviewPath),
  };
}

async function ensureClubMember(page, overrides = {}) {
  const role = overrides.role || 'clubAdmin';
  const existingMember = await findClubMemberByReg(page, overrides.reg, role);

  if (existingMember) {
    return {
      created: false,
      reg: existingMember.reg,
      userId: getUserIdFromEditPath(existingMember.editPath),
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
    userId: getUserIdFromEditPath(createdMember.editPath),
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

async function setMemberFinanceType(page, userId, financeType) {
  if (!userId) {
    throw new Error('Cannot set member finance type without a user id');
  }

  const result = await postFormInSession(page, `./user_finance_type_exc.php?user_id=${userId}`, {
    type: String(financeType ?? 0),
  });

  return ensureHtmlSubmission(result, `Set finance type for user ${userId}`);
}

async function submitMemberFinanceEntry(page, userId, type, overrides = {}) {
  if (!userId) {
    throw new Error('Cannot submit member finance entry without a user id');
  }

  if (!['in', 'out'].includes(type)) {
    throw new Error(`Unsupported finance entry type: ${type}`);
  }

  await page.goto(`./user_finance_view.php?user_id=${userId}`);

  const form = await readFormState(page, `form.form[action^="?payment=${type}&user_id="]`);
  const result = await postFormInSession(page, form.action, {
    ...form.fields,
    amount: String(overrides.amount ?? ''),
    note: overrides.note ?? form.fields.note ?? '',
    datum: overrides.date ?? form.fields.datum,
    id_zavod: overrides.raceId ?? form.fields.id_zavod,
    ...overrides.fields,
  });

  const label = type === 'out' ? 'Submit member payment' : 'Submit member deposit';
  return ensureHtmlSubmission(result, `${label} for user ${userId}`);
}

async function submitFinanceTransferByReg(page, targetReg, overrides = {}) {
  const sourcePath = overrides.path || './index.php?id=200&subid=10';
  await page.goto(sourcePath);

  const targetUserId = await page.evaluate((formattedReg) => {
    const select = document.querySelector('form.form[action*="payment=both"] select[name="id_to"]');
    if (!select) {
      return null;
    }

    for (const option of Array.from(select.options)) {
      if (option.textContent.includes(formattedReg)) {
        return option.value;
      }
    }

    return null;
  }, formatClubReg(targetReg));

  if (!targetUserId || targetUserId === '-1') {
    throw new Error(`Could not find member transfer recipient with reg ${formatClubReg(targetReg)}`);
  }

  const form = await readFormState(page, 'form.form[action*="payment=both"]');
  const result = await postFormInSession(page, form.action, {
    ...form.fields,
    id_to: String(targetUserId),
    amount: String(overrides.amount ?? ''),
    note: overrides.note ?? form.fields.note ?? '',
    ...overrides.fields,
  });

  return ensureHtmlSubmission(result, `Submit member transfer to reg ${formatClubReg(targetReg)}`);
}

async function submitMemberTransferByReg(page, targetReg, overrides = {}) {
  return submitFinanceTransferByReg(page, targetReg, {
    path: './index.php?id=200&subid=10',
    ...overrides,
  });
}

async function stornoFirstMemberFinanceEntry(page, userId, overrides = {}) {
  if (!userId) {
    throw new Error('Cannot storno member finance entry without a user id');
  }

  await page.goto(`./user_finance_view.php?user_id=${userId}`);

  const stornoPath = await page.locator('a', { hasText: 'Storno' }).first().getAttribute('href');

  if (!stornoPath) {
    throw new Error(`No storno link was found for user ${userId}`);
  }

  const match = stornoPath.match(/[?&]trn_id=(\d+)/);
  if (!match) {
    throw new Error(`No transaction id was found in storno link for user ${userId}`);
  }

  const result = await postFormInSession(page, `./user_finance_view.php?payment=storno&trn_id=${match[1]}&user_id=${userId}`, {
    storno_note: overrides.note ?? '',
    ...overrides.fields,
  });

  return ensureHtmlSubmission(result, `Storno first member finance entry for user ${userId}`);
}

async function updateFirstMemberFinanceEntry(page, userId, overrides = {}) {
  if (!userId) {
    throw new Error('Cannot update member finance entry without a user id');
  }

  await page.goto(`./user_finance_view.php?user_id=${userId}`);

  const updatePath = await page.locator('a', { hasText: 'Změnit' }).first().getAttribute('href');

  if (!updatePath) {
    throw new Error(`No update link was found for user ${userId}`);
  }

  const match = updatePath.match(/[?&]trn_id=(\d+)/);
  if (!match) {
    throw new Error(`No transaction id was found in update link for user ${userId}`);
  }

  const result = await postFormInSession(page, `./user_finance_view.php?payment=update&user_id=${userId}&trn_id=${match[1]}`, {
    amount: String(overrides.amount ?? ''),
    note: overrides.note ?? '',
    id_zavod: overrides.raceId ?? 'null',
    ...overrides.fields,
  });

  return ensureHtmlSubmission(result, `Update first member finance entry for user ${userId}`);
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
  getFinanceDirectoryEntryByReg,
  setMemberFinanceType,
  stornoFirstMemberFinanceEntry,
  updateFirstMemberFinanceEntry,
  submitFinanceTransferByReg,
  submitMemberFinanceEntry,
  submitMemberTransferByReg,
  submitManagedRaceRegistration,
  submitMemberRaceRegistration,
  updateRace,
};
