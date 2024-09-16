<?php

namespace Webkul\Google\Services;

use Webkul\Google\Models\Account;
use Webkul\Google\Models\Calendar;

class Google
{
    /**
     * Google Client object
     *
     * @var \Google_Client
     */
    protected $client;

    /**
     * Google service constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $client = new \Google_Client;

        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setScopes(config('services.google.scopes'));
        $client->setApprovalPrompt(config('services.google.approval_prompt'));
        $client->setAccessType(config('services.google.access_type'));
        $client->setIncludeGrantedScopes(config('services.google.include_granted_scopes'));

        $this->client = $client;
    }

    /**
     * Dynamically call methods on the Google client.
     */
    public function __call($method, $args): mixed
    {
        if (! method_exists($this->client, $method)) {
            throw new \Exception("Call to undefined method '{$method}'");
        }

        return call_user_func_array([$this->client, $method], $args);
    }

    /**
     * Create a new Google service instance.
     */
    public function service($service): mixed
    {
        $className = "Google_Service_$service";

        return new $className($this->client);
    }

    /**
     * Connect to Google using the given token.
     */
    public function connectUsing(string|array $token): self
    {
        $this->client->setAccessToken($token);

        return $this;
    }

    /**
     * Create a new Google service instance.
     */
    public function revokeToken(string|array|null $token = null): bool
    {
        $token = $token ?? $this->client->getAccessToken();

        return $this->client->revokeToken($token);
    }

    /**
     * Connect to Google using the given synchronizable.
     */
    public function connectWithSynchronizable(mixed $synchronizable): self
    {
        $token = $this->getTokenFromSynchronizable($synchronizable);

        return $this->connectUsing($token);
    }

    /**
     * Get the token from the given synchronizable.
     */
    protected function getTokenFromSynchronizable(mixed $synchronizable): mixed
    {
        switch (true) {
            case $synchronizable instanceof Account:
                return $synchronizable->token;

            case $synchronizable instanceof Calendar:
                return $synchronizable->account->token;

            default:
                throw new \Exception('Invalid Synchronizable');
        }
    }
}
