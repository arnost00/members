const { defineConfig } = require('@playwright/test');

const baseConfig = require('./playwright.config');
const projects = baseConfig.projects
  .filter((project) => project.name === 'chromium')
  .map((project) => ({
    ...project,
    dependencies: [],
    testIgnore: '**/member-7203.setup.js',
  }));

module.exports = defineConfig({
  ...baseConfig,
  // This suite changes shared ORIS-mock settings, so its tests must not overlap.
  workers: 1,
  testIgnore: undefined,
  testMatch: ['**/oris-connector-errors.spec.js'],
  projects,
});
