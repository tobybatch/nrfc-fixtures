import { test, expect } from '@playwright/test';

test.describe('Fixtures', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should display fixture page', async ({ page }) => {
    await expect(page).toHaveTitle(/Fixtures/);
    await expect(page.locator('h1')).toContainText('Fixtures');
  });

  test('should display fixture row header', async ({ page }) => {
    await expect(page.locator('table thead tr th')).toHaveText([
      'Date', 'Minis', 'U13B', 'U14B', 'U15B', 'U16B', 'U18B', 'U12G', 'U14G', 'U16G', 'U18G'
    ]);
  });

  test('Row date is a link to byDate/xxx', async ({ page }) => {
    const firstRow = page.locator('tbody tr:first-child');

    await expect(firstRow.locator('td a').first()).toHaveText(/\w+/);
    await expect(firstRow.locator('td a').first()).toHaveAttribute('href', /\/byDate\/\d/);
  });

  test('Cell entries link to a /fixture/xxx page', async ({ page }) => {
    const firstRow = page.locator('tbody tr:first-child');
    const link = firstRow.locator('td:nth-child(2) a'); // Target link in 2nd cell

    await expect(link).toHaveText(/\w+/);
    await expect(link).toHaveAttribute('href', /\/\d/);
  });
}); 