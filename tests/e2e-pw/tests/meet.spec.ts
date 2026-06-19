import { test, expect } from "../setup";

/**
 * Google Meet tab — connected-account state.
 *
 * global-setup guarantees the admin account carries the `meet` scope, so the
 * Meet tab renders its connected management block (info + Remove) rather than
 * the "Connect Google Meet" CTA. Remove calls the live Google API, so we assert
 * its route wiring instead of submitting it.
 */
test.describe("google integration - meet (connected)", () => {
    test.beforeEach(async ({ adminPage }) => {
        await adminPage.goto("admin/google?route=meet");
    });

    test("renders the Google Meet page with its title and tabs", async ({ adminPage }) => {
        await expect(
            adminPage.locator("div.font-bold", { hasText: "Google Meet" }).first()
        ).toBeVisible();

        await expect(adminPage.getByRole("link", { name: "Google Calendar", exact: true })).toBeVisible();
        await expect(adminPage.getByRole("link", { name: "Google Meet", exact: true })).toBeVisible();
    });

    test("shows the connected Meet block instead of the connect CTA", async ({ adminPage }) => {
        await expect(
            adminPage.getByText("Google time management and scheduling meet for enhancing work speed")
        ).toBeVisible();

        await expect(adminPage.getByRole("link", { name: "Connect Google Meet" })).toHaveCount(0);
    });

    test("wires Remove to the account destroy route via DELETE", async ({ adminPage }) => {
        const removeForm = adminPage.locator('form:has(button:has-text("Remove"))').first();

        await expect(removeForm).toHaveAttribute("action", /\/admin\/google\/\d+$/);
        await expect(removeForm.locator('input[name="_method"]')).toHaveValue("DELETE");
        await expect(removeForm.locator('input[name="route"]')).toHaveValue("meet");
    });
});
