const { defineConfig } = require('@playwright/test');

const baseConfig = require('./playwright.config');

module.exports = defineConfig({
  ...baseConfig,
  testIgnore: undefined,
  testMatch: ['**/bank-connector-errors.spec.js'],
});
