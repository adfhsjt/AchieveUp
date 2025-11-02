// @ts-check
import { test, expect } from '@playwright/test';

test('has title', async ({ page }) => {
  await page.goto('https://achieveup-production-7ae9.up.railway.app');

  // Expect a title "to contain" a substring.
  await expect(page).toHaveTitle(/AchieveUp - Memberdayakan Masa Depan Melalui Pembelajaran/);
});

test('get started link', async ({ page }) => {
  await page.goto('https://achieveup-production-7ae9.up.railway.app');

  // Click the get started link.
  await page.getByRole('link', { name: 'Mulai Sekarang' }).click();

  // Expects page to have a heading with the name of Installation.
  await expect(page.getByRole('heading', { name: 'login' })).toBeVisible();
});
