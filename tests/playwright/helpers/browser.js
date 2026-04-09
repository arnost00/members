const { TEST_USERS } = require('../constants/users');
const { login } = require('../components/login');

async function loginAs(page, role) {
  const user = TEST_USERS[role];

  if (!user) {
    throw new Error(`Unknown Playwright test role: ${role}`);
  }

  await login(page, user);
}

async function postFormInSession(page, action, fields = {}) {
  return page.evaluate(async ({ action, fields }) => {
    const params = new URLSearchParams();

    for (const [key, rawValue] of Object.entries(fields)) {
      if (rawValue === undefined || rawValue === null) {
        continue;
      }

      if (Array.isArray(rawValue)) {
        for (const item of rawValue) {
          if (item !== undefined && item !== null) {
            params.append(key, String(item));
          }
        }
        continue;
      }

      params.append(key, String(rawValue));
    }

    const response = await fetch(new URL(action, window.location.href).toString(), {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
      },
      body: params.toString(),
    });

    return {
      ok: response.ok,
      status: response.status,
      url: response.url,
      text: await response.text(),
    };
  }, { action, fields });
}

async function readFormState(page, selector = 'form') {
  const form = page.locator(selector);

  return form.evaluate((node) => {
    const fields = {};

    function setValue(name, value) {
      if (Object.prototype.hasOwnProperty.call(fields, name)) {
        if (Array.isArray(fields[name])) {
          fields[name].push(value);
        } else {
          fields[name] = [fields[name], value];
        }
        return;
      }

      fields[name] = value;
    }

    for (const element of Array.from(node.elements)) {
      if (!element.name || element.disabled) {
        continue;
      }

      if (['submit', 'button', 'reset', 'file'].includes(element.type)) {
        continue;
      }

      if ((element.type === 'checkbox' || element.type === 'radio') && !element.checked) {
        continue;
      }

      if (element.tagName === 'SELECT' && element.multiple) {
        for (const option of Array.from(element.selectedOptions)) {
          setValue(element.name, option.value);
        }
        continue;
      }

      setValue(element.name, element.value);
    }

    return {
      action: node.action,
      method: node.method || 'GET',
      fields,
    };
  });
}

async function firstCheckedValue(page, selector) {
  return page.locator(selector).first().getAttribute('value');
}

function ensureHtmlSubmission(result, label) {
  if (!result.ok) {
    throw new Error(`${label} failed with HTTP ${result.status}`);
  }

  if (/Nepodařilo se|Chyba při provádění dotazu|Fatal error|Warning:/i.test(result.text)) {
    throw new Error(`${label} returned an application error`);
  }

  return result;
}

module.exports = {
  ensureHtmlSubmission,
  firstCheckedValue,
  loginAs,
  postFormInSession,
  readFormState,
};
