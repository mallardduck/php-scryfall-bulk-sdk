<?php

use Mallardduck\ScryfallBulkSdk\BulkFileType;
use Mallardduck\ScryfallBulkSdk\Config\Config;
use Mallardduck\ScryfallBulkSdk\Config\DefaultBulkPathSelector;
use Mallardduck\ScryfallBulkSdk\Config\Env;

beforeEach(function () {
    // Ensure Env singleton is reset before each test
    $reflection = new ReflectionClass(Env::class);
    $reflection->setStaticPropertyValue('putenv', true);
    $reflection->setStaticPropertyValue('repository',null);

    // Reset Config singleton instance
    $configReflection = new ReflectionClass(Config::class);
    $configReflection->setStaticPropertyValue('instance', null);
});

it('loads default config values when no env variables are set', function () {
    $config = Config::getInstance();

    expect($config->get(Config::BULK_FILE_PATH_KEY))->toBe(null);
    expect($config->get(Config::BULK_FILE_TYPE_KEY))->toBe(BulkFileType::OracleCards->value);
    expect($config->get(Config::USER_AGENT_KEY))->toBe("PHP Scryfall Bulk SDK");
});

it('loads config values from environment variables', function () {
    Env::getRepository()->set('SBS_BULK_FILE_PATH', '/custom/bulk/path');
    Env::getRepository()->set('SBS_BULK_FILE_TYPE', BulkFileType::UniqueArtwork->value);
    Env::getRepository()->set('SBS_USER_AGENT', 'Custom User Agent');

    $config = Config::getInstance();

    expect($config->get(Config::BULK_FILE_PATH_KEY))->toBe('/custom/bulk/path');
    expect($config->get(Config::BULK_FILE_TYPE_KEY))->toBe(BulkFileType::UniqueArtwork->value);
    expect($config->get(Config::USER_AGENT_KEY))->toBe('Custom User Agent');
});

it('returns the default value for a non-existent config key', function () {
    $config = Config::getInstance();

    expect($config->get('nonExistentKey', 'default_value'))->toBe('default_value');
});

it('returns null for a non-existent config key without default', function () {
    $config = Config::getInstance();

    expect($config->get('nonExistentKey'))->toBeNull();
});

it('throws when calling Config waking', function () {
    $config = Config::getInstance();
    $serialized = serialize($config);
    $reConfig = unserialize($serialized);
})->throws(Exception::class, "Cannot unserialize a singleton.");

it('actually clears singleton instance when called', function () {
    $config = Config::getInstance();
    $reflection = new ReflectionClass(Config::class);
    expect($reflection->getStaticPropertyValue('instance'))->toBeInstanceOf(Config::class);
    Config::clearInstance();
    expect($reflection->getStaticPropertyValue('instance'))->toBeNull();
});

it('throws on clone too', function () {
    $config = Config::getInstance();
    $newConfig = clone $config;
})->throws(Error::class, 'Call to private Mallardduck\ScryfallBulkSdk\Config\Config::__clone() from scope P\Tests\Unit\Config\ConfigTest');