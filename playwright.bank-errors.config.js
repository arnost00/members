const { defineConfig } = require('@playwright/test');

const baseConfig = require('./playwright.config');
const projects = baseConfig.projects.map((project) => (
  project.name === 'chromium'
    ? { ...project, testIgnore: '**/member-7203.setup.js' }
    : project
));

module.exports = defineConfig({
  ...baseConfig,
  // This suite changes shared bank-mock settings, so its tests must not overlap.
  workers: 1,
  testIgnore: undefined,
  testMatch: ['**/bank-connector-errors.spec.js'],
  projects,
});
