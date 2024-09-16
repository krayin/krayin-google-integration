<?php

namespace Webkul\Google\Jobs;

abstract class SynchronizeResource
{
    /**
     * The synchronizable instance.
     */
    protected $synchronizable;

    /**
     * The synchronization instance.
     */
    protected $synchronization;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($synchronizable)
    {
        $this->synchronizable = $synchronizable;

        $this->synchronization = $synchronizable->synchronization;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pageToken = null;

        $syncToken = $this->synchronization->token;

        $service = $this->synchronizable->getGoogleService('Calendar');

        do {
            $tokens = compact('pageToken', 'syncToken');

            try {
                $list = $this->getGoogleRequest($service, $tokens);
            } catch (\Google_Service_Exception $e) {
                if ($e->getCode() === 410) {
                    $this->synchronization->update(['token' => null]);
                    $this->dropAllSyncedItems();

                    return $this->handle();
                }

                throw $e;
            }

            foreach ($list->getItems() as $item) {
                $this->syncItem($item);
            }

            $pageToken = $list->getNextPageToken();
        } while ($pageToken);

        $this->synchronization->update([
            'token'                => $list->getNextSyncToken(),
            'last_synchronized_at' => now(),
        ]);
    }

    /**
     * Get the Google request.
     *
     * @param  mixed  $service
     * @param  mixed  $options
     * @return mixed
     */
    abstract public function getGoogleRequest($service, $options);

    /**
     * Sync the item.
     *
     * @param  mixed  $item
     * @return mixed
     */
    abstract public function syncItem($item);

    /**
     * Drop all synced items.
     *
     * @return mixed
     */
    abstract public function dropAllSyncedItems();
}
