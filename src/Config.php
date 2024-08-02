<?php

namespace Mallardduck\ScryfallBulkSdk;

use Mallardduck\ScryfallBulkSdk\Config\Env;

final class Config
{
    const BULK_FILE_PATH_KEY = 'bulk_file_path';
    const BULK_FILE_TYPE_KEY = 'bulk_file_type';
    const USER_AGENT_KEY = 'user_agent';

    protected array $config = [];

    protected function __construct(array $config)
    {
        // Load all the needed configs via ENV or set defaults...
        $this->config = array_merge(
            [
                # TODO: find sane values...
                self::BULK_FILE_PATH_KEY => Env::get('SBS_BULK_FILE_PATH', null),
                self::BULK_FILE_TYPE_KEY => Env::get('SBS_BULK_FILE_TYPE', BulkFileType::OracleCards->value),
                self::USER_AGENT_KEY => Env::get('SBS_USER_AGENT', "PHP Scryfall Bulk SDK"),
            ],
            $config
        );
    }

    public function get($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    protected static ?self $instance =  null;
    private static ?string $instanceConfigInHash;

    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /**
     * Allow setting config at runtime where ENV pulled vars won't make sense.
     * Necessary for more dynamic configs provided by an app/framework/etc.
     *
     * @param array|null $config
     * @return Config
     */
    final public static function getInstance(?array $config = null): Config
    {
        $inHash = md5(serialize($config));
        if (Config::$instance !== null && Config::$instanceConfigInHash !== $inHash) {
            Config::$instanceConfigInHash = null;
            Config::$instance = null;
        }

        if (Config::$instance === null) {
            Config::$instanceConfigInHash = md5(serialize($config));
            Config::$instance = new Config($config ?? []);
        }
        return Config::$instance;
    }

    final public static function clearInstance(): void
    {
        Config::$instance = null;
    }
}