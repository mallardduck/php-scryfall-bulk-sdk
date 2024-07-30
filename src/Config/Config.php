<?php

namespace Mallardduck\ScryfallBulkSdk\Config;

use Mallardduck\ScryfallBulkSdk\BulkFileType;

class Config
{
    protected Env $env;
    protected array $config = [];

    public function __construct($envFilePath = null)
    {
        $this->env = Env::getInstance();
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        // Load all the needed configs via ENV or set defaults...
        $this->config = [
            # TODO: find sane values...
            'bulkFilePath' => $this->env->get('SBS_BULK_FILE_PATH', 'default_value'),
            'bulkFileType' => $this->env->get('SBS_BULK_FILE_TYPE', BulkFileType::OracleCards),
        ];
    }

    public function get($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}