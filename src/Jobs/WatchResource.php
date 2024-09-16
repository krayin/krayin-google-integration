<?php

namespace Webkul\Google\Jobs;

abstract class WatchResource
{
    protected $synchronizable;

    /**
     * Create a new job instance.
     *
     * @param mixed $synchronizable
     * 
     * @return void
     */
    public function __construct($synchronizable)
    {
        $this->synchronizable = $synchronizable;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $synchronization = $this->synchronizable->synchronization;

        try {
            $response = $this->getGoogleRequest(
                $this->synchronizable->getGoogleService('Calendar'),
                $synchronization->asGoogleChannel()
            );

            $synchronization->update([
                'resource_id' => $response->getResourceId(),
                'expired_at'  => Carbon::createFromTimestampMs($response->getExpiration()),
            ]);
        } catch (\Google_Service_Exception $e) {
            // If we reach an error at this point, it is likely that
            // push notifications are not allowed for this resource.
            // Instead we will sync it manually at regular interval.
        }
    }

    /**
     * Get the google request.
     *
     * @param mixed $service
     * @param mixed $channel
     * @return mixed
     */
    abstract public function getGoogleRequest($service, $channel);
}
