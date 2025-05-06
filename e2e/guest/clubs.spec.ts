import { test, expect } from '@playwright/test';

test.describe('Clubs', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/club');
  });

  test('should display clubs page', async ({ page }) => {
    await expect(page).toHaveTitle(/Clubs/);
    await expect(page.locator('h1')).toContainText('Clubs');
  });

  test('should display clubs row header', async ({ page }) => {
    await expect(page.locator('table thead tr th')).toHaveText([
      'Name',
      'Address'
    ]);
  });

  test('club name is a like to club/xxx', async ({ page }) => {
    const firstRow = page.locator('tbody tr:first-child');

    await expect(firstRow.locator('td a').first()).toHaveText(/\w+/);
    await expect(firstRow.locator('td a').first()).toHaveAttribute('href', /\/club\/\d/);
  });

  test('club address has a google map link', async ({ page }) => {
    const firstRow = page.locator('tbody tr:first-child');
    const mapLink = firstRow.locator('td:nth-child(2) a'); // Target link in 2nd cell

    // Check the link has visible text (the ↗️ emoji)
    await expect(mapLink).toHaveText('↗️'); // or use .toContainText() if whitespace exists

    // Verify it's a Google Maps link with a valid query
    await expect(mapLink).toHaveAttribute('href', /www\.google\.com\/maps\/search\/\?api=1/);
    await expect(mapLink).toHaveAttribute('target', '_blank'); // Opens in new tab
  });
}); 