<?php
class Cache
{
    private Predis\Client $client;

    function __construct($client)
    {
        $this->client = $client;
    }

    public function get($key)
    {
        return $this->client->get($key);
    }

    public function set($key, $val, int $ttl = null)
    {
        if (isset($ttl)) {
            $this->client->setex($key, $ttl, $val);
        } else {
            $this->client->set($key, $val);
        }
    }

    public function ttl($key)
    {
        return $this->client->ttl($key);
    }
}
