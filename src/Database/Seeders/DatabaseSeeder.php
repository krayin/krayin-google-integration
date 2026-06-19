<?php

namespace Webkul\Google\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Webkul\Google\Models\AccountProxy;
use Webkul\Google\Models\CalendarProxy;

/**
 * Demo data seeder for the Google Integration module.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the Google Integration demo data.
     */
    public function run(): void
    {
        $user = DB::table('users')->where('email', 'admin@example.com')->first()
            ?? DB::table('users')->orderBy('id')->first();

        if (! $user) {
            $this->command?->warn('Google Integration demo seeder: no users found; skipped.');

            return;
        }

        if (DB::table('google_accounts')->where('user_id', $user->id)->exists()) {
            $this->command?->info('Google Integration demo seeder: user already has a Google account; skipped.');

            return;
        }

        DB::transaction(function () use ($user) {
            $now = Carbon::now();

            $accountId = $this->seedAccount($user, $now);

            [$primaryCalendarId] = $this->seedCalendars($accountId, $now);

            $this->seedSynchronizations($accountId, $primaryCalendarId, $now);

            $this->seedEventsWithActivities($user, $primaryCalendarId, $now);
        });

        $this->command?->info('Google Integration demo data seeded successfully.');
    }

    /**
     * Seed a connected (stand-in) Google account for the given user.
     */
    protected function seedAccount(object $user, Carbon $now): int
    {
        return DB::table('google_accounts')->insertGetId([
            'google_id'  => 'demo-google-account-001',
            'name'       => 'demo.crm@example.com',
            'token'      => json_encode([
                'access_token'  => 'demo-access-token',
                'refresh_token' => 'demo-refresh-token',
                'expires_in'    => 3600,
                'created'       => $now->timestamp,
                'scope'         => 'https://www.googleapis.com/auth/calendar',
            ]),
            'scopes'     => json_encode(['calendar', 'meet']),
            'user_id'    => $user->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Seed one primary and one secondary calendar for the account.
     *
     * @return array{0: int, 1: int}  [primaryCalendarId, secondaryCalendarId]
     */
    protected function seedCalendars(int $accountId, Carbon $now): array
    {
        $primaryCalendarId = DB::table('google_calendars')->insertGetId([
            'google_id'         => 'demo.crm@example.com',
            'name'              => 'Demo Primary Calendar',
            'color'             => '#4285F4',
            'timezone'          => 'UTC',
            'is_primary'        => 1,
            'google_account_id' => $accountId,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $secondaryCalendarId = DB::table('google_calendars')->insertGetId([
            'google_id'         => 'team-events@group.calendar.google.com',
            'name'              => 'Team Events',
            'color'             => '#0B8043',
            'timezone'          => 'UTC',
            'is_primary'        => 0,
            'google_account_id' => $accountId,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        return [$primaryCalendarId, $secondaryCalendarId];
    }

    /**
     * Seed the polymorphic synchronization records for the account and its
     * primary calendar. IDs are UUIDs (matching the Synchronization model).
     */
    protected function seedSynchronizations(int $accountId, int $primaryCalendarId, Carbon $now): void
    {
        DB::table('google_synchronizations')->insert([
            [
                'id'                   => Uuid::uuid4()->toString(),
                'synchronizable_type'  => AccountProxy::modelClass(),
                'synchronizable_id'    => $accountId,
                'token'                => null,
                'resource_id'          => null,
                'expired_at'           => null,
                'last_synchronized_at' => $now,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
            [
                'id'                   => Uuid::uuid4()->toString(),
                'synchronizable_type'  => CalendarProxy::modelClass(),
                'synchronizable_id'    => $primaryCalendarId,
                'token'                => null,
                'resource_id'          => null,
                'expired_at'           => null,
                'last_synchronized_at' => $now,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
        ]);
    }

    /**
     * Seed a few demo CRM activities and link them to the primary calendar as
     * synced Google events, so the connected calendar has visible entries.
     */
    protected function seedEventsWithActivities(object $user, int $primaryCalendarId, Carbon $now): void
    {
        $samples = [
            ['title' => 'Product demo with Acme Corp', 'type' => 'meeting', 'days' => 1, 'hour' => 10],
            ['title' => 'Discovery call with new lead',  'type' => 'call',    'days' => 2, 'hour' => 14],
            ['title' => 'Quarterly review lunch',        'type' => 'lunch',   'days' => 3, 'hour' => 13],
        ];

        foreach ($samples as $index => $sample) {
            $from = $now->copy()->addDays($sample['days'])->setTime($sample['hour'], 0);
            $to   = $from->copy()->addMinutes(30);

            $activityId = DB::table('activities')->insertGetId([
                'title'         => $sample['title'],
                'type'          => $sample['type'],
                'comment'       => 'Demo activity created by the Google Integration seeder.',
                'schedule_from' => $from,
                'schedule_to'   => $to,
                'is_done'       => 0,
                'user_id'       => $user->id,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            // google_events has no timestamps (see migration / Event model).
            DB::table('google_events')->insert([
                'google_id'          => 'demo-event-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'activity_id'        => $activityId,
                'google_calendar_id' => $primaryCalendarId,
            ]);
        }
    }
}
