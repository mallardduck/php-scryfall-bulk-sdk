<?php

namespace Mallardduck\ScryfallBulkSdk\Downloader;

use GuzzleHttp\Client;
use Mallardduck\ScryfallBulkSdk\Config\Config;

class ClientBuilder
{
    public function __invoke(): Client
    {
        return new Client([
            'headers' => [
                'Accept' => 'application/json;q=0.9,*/*;q=0.8',
                'User-Agent' => Config::getInstance()->get(Config::USER_AGENT_KEY)
            ]
        ]);
    }
}