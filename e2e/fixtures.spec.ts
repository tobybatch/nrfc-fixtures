import { test, expect } from '@playwright/test';

test.describe('Fixtures', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/fixtures');
  });

  test('should display fixtures page', async ({ page }) => {
    await expect(page).toHaveTitle(/Fixtures/);
    await expect(page.locator('h1')).toContainText('Fixtures');
  });

  test('should filter fixtures by team', async ({ page }) => {
    const teamSelect = page.locator('select[name="team"]');
    await teamSelect.selectOption('U13B');
    
    // Wait for the fixtures to update
    await page.waitForSelector('.fixture-card');
    
    // Verify that all displayed fixtures are for U13B
    const fixtures = await page.locator('.fixture-card').all();
    for (const fixture of fixtures) {
      await expect(fixture.locator('.team')).toContainText('U13B');
    }
  });

  test('should navigate to fixture details', async ({ page }) => {
    const firstFixture = page.locator('.fixture-card').first();
    const fixtureTitle = await firstFixture.locator('h3').textContent();
    
    await firstFixture.click();
    
    await expect(page).toHaveURL(/\/fixtures\/\d+/);
    await expect(page.locator('h1')).toContainText(fixtureTitle);
  });

  test('should create new fixture', async ({ page }) => {
    await page.click('text=New Fixture');
    
    // Fill in the form
    await page.fill('input[name="fixture[date]"]', '2024-03-15');
    await page.selectOption('select[name="fixture[homeAway]"]', 'home');
    await page.selectOption('select[name="fixture[competition]"]', 'league');
    await page.selectOption('select[name="fixture[team]"]', 'U13B');
    await page.selectOption('select[name="fixture[club]"]', '1');
    
    // Submit the form
    await page.click('button[type="submit"]');
    
    // Verify redirection and success message
    await expect(page).toHaveURL(/\/fixtures\/\d+/);
    await expect(page.locator('.alert-success')).toContainText('Fixture created successfully');
  });
}); 