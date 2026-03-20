import { test, expect } from '@playwright/test'

test('admin can view users list', async ({ page }) => {
    await page.goto('/login')
    await page.fill('[data-testid="login-email"]', 'admin@test.com')
    await page.fill('[data-testid="login-password"]', 'password')
    await page.click('[data-testid="login-form-submit"]')

    await page.goto('/management/users')
    await expect(page).toHaveURL(/\/management\/users$/)

    await expect(page.locator('[data-testid="users-table"]')).toBeVisible()
    await expect(page.locator('[data-testid="users-table-row"]').first()).toBeVisible()
})
