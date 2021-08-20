<?php

namespace Webkul\Google\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Webkul\Google\Models\Synchronization;

class RefreshWebhookSynchronizations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        Synchronization::query()
            ->whereNotNull('resource_id')
            ->whereNull('expired_at')
            ->orWhere('expired_at', '<', now()->addDays(2))
            ->get()
            ->each->refreshWebhook();
    }
}