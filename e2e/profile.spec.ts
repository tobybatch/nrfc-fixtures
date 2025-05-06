import { test, expect } from '@playwright/test';

test.describe('Profile', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/profile');
  });

  test('should display profile page', async ({ page }) => {
    await expect(page).toHaveTitle(/Profile/);
    await expect(page.locator('h1')).toContainText('Profile');
  });

  test('should update email', async ({ page }) => {
    const newEmail = 'newemail@example.com';
    
    await page.fill('input[name="profile[email]"]', newEmail);
    await page.fill('input[name="profile[currentPassword]"]', 'currentpassword');
    await page.click('button[type="submit"]');
    
    await expect(page.locator('.alert-success')).toContainText('Profile updated successfully');
    await expect(page.locator('input[name="profile[email]"]')).toHaveValue(newEmail);
  });

  test('should update password', async ({ page }) => {
    const newPassword = 'newpassword123';
    
    await page.fill('input[name="profile[currentPassword]"]', 'currentpassword');
    await page.fill('input[name="profile[plainPassword][first]"]', newPassword);
    await page.fill('input[name="profile[plainPassword][second]"]', newPassword);
    await page.click('button[type="submit"]');
    
    await expect(page.locator('.alert-success')).toContainText('Profile updated successfully');
  });

  test('should show error with incorrect current password', async ({ page }) => {
    await page.fill('input[name="profile[currentPassword]"]', 'wrongpassword');
    await page.fill('input[name="profile[plainPassword][first]"]', 'newpassword123');
    await page.fill('input[name="profile[plainPassword][second]"]', 'newpassword123');
    await page.click('button[type="submit"]');
    
    await expect(page.locator('.alert-danger')).toContainText('Invalid password');
  });

  test('should show error with mismatched passwords', async ({ page }) => {
    await page.fill('input[name="profile[currentPassword]"]', 'currentpassword');
    await page.fill('input[name="profile[plainPassword][first]"]', 'newpassword123');
    await page.fill('input[name="profile[plainPassword][second]"]', 'differentpassword');
    await page.click('button[type="submit"]');
    
    await expect(page.locator('.alert-danger')).toContainText('Passwords do not match');
  });
}); 