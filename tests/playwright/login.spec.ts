import { test, expect } from '@playwright/test';

test.describe('Login', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
  });

  test('should display login page', async ({ page }) => {
    await expect(page).toHaveTitle(/Login/);
    await expect(page.locator('h1')).toContainText('Login');
    await expect(page.locator('form')).toBeVisible();
  });

  test('should show error with invalid credentials', async ({ page }) => {
    await page.fill('input[name="email"]', 'invalid@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');
    
    await expect(page.locator('.alert-danger')).toContainText('Invalid credentials');
  });

  test('should request magic login link', async ({ page }) => {
    await page.click('text=Request Magic Login Link');
    await page.fill('input[name="email"]', 'user@example.com');
    await page.click('button[type="submit"]');
    
    await expect(page.locator('.alert-success')).toContainText('Check your email for a magic login link');
  });

  test('should navigate to registration page', async ({ page }) => {
    await page.click('text=Register');
    await expect(page).toHaveURL('/register');
    await expect(page.locator('h1')).toContainText('Register');
  });
}); 