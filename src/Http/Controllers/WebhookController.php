<?php

namespace Webkul\Google\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Google\Models\Synchronization;

class WebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->header('x-goog-resource-state') !== 'exists') {
            return;
        }

        Synchronization::query()
            ->where('id', $request->header('x-goog-channel-id'))
            ->where('resource_id', $request->header('x-goog-resource-id'))
            ->firstOrFail()
            ->ping();
    }
}
