import { test, expect } from "@playwright/test";

/**
 * Auth gate.
 *
 * Every Google route is wrapped in the `user` middleware (Routes/web.php), so
 * a guest hitting any of them must be bounced to the admin login. This uses the
 * plain (unauthenticated) `page` fixture rather than the adminPage fixture.
 */
test.describe("google integration - auth gate", () => {
    test("redirects unauthenticated users from /admin/google to admin login", async ({ page }) => {
        await page.goto("admin/google");

        await expect(page).toHaveURL(/\/admin\/login/);
    });

    test("redirects unauthenticated users from the OAuth route to admin login", async ({ page }) => {
        await page.goto("admin/google/oauth?route=calendar");

        await expect(page).toHaveURL(/\/admin\/login/);
    });
});
