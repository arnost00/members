const { expect } = require('@playwright/test');
const {
  ensureHtmlSubmission,
  openPopup,
  postFormInSession,
} = require('./browser');
const {
  formatClubReg,
} = require('./app-actions');

async function openOrisRaceImportPopup(page, orisId) {
  await page.goto('./index.php?id=400&subid=4');

  const extIdInput = page.locator('#extID');
  await extIdInput.click();
  await extIdInput.fill('');
  await page.keyboard.type(String(orisId));

  return openPopup(page, async () => {
    await page.locator('#loadRaceByIdButton').click();
  });
}

async function readOrisRaceSummary(popup) {
  await expect(popup.locator('input[name="ext_id"]')).toBeVisible();

  return popup.evaluate(() => {
    const inputValue = (name) => {
      const node = document.querySelector(`input[name="${name}"]`);
      return node ? node.value.trim() : '';
    };

    return {
      extId: inputValue('ext_id'),
      date: inputValue('datum'),
      name: inputValue('nazev'),
      place: inputValue('misto'),
      club: inputValue('oddil'),
      alreadyExists: document.body.textContent.includes('ID již použito'),
    };
  });
}

async function findRaceInTableBySummary(page, summary, options = {}) {
  if (options.path) {
    await page.goto(options.path);
  }

  return page.evaluate((raceSummary) => {
    const normalize = (value) => String(value || '').replace(/\s+/g, ' ').trim();
    const requiredParts = [
      raceSummary.name,
      raceSummary.place,
      raceSummary.club,
    ].map(normalize).filter(Boolean);
    const requiredExtId = normalize(raceSummary.extId);

    function extractPath(href, pattern) {
      if (!href) {
        return null;
      }

      const match = href.match(pattern);
      return match ? match[1] : null;
    }

    for (const row of Array.from(document.querySelectorAll('tr'))) {
      if (!row.classList.contains('r1') && !row.classList.contains('r2') && !row.classList.contains('highlight')) {
        continue;
      }

      const cells = row.querySelectorAll('td');
      if (cells.length < 4) {
        continue;
      }

      const rowText = normalize(row.textContent);
      const hrefs = Array.from(row.querySelectorAll('a'))
        .map((link) => link.getAttribute('href') || '')
        .filter(Boolean);

      if (!requiredParts.every((part) => rowText.includes(part))) {
        continue;
      }

      if (requiredExtId && !hrefs.some((href) => href.includes(`id=${requiredExtId}`))) {
        continue;
      }

      const editPath = hrefs
        .map((href) => extractPath(href, /(?:open_win\(')?(\.\/race_edit\.php\?id=\d+)/))
        .find(Boolean) || null;
      const regsAllPath = hrefs
        .map((href) => extractPath(href, /(?:open_win\(')?(\.\/race_regs_all\.php\?[^']*id=\d+)/))
        .find(Boolean) || null;
      const financePath = hrefs
        .map((href) => extractPath(href, /(?:open_win\(')?(\.\/race_finance_view\.php\?race_id=\d+)/))
        .find(Boolean) || null;

      const raceIdMatch = (editPath || regsAllPath || financePath || '').match(/(?:id|race_id)=(\d+)/);

      return {
        raceId: raceIdMatch ? raceIdMatch[1] : null,
        editPath,
        regsAllPath,
        financePath,
        rowText,
      };
    }

    return null;
  }, summary);
}

async function ensureOrisRace(page, orisId) {
  const popup = await openOrisRaceImportPopup(page, orisId);
  const summary = await readOrisRaceSummary(popup);

  if (!summary.date || !summary.name || !summary.place || !summary.club) {
    throw new Error(`ORIS race ${orisId} did not load all summary fields`);
  }

  if (summary.alreadyExists) {
    const reloadPromise = page.waitForNavigation({
      url: /index\.php\?id=400&subid=4(?:&.*)?$/,
      waitUntil: 'load',
      timeout: 1000,
    }).catch(() => null);
    await popup.close();
    await reloadPromise;
  } else {
    await Promise.all([
      page.waitForNavigation({
        url: /index\.php\?id=400&subid=4(?:&.*)?$/,
        waitUntil: 'load',
      }),
      popup.waitForEvent('close'),
      popup.locator('input[type="submit"][value="Vytvořit závod"]').click(),
    ]);
  }

  const raceRow = await findRaceInTableBySummary(page, summary, {
    path: './index.php?id=400&subid=4&fC=1',
  });

  if (!raceRow || !raceRow.raceId) {
    throw new Error(`Could not find local race row for ORIS race ${orisId}`);
  }

  return {
    ...summary,
    id: raceRow.raceId,
    created: !summary.alreadyExists,
  };
}

async function getRaceRegistrationRow(page, reg, options = {}) {
  if (options.path) {
    await page.goto(options.path);
  }

  return page.evaluate((formattedReg) => {
    const rows = Array.from(document.querySelectorAll('tr'));

    function getInputValue(row, prefix) {
      const input = row.querySelector(`input[name^="${prefix}["]`);
      return input ? input.value.trim() : null;
    }

    function getCheckboxValue(row, prefix) {
      const input = row.querySelector(`input[type="checkbox"][name^="${prefix}["]`);
      return input ? input.checked : null;
    }

    for (const row of rows) {
      const cells = Array.from(row.querySelectorAll('td'));
      if (cells.length < 6) {
        continue;
      }

      if (cells[1].textContent.trim() !== formattedReg) {
        continue;
      }

      const categoryInput = row.querySelector('input[name^="kateg["]');
      const userIdMatch = categoryInput ? categoryInput.name.match(/\[(\d+)\]/) : null;

      return {
        reg: formattedReg,
        userId: userIdMatch ? userIdMatch[1] : null,
        category: getInputValue(row, 'kateg'),
        transport: getCheckboxValue(row, 'transport'),
        accommodation: getCheckboxValue(row, 'ubytovani'),
        term: getInputValue(row, 'term'),
      };
    }

    return null;
  }, formatClubReg(reg));
}

async function ensureRaceParticipants(page, raceId, participants, options = {}) {
  const groupId = options.groupId || 400;
  const registrationPath = `./race_regs_all.php?gr_id=${groupId}&id=${raceId}`;

  await page.goto(registrationPath);

  const postFields = {};

  for (const [reg, participant] of Object.entries(participants)) {
    const row = await getRaceRegistrationRow(page, reg);

    if (!row || !row.userId) {
      throw new Error(`Could not find race registration row for reg ${formatClubReg(reg)} on race ${raceId}`);
    }

    postFields[`kateg[${row.userId}]`] = String(participant.kateg);
    postFields[`pozn[${row.userId}]`] = participant.note || '';
    postFields[`pozn2[${row.userId}]`] = participant.noteInternal || '';

    if (participant.transport) {
      postFields[`transport[${row.userId}]`] = '1';
    }

    if (participant.ubytovani) {
      postFields[`ubytovani[${row.userId}]`] = '1';
    }

    const effectiveTerm = participant.term ?? row.term;
    if (effectiveTerm !== undefined && effectiveTerm !== null) {
      postFields[`term[${row.userId}]`] = String(effectiveTerm);
    }
  }

  const result = await postFormInSession(
    page,
    `./race_regs_all_exc.php?gr_id=${groupId}&id=${raceId}`,
    postFields
  );

  ensureHtmlSubmission(result, `Ensure race participants for race ${raceId}`);

  const verifiedParticipants = {};

  for (const [reg, participant] of Object.entries(participants)) {
    const row = await getRaceRegistrationRow(page, reg, {
      path: reg === Object.keys(participants)[0] ? registrationPath : undefined,
    });

    if (!row) {
      throw new Error(`Could not reload race registration row for reg ${formatClubReg(reg)} on race ${raceId}`);
    }

    expect(row.category).toBe(String(participant.kateg));

    if (participant.transport !== undefined) {
      expect(Boolean(row.transport)).toBe(Boolean(participant.transport));
    }

    if (participant.ubytovani !== undefined) {
      expect(Boolean(row.accommodation)).toBe(Boolean(participant.ubytovani));
    }

    if (participant.term !== undefined && row.term !== null) {
      expect(row.term).toBe(String(participant.term));
    }

    verifiedParticipants[reg] = row;
  }

  return verifiedParticipants;
}

module.exports = {
  ensureOrisRace,
  ensureRaceParticipants,
};
