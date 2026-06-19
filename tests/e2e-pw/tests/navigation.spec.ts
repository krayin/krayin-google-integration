import { test, expect } from "../setup";

/**
 * Navigation tests for the Google Integration module.
 *
 * State-agnostic: these only assert routing, tab switching and the sidebar
 * entry, so they pass whether or not the account is connected. (Earlier these
 * keyed off the "Connect Google …" CTAs, which only render when DISCONNECTED;
 * global-setup now guarantees a connected account, so we anchor on the tab
 * headings instead.)
 */
test.describe("google integration - navigation", () => {
    test("redirects the bare /admin/google to the calendar tab", async ({ adminPage }) => {
        await adminPage.goto("admin/google");

        await expect(adminPage).toHaveURL(/\/admin\/google\?route=calendar$/);
    });

    test("switches between the Calendar and Meet tabs", async ({ adminPage }) => {
        await adminPage.goto("admin/google?route=calendar");

        await expect(
            adminPage.locator("div.font-bold", { hasText: "Google Calendar" }).first()
        ).toBeVisible();

        // The two tabs are links whose exact text is the localized title.
        await adminPage.getByRole("link", { name: "Google Meet", exact: true }).click();
        await expect(adminPage).toHaveURL(/\/admin\/google\?route=meet$/);
        await expect(
            adminPage.locator("div.font-bold", { hasText: "Google Meet" }).first()
        ).toBeVisible();

        await adminPage.getByRole("link", { name: "Google Calendar", exact: true }).click();
        await expect(adminPage).toHaveURL(/\/admin\/google\?route=calendar$/);
        await expect(
            adminPage.locator("div.font-bold", { hasText: "Google Calendar" }).first()
        ).toBeVisible();
    });

    test("shows the Google entry in the admin sidebar menu", async ({ adminPage }) => {
        await adminPage.goto("admin/dashboard");

        // The menu item (config/menu.php -> route admin.google.index) is rendered
        // into the sidebar DOM even while the menu group is collapsed.
        await expect(
            adminPage.locator('a[href$="/admin/google"]').first()
        ).toBeAttached();
    });
});
