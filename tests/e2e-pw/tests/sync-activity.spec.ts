import { test, expect } from "../setup";
import { createLead, openLeadByTitle } from "../utils/faker";

/**
 * Google Integration — REAL end-to-end flow (live Google account).
 *
 * Unlike activity-meet.spec.ts (which stubs the Meet endpoint), this spec drives
 * the genuine server-side paths against the connected Google account that
 * global-setup leaves untouched:
 *
 *   1. Calendar  — select the synced calendar and "Save and Sync" (real ping).
 *   2. Lead      — create a lead via the UI.
 *   3. Meeting   — add a Meeting activity, mint a REAL Google Meet link, save,
 *                  and confirm it lands in the lead's activity timeline.
 *   4. Lunch     — add a Lunch activity, save, and confirm it shows too.
 *
 * REQUIRES a real connected account (valid refreshable OAuth token) for
 * admin@example.com with the calendar + meet scopes. These tests hit the live
 * Google API: they are slower, create real calendar events, and depend on
 * Google availability — run them deliberately, not as a fast smoke check.
 */

const MEET_URL_RE = /^https:\/\/meet\.google\.com\//;

/** One lead shared by the activity tests, created once. */
let leadTitle: string;

/**
 * A unique far-future schedule window per call. The meeting-overlap check
 * (ActivityRepository::isDurationOverlapping) rejects a meeting whose window
 * overlaps ANY existing activity, so reusing a fixed time would collide with
 * activities left behind by earlier runs. Spreading across years keeps every
 * run (and the meeting vs. lunch within a run) on its own slot.
 */
function uniqueSchedule(): { from: string; to: string } {
    const offsetMinutes = Date.now() % (5 * 365 * 24 * 60);
    const start = new Date(2035, 0, 1, 0, 0, 0);
    start.setMinutes(start.getMinutes() + offsetMinutes);
    const end = new Date(start.getTime() + 60 * 60 * 1000);

    const fmt = (d: Date) =>
        `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")} ` +
        `${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}:00`;

    return { from: fmt(start), to: fmt(end) };
}

/**
 * Open the activity modal on the shared lead and switch it to the given type.
 * Returns once the type's form is ready (title + schedule filled).
 *
 * The activity button is server-rendered before its Vue handler mounts, so a
 * too-early click is a silent no-op — retry until the modal actually opens.
 */
async function openActivityModal(adminPage, typeLabel: string, activityTitle: string) {
    await openLeadByTitle(adminPage, leadTitle);

    const activityButton = adminPage.getByRole("button", { name: " Activity" });
    await expect(activityButton).toBeVisible();

    const heading = adminPage.getByRole("heading", { name: "Add Activity" });

    await expect(async () => {
        await activityButton.click();
        await expect(heading).toBeVisible({ timeout: 3000 });
    }).toPass({ timeout: 30000 });

    await heading.locator("span").click();
    const option = adminPage.getByText(typeLabel, { exact: true });
    await expect(option).toBeVisible();
    await option.click();

    const schedule = uniqueSchedule();

    await adminPage.locator('input[name="title"]').fill(activityTitle);
    await adminPage.locator('input[name="schedule_from"]').fill(schedule.from);
    await adminPage.locator('input[name="schedule_to"]').fill(schedule.to);
}

/**
 * Reload the lead view and assert the activity title appears in the timeline.
 */
async function expectInTimeline(adminPage, activityTitle: string) {
    await openLeadByTitle(adminPage, leadTitle);
    await expect(adminPage.getByText(activityTitle).first()).toBeVisible();
}

test.describe.configure({ mode: "serial" });

test.describe("google integration - real sync & activity flow", () => {
    // These tests need a real connected Google account (valid refreshable token)
    // with calendar + meet scopes. Without one (e.g. CI, or only the stand-in
    // seed), the live sync/Meet calls fail with opaque auth errors — so skip
    // unless explicitly enabled. Run with: GOOGLE_LIVE=1 npx playwright test
    test.skip(
        !process.env.GOOGLE_LIVE,
        "Requires a live connected Google account. Set GOOGLE_LIVE=1 to run."
    );

    test.beforeAll(async ({ browser }) => {
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

    test("syncs the primary Google Calendar", async ({ adminPage }) => {
        // A real sync can pull a calendar's events into the CRM — allow time.
        test.setTimeout(180_000);

        await adminPage.goto("admin/google?route=calendar");

        await expect(adminPage.getByText("Synced Account")).toBeVisible();

        const select = adminPage.locator('select[name="calendar_id"]');
        await expect(select).toBeAttached();
        // Pick the primary calendar (first real option).
        await select.selectOption({ index: 0 });

        await adminPage.getByRole("button", { name: "Save and Sync" }).click();

        // Controller flashes google::app.account-synced and redirects back.
        // Scope to the flash <p> so it doesn't also match the phpdebugbar
        // sf-dump <span> that echoes the flashed session payload.
        await expect(
            adminPage.locator("p", { hasText: "Account synced successfully." })
        ).toBeVisible({ timeout: 120_000 });
    });

    test("adds a Meeting activity with a real Google Meet link and shows it in the timeline", async ({ adminPage }) => {
        test.setTimeout(120_000);

        const meetingTitle = `PW-Meeting-${Date.now()}`;

        await openActivityModal(adminPage, "Meeting", meetingTitle);

        // Mint a REAL Meet link (server inserts + deletes a temp Google event).
        await adminPage.getByRole("button", { name: "Google Meet", exact: true }).click();

        await expect(adminPage.locator('input[name="location"]')).toHaveValue(MEET_URL_RE, {
            timeout: 30_000,
        });
        await expect(adminPage.getByRole("link", { name: "Join Google Meet" })).toBeVisible();

        // Saving fires the listener, which creates a real Google Calendar event.
        await adminPage.getByRole("button", { name: "Save Activity" }).click();

        // Modal closes on success.
        await expect(adminPage.getByRole("heading", { name: "Add Activity" })).toBeHidden({
            timeout: 30_000,
        });

        await expectInTimeline(adminPage, meetingTitle);
    });

    test("adds a Lunch activity and shows it in the timeline", async ({ adminPage }) => {
        test.setTimeout(120_000);

        const lunchTitle = `PW-Lunch-${Date.now()}`;

        await openActivityModal(adminPage, "Lunch", lunchTitle);
        await adminPage.locator('input[name="location"]').fill("Cafe");

        await adminPage.getByRole("button", { name: "Save Activity" }).click();

        await expect(adminPage.getByRole("heading", { name: "Add Activity" })).toBeHidden({
            timeout: 30_000,
        });

        await expectInTimeline(adminPage, lunchTitle);
    });
});
