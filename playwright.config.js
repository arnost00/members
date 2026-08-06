const { defineConfig, devices } = require('@playwright/test');

const rawBaseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://web:10100/members/';
const baseURL = rawBaseURL.endsWith('/') ? rawBaseURL : `${rawBaseURL}/`;
const testSuite = process.env.MEMBERS_E2E_SUITE || '';
const noOrisSuite = testSuite === 'no-oris';
const noOrisKeySuite = testSuite === 'no-oris-key';
const alternateOrisSuite = noOrisSuite || noOrisKeySuite;
const applicationHeaders = alternateOrisSuite
  ? { 'X-Members-Autotest-Suite': testSuite }
  : undefined;
const regularTestIgnore = [
  '**/bank-connector-errors.spec.js',
  '**/oris-connector-errors.spec.js',
];
const alternateTestIgnore = [
  ...regularTestIgnore,
  '**/workflows/oris-*.spec.js',
];
const testIgnore = alternateOrisSuite ? alternateTestIgnore : regularTestIgnore;

module.exports = defineConfig({
  testDir: './tests/playwright',
  testIgnore,
  globalSetup: alternateOrisSuite ? './tests/playwright/setup/oris-suite.js' : undefined,
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
    extraHTTPHeaders: applicationHeaders,
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
        ...testIgnore,
        '**/member-7203.setup.js',
      ],
      dependencies: ['member-7203-setup'],
      use: {
        ...devices['Desktop Chrome'],
      },
    },
  ],
});
