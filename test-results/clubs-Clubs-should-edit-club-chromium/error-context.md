# Test info

- Name: Clubs >> should edit club
- Location: /home/tobias/usr/nrfc/nrfc-fixtures/tests/playwright/clubs.spec.ts:47:7

# Error details

```
Error: page.fill: Test ended.
Call log:
  - waiting for locator('input[name="club[name]"]')

    at /home/tobias/usr/nrfc/nrfc-fixtures/tests/playwright/clubs.spec.ts:51:16
```

# Test source

```ts
   1 | import { test, expect } from '@playwright/test';
   2 |
   3 | test.describe('Clubs', () => {
   4 |   test.beforeEach(async ({ page }) => {
   5 |     await page.goto('/clubs');
   6 |   });
   7 |
   8 |   test('should display clubs page', async ({ page }) => {
   9 |     await expect(page).toHaveTitle(/Clubs/);
  10 |     await expect(page.locator('h1')).toContainText('Clubs');
  11 |   });
  12 |
  13 |   test('should create new club', async ({ page }) => {
  14 |     await page.click('text=New Club');
  15 |     
  16 |     // Fill in the form
  17 |     await page.fill('input[name="club[name]"]', 'Test Club');
  18 |     await page.fill('input[name="club[address]"]', '123 Test Street');
  19 |     await page.fill('input[name="club[postcode]"]', 'AB12 3CD');
  20 |     await page.fill('input[name="club[latitude]"]', '51.5074');
  21 |     await page.fill('input[name="club[longitude]"]', '-0.1278');
  22 |     
  23 |     // Submit the form
  24 |     await page.click('button[type="submit"]');
  25 |     
  26 |     // Verify redirection and success message
  27 |     await expect(page).toHaveURL(/\/clubs\/\d+/);
  28 |     await expect(page.locator('.alert-success')).toContainText('Club created successfully');
  29 |   });
  30 |
  31 |   test('should display club details with map', async ({ page }) => {
  32 |     // Navigate to a club with coordinates
  33 |     await page.goto('/clubs/1');
  34 |     
  35 |     await expect(page.locator('h1')).toContainText('Test Club');
  36 |     await expect(page.locator('#map')).toBeVisible();
  37 |   });
  38 |
  39 |   test('should display club details without map', async ({ page }) => {
  40 |     // Navigate to a club without coordinates
  41 |     await page.goto('/clubs/2');
  42 |     
  43 |     await expect(page.locator('h1')).toContainText('Test Club 2');
  44 |     await expect(page.locator('#map')).not.toBeVisible();
  45 |   });
  46 |
  47 |   test('should edit club', async ({ page }) => {
  48 |     await page.goto('/clubs/1/edit');
  49 |     
  50 |     // Update the form
> 51 |     await page.fill('input[name="club[name]"]', 'Updated Club Name');
     |                ^ Error: page.fill: Test ended.
  52 |     await page.click('button[type="submit"]');
  53 |     
  54 |     // Verify redirection and success message
  55 |     await expect(page).toHaveURL(/\/clubs\/\d+/);
  56 |     await expect(page.locator('.alert-success')).toContainText('Club updated successfully');
  57 |     await expect(page.locator('h1')).toContainText('Updated Club Name');
  58 |   });
  59 | }); 
```