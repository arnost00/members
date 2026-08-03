const { defineConfig, devices } = require('@playwright/test');

const rawBaseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://web:10100/members/';
const baseURL = rawBaseURL.endsWith('/') ? rawBaseURL : `${rawBaseURL}/`;

module.exports = defineConfig({
  testDir: './tests/playwright',
  testIgnore: ['**/bank-connector-errors.spec.js'],
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [
    ['list'],
    ['html', { open: 'never' }],
  ],
  use: {
    baseURL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'member-7203-setup',
      testMatch: '**/member-7203.setup.js',
    },
    {
      name: 'chromium',
      testIgnore: [
        '**/bank-connector-errors.spec.js',
        '**/member-7203.setup.js',
      ],
      dependencies: ['member-7203-setup'],
      use: {
        ...devices['Desktop Chrome'],
      },
    },
  ],
});
