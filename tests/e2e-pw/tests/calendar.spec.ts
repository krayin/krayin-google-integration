import { test, expect } from "../setup";

/**
 * Google Calendar tab — connected-account state.
 *
 * global-setup guarantees the admin user has a connected Google account with
 * the `calendar` scope and at least one synced calendar, so the tab renders the
 * synced-account management form (not the "Connect" CTA).
 *
 * Save-and-Sync and Remove both call the live Google API server-side, so we
 * assert they are wired to the correct routes rather than submitting them
 * (submitting would create flaky dependencies on a live Google session).
 */
test.describe("google integration - calendar (connected)", () => {
    test.beforeEach(async ({ adminPage }) => {
        await adminPage.goto("admin/google?route=calendar");
    });

    test("renders the Google Calendar page with its title and tabs", async ({ adminPage }) => {
        await expect(
            adminPage.locator("div.font-bold", { hasText: "Google Calendar" }).first()
        ).toBeVisible();

        await expect(adminPage.getByRole("link", { name: "Google Calendar", exact: true })).toBeVisible();
        await expect(adminPage.getByRole("link", { name: "Google Meet", exact: true })).toBeVisible();
    });

    test("shows the synced account instead of the connect CTA", async ({ adminPage }) => {
        await expect(adminPage.getByText("Synced Account")).toBeVisible();

        const accountField = adminPage.locator('input[name="account_name"]');
        await expect(accountField).toBeVisible();
        await expect(accountField).not.toHaveValue("");

        // Connected state must NOT offer the connect call-to-action.
        await expect(adminPage.getByRole("link", { name: "Connect Google Calendar" })).toHaveCount(0);
    });

    test("lists at least one calendar to sync", async ({ adminPage }) => {
        await expect(adminPage.getByText("Select the calendar you want to sync")).toBeVisible();

        const select = adminPage.locator('select[name="calendar_id"]');
        await expect(select).toBeAttached();
        await expect(select.locator("option")).not.toHaveCount(0);
    });

    test("wires Save and Sync to the calendar sync route", async ({ adminPage }) => {
        await expect(adminPage.getByRole("button", { name: "Save and Sync" })).toBeVisible();

        const form = adminPage.locator('form:has(button:has-text("Save and Sync"))');
        await expect(form).toHaveAttribute("action", /\/admin\/google\/sync\/\d+$/);
    });

    test("wires Remove to the account destroy route via DELETE", async ({ adminPage }) => {
        const removeForm = adminPage.locator('form:has(button:has-text("Remove"))').first();

        await expect(removeForm).toHaveAttribute("action", /\/admin\/google\/\d+$/);
        await expect(removeForm.locator('input[name="_method"]')).toHaveValue("DELETE");

        // The hidden route input tells the controller which scope to drop.
        await expect(removeForm.locator('input[name="route"]')).toHaveValue("calendar");
    });
});
