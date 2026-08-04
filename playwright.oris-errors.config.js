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
  testIgnore: undefined,
  testMatch: ['**/oris-connector-errors.spec.js'],
  projects,
});
