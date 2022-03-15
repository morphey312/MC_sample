<?php

namespace App\V1\Services;

use App\V1\Contracts\Services\ClientService as ClientServiceInterface;
use App\V1\Models\Company;
use Illuminate\Support\Arr;
use Auth;

class ClientService implements ClientServiceInterface
{
    /**
     * @var Company
     */
    protected $client = null;

    /**
     * @inheritdoc
     */
    public function getClientId()
    {
        return $this->resolveClient()->id;
    }

    /**
     * @inheritdoc
     */
    public function getClientAuthorityLevel()
    {
        return $this->resolveClient()->authority_level;
    }

    /**
     * @inheritdoc
     */
    public function getClientConfig($path, $default = null)
    {
        $clientConfig = (array) $this->resolveClient()->config;
        $value = Arr::get($clientConfig, $path, null);

        if ($value !== null) {
            return $value;
        }

        return config($path, $default);
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->resolveClient()->enabled;
    }

    /**
     * @inheritdoc
     */
    public function stickTo(Company $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function on(Company $client)
    {
        $instance = new static();
        $instance->stickTo($client);
        return $instance;
    }

    /**
     * Resolve current client
     * 
     * @return Company
     */
    protected function resolveClient()
    {
        if ($this->client === null) {
            $user = Auth::user();

            if ($user === null || $user->company === null) {
                $this->client = new Company();
                $this->client->authority_level = -1;
                $this->client->enabled = false;
                $this->client->config = [];
            } else {
                $this->client = $user->company;
            }
        }

        return $this->client;
    }
}
