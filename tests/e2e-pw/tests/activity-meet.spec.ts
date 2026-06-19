import { test, expect } from "../setup";
import { createLead, openLeadByTitle } from "../utils/faker";

/**
 * Google Meet activity flow — connected-account state.
 *
 * Exercises the package's contribution to activity creation: the "Google Meet"
 * button injected into the activity modal (see Resources/views/leads/view/
 * activities/create.blade.php, wired at the
 * `...activity.form_controls.modal.content.controls.after` render event).
 *
 * The button POSTs to `admin/google/create-link`, which mints a real Meet link
 * via the live Google Calendar API server-side. Playwright cannot intercept a
 * server→Google call, so each test stubs that ONE browser request with
 * page.route() and asserts the resulting UI behaviour. global-setup guarantees
 * the admin account carries the `meet` scope so the connected button renders.
 *
 * OUT OF SCOPE (intentionally): the actual Meet-link minting and the
 * activity → google_events calendar sync that fires on save. Both run
 * server-side against the live API and are covered by the mocked PHPUnit/Pest
 * feature test (tests/Feature/GoogleIntegrationTest.php). Saving a `meeting`
 * activity with the connected stand-in account would 500 on the listener's
 * un-try/catch'd Google call, so these tests stop at a populated, ready-to-save
 * form.
 */

const CREATE_LINK_GLOB = "**/admin/google/create-link";

const STUB_LINK = "https://meet.google.com/abc-defg-hij";

const STUB_COMMENT =
    "──────────\n\nYou are invited to join Google Meet meeting.\n\n" +
    "Join the Google Meet meeting: " + STUB_LINK + "\n\n──────────";

/** Title of the lead created once for the whole file. */
let leadTitle: string;

/**
 * Stub the Meet-link endpoint so the button never reaches the live Google API.
 */
async function stubCreateLink(page, status: number, body: object) {
    await page.route(CREATE_LINK_GLOB, (route) =>
        route.fulfill({
            status,
            contentType: "application/json",
            body: JSON.stringify(body),
        })
    );
}

/**
 * Open the lead, launch the activity modal and switch it to the Meeting type
 * with the required fields filled — leaving it ready for the Meet button.
 */
async function openMeetingModal(adminPage) {
    await openLeadByTitle(adminPage, leadTitle);

    const activityButton = adminPage.getByRole("button", { name: " Activity" });
    await expect(activityButton).toBeVisible();

    // The activity button is server-rendered before its Vue click handler mounts,
    // so a too-early click is a silent no-op. Retry until the teleported modal
    // (heading reads "Add Activity - <Type>") actually opens.
    const heading = adminPage.getByRole("heading", { name: "Add Activity" });

    await expect(async () => {
        await activityButton.click();
        await expect(heading).toBeVisible({ timeout: 3000 });
    }).toPass({ timeout: 30000 });

    await heading.locator("span").click();

    const meetingOption = adminPage.getByText("Meeting", { exact: true });
    await expect(meetingOption).toBeVisible();
    await meetingOption.click();

    await adminPage.locator('input[name="title"]').fill(leadTitle);
    await adminPage.locator('input[name="schedule_from"]').fill("2030-01-01 10:00:00");
    await adminPage.locator('input[name="schedule_to"]').fill("2030-01-01 10:30:00");
}

test.describe.configure({ mode: "serial" });

test.describe("google integration - meet activity (connected)", () => {
    test.beforeAll(async ({ browser }) => {
        // beforeAll runs without the per-test adminPage fixture, so log in on a
        // throwaway context just to create the shared lead (persisted server-side).
        const context = await browser.newContext();
        const page = await context.newPage();

        await page.goto("admin/login");
        await page.fill('input[name="email"]', "admin@example.com");
        await page.fill('input[name="password"]', "admin123");
        await page.press('input[name="password"]', "Enter");
        await page.waitForURL("**/admin/dashboard");

        ({ leadTitle } = await createLead(page));

        await context.close();
    });

    test("renders the Create Google Meet button in the activity modal", async ({ adminPage }) => {
        await openMeetingModal(adminPage);

        // Connected state shows the create button (lang activity.google-meet),
        // never the disconnected "Connect Google Meet" CTA.
        await expect(
            adminPage.getByRole("button", { name: "Google Meet", exact: true })
        ).toBeVisible();

        await expect(
            adminPage.getByRole("link", { name: "Connect Google Meet" })
        ).toHaveCount(0);
    });

    test("populates the activity location and comment with the Meet link", async ({ adminPage }) => {
        await stubCreateLink(adminPage, 200, { link: STUB_LINK, comment: STUB_COMMENT });

        await openMeetingModal(adminPage);

        await adminPage.getByRole("button", { name: "Google Meet", exact: true }).click();

        await expect(adminPage.locator('input[name="location"]')).toHaveValue(STUB_LINK);
        await expect(adminPage.locator('textarea[name="comment"]')).toHaveValue(STUB_COMMENT);

        // The create button is swapped for the Join link pointing at the meet URL.
        const join = adminPage.getByRole("link", { name: "Join Google Meet" });
        await expect(join).toBeVisible();
        await expect(join).toHaveAttribute("href", STUB_LINK);

        await expect(
            adminPage.getByRole("button", { name: "Google Meet", exact: true })
        ).toHaveCount(0);
    });

    test("removes the Meet link via the confirm modal", async ({ adminPage }) => {
        await stubCreateLink(adminPage, 200, { link: STUB_LINK, comment: STUB_COMMENT });

        await openMeetingModal(adminPage);
        await adminPage.getByRole("button", { name: "Google Meet", exact: true }).click();
        await expect(adminPage.locator('input[name="location"]')).toHaveValue(STUB_LINK);

        // Remove (icon-delete span, titled "Remove Google Meet") -> confirm modal.
        await adminPage.locator('[title="Remove Google Meet"]').click();
        await adminPage.getByRole("button", { name: "Agree", exact: true }).click();

        await expect(adminPage.locator('input[name="location"]')).toHaveValue("");
        await expect(adminPage.locator('textarea[name="comment"]')).toHaveValue("");

        // Back to the create state.
        await expect(
            adminPage.getByRole("button", { name: "Google Meet", exact: true })
        ).toBeVisible();
    });

    test("surfaces an error flash when the endpoint reports no connected account", async ({ adminPage }) => {
        const message = "No connected Google account was found.";

        await stubCreateLink(adminPage, 404, { message });

        await openMeetingModal(adminPage);
        await adminPage.getByRole("button", { name: "Google Meet", exact: true }).click();

        // The Vue component pushes the response message through the flash emitter.
        await expect(adminPage.getByText(message)).toBeVisible();

        // The activity form is left untouched.
        await expect(adminPage.locator('input[name="location"]')).toHaveValue("");
    });
});
