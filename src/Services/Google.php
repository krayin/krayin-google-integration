<?php

namespace Webkul\Google\Services;

use Webkul\Google\Models\Calendar;
use Webkul\Google\Models\Account;

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
    function __construct()
    {
        $client = new \Google_Client();

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
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (! method_exists($this->client, $method)) {
            throw new \Exception("Call to undefined method '{$method}'");
        }

        return call_user_func_array([$this->client, $method], $args);
    }

    /**
     * @return mixed
     */
    public function service($service)
    {
        $className = "Google_Service_$service";

        return new $className($this->client);
    }

    /**
     * @param  string  $token
     * @return self
     */
    public function connectUsing($token)
    {
        $this->client->setAccessToken($token);

        return $this;
    }
    
    /**
     * @param  string  $token
     * @return self
     */
    public function revokeToken($token = null)
    {
        $token = $token ?? $this->client->getAccessToken();

        return $this->client->revokeToken($token);
    }

    public function connectWithSynchronizable($synchronizable)
    {
        $token = $this->getTokenFromSynchronizable($synchronizable);
        
        return $this->connectUsing($token);
    }

    protected function getTokenFromSynchronizable($synchronizable)
    {
        switch (true) {
            case $synchronizable instanceof Account:
                return $synchronizable->token;

            case $synchronizable instanceof Calendar:
                return $synchronizable->account->token;
            
            default:
                throw new \Exception("Invalid Synchronizable");
        }
    }
}