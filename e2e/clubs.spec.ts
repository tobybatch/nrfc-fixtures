import { test, expect } from '@playwright/test';

test.describe('Clubs', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/clubs');
  });

  test('should display clubs page', async ({ page }) => {
    await expect(page).toHaveTitle(/Clubs/);
    await expect(page.locator('h1')).toContainText('Clubs');
  });

  test('should create new club', async ({ page }) => {
    await page.click('text=New Club');
    
    // Fill in the form
    await page.fill('input[name="club[name]"]', 'Test Club');
    await page.fill('input[name="club[address]"]', '123 Test Street');
    await page.fill('input[name="club[postcode]"]', 'AB12 3CD');
    await page.fill('input[name="club[latitude]"]', '51.5074');
    await page.fill('input[name="club[longitude]"]', '-0.1278');
    
    // Submit the form
    await page.click('button[type="submit"]');
    
    // Verify redirection and success message
    await expect(page).toHaveURL(/\/clubs\/\d+/);
    await expect(page.locator('.alert-success')).toContainText('Club created successfully');
  });

  test('should display club details with map', async ({ page }) => {
    // Navigate to a club with coordinates
    await page.goto('/clubs/1');
    
    await expect(page.locator('h1')).toContainText('Test Club');
    await expect(page.locator('#map')).toBeVisible();
  });

  test('should display club details without map', async ({ page }) => {
    // Navigate to a club without coordinates
    await page.goto('/clubs/2');
    
    await expect(page.locator('h1')).toContainText('Test Club 2');
    await expect(page.locator('#map')).not.toBeVisible();
  });

  test('should edit club', async ({ page }) => {
    await page.goto('/clubs/1/edit');
    
    // Update the form
    await page.fill('input[name="club[name]"]', 'Updated Club Name');
    await page.click('button[type="submit"]');
    
    // Verify redirection and success message
    await expect(page).toHaveURL(/\/clubs\/\d+/);
    await expect(page.locator('.alert-success')).toContainText('Club updated successfully');
    await expect(page.locator('h1')).toContainText('Updated Club Name');
  });
}); 