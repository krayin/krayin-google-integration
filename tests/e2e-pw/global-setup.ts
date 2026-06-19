import { execFileSync } from "child_process";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * Repository root, five levels up:
 * tests/e2e-pw → tests → GoogleIntegration → Webkul → packages → root.
 */
const APP_ROOT = path.resolve(__dirname, "../../../../../");

/**
 * Ensure the admin test user has a CONNECTED Google account so the
 * connected-state specs (calendar/meet tabs) have something to render.
 *
 * This is deliberately NON-DESTRUCTIVE and idempotent:
 *   - if the admin already has a Google account (e.g. a real one linked
 *     locally), it is left completely untouched;
 *   - only when none exists (e.g. a fresh CI install) is a stand-in account
 *     plus one primary calendar inserted.
 *
 * Rows are written with raw queries on purpose: creating the Account via
 * Eloquent would fire the Synchronizable model hooks, which call the live
 * Google API (watch/sync) — impossible with a stand-in token.
 */
const SEED_PHP = `
$user = DB::table('users')->where('email', 'admin@example.com')->first();

if (! $user) {
    echo 'google-e2e: admin@example.com not found; skipped seeding';
} elseif (DB::table('google_accounts')->where('user_id', $user->id)->exists()) {
    echo 'google-e2e: admin already has a Google account; left untouched';
} else {
    $now = now();

    $accountId = DB::table('google_accounts')->insertGetId([
        'google_id'  => 'e2e-test-account',
        'name'       => 'e2e-test@example.com',
        'token'      => json_encode([
            'access_token' => 'e2e-test-token',
            'expires_in'   => 3600,
            'created'      => time(),
            'scope'        => 'https://www.googleapis.com/auth/calendar',
        ]),
        'scopes'     => json_encode(['calendar', 'meet']),
        'user_id'    => $user->id,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('google_calendars')->insert([
        'google_id'         => 'e2e-test@example.com',
        'name'              => 'e2e-test@example.com',
        'color'             => '#1a73e8',
        'timezone'          => 'UTC',
        'is_primary'        => 1,
        'google_account_id' => $accountId,
        'created_at'        => $now,
        'updated_at'        => $now,
    ]);

    echo 'google-e2e: seeded stand-in connected account #' . $accountId;
}
`;

export default async function globalSetup() {
    try {
        const out = execFileSync(
            "php",
            ["artisan", "tinker", "--execute", SEED_PHP],
            { cwd: APP_ROOT, encoding: "utf-8" }
        );

        console.log(out.trim());
    } catch (err: any) {
        // Don't hard-fail the run; connected specs will surface the problem
        // with a clearer assertion message if seeding didn't happen.
        console.warn(
            "google-e2e global-setup: could not seed connected account:",
            err?.message ?? err
        );
    }
}
