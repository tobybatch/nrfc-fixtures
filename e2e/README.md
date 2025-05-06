# Playwright Tests

This directory contains end-to-end tests for the NRFC Fixtures application using Playwright.

## Prerequisites

- Node.js (v16 or later)
- npm or yarn
- PHP 8.1 or later
- Composer

## Installation

1. Install dependencies:
```bash
npm install
```

2. Install Playwright browsers:
```bash
npm run playwright:install
```

## Running Tests

### Run all tests
```bash
npm test
```

### Run tests in UI mode
```bash
npm run test:ui
```

### Run tests in debug mode
```bash
npm run test:debug
```

### Generate HTML report
```bash
npm run test:report
```

## Configuration

The Playwright configuration is in `playwright.config.ts`. Key settings:

- Test directory: `./tests/playwright`
- Base URL: `http://localhost:8000`
- Browsers: Chromium, Firefox, and WebKit
- HTML reporter enabled
- Screenshots on failure
- Trace on first retry

## Writing Tests

1. Create a new test file in the `tests/playwright` directory
2. Use the `test` and `expect` functions from `@playwright/test`
3. Follow the existing test patterns
4. Use meaningful test descriptions
5. Include proper error handling and assertions

## Best Practices

- Use `test.beforeEach` for common setup
- Use meaningful selectors (prefer text content over CSS selectors)
- Include proper error messages in assertions
- Keep tests independent and isolated
- Use proper waiting strategies
- Handle authentication state appropriately 