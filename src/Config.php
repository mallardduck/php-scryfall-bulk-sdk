<?php

namespace Mallardduck\ScryfallBulkSdk;

use Mallardduck\ScryfallBulkSdk\Config\Env;

final class Config
{
    const BULK_FILE_PATH_KEY = 'bulk_file_path';
    const BULK_FILE_TYPE_KEY = 'bulk_file_type';
    const USER_AGENT_KEY = 'user_agent';

    protected array $config = [];

    protected function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        // Load all the needed configs via ENV or set defaults...
        $this->config = [
            # TODO: find sane values...
            self::BULK_FILE_PATH_KEY => Env::get('SBS_BULK_FILE_PATH', null),
            self::BULK_FILE_TYPE_KEY => Env::get('SBS_BULK_FILE_TYPE', BulkFileType::OracleCards->value),
            self::USER_AGENT_KEY => Env::get('SBS_USER_AGENT', "PHP Scryfall Bulk SDK"),
        ];
    }

    public function get($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    protected static ?self $instance =  null;

    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    final public static function getInstance(): Config
    {
        $calledClass = get_called_class();
        if (Config::$instance === null) {
            Config::$instance = new Config;
        }
        return Config::$instance;
    }

    final public static function clearInstance(): void
    {
        Config::$instance = null;
    }
}