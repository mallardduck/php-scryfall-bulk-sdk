<?php

use Mallardduck\ScryfallBulkSdk\BulkFileType;
use Mallardduck\ScryfallBulkSdk\Config\Config;
use Mallardduck\ScryfallBulkSdk\Config\Env;

beforeEach(function () {
    $instance = Env::getInstance();
    // Ensure Env singleton is reset before each test
    $reflection = new ReflectionClass(Env::class);
    $instance = $reflection->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue($instance, null);

    $putenv = $reflection->getProperty('putenv');
    $putenv->setAccessible(true);
    $putenv->setValue($instance, true);

    $repository = $reflection->getProperty('repository');
    $repository->setAccessible(true);
    $repository->setValue($instance, null);
});

it('loads default config values when no env variables are set', function () {
    $config = new Config();

    expect($config->get('bulkFilePath'))->toBe('default_value');
    expect($config->get('bulkFileType'))->toBe(BulkFileType::OracleCards);
});

it('loads config values from environment variables', function () {
    Env::getRepository()->set('SBS_BULK_FILE_PATH', '/path/to/bulk/files');
    Env::getRepository()->set('SBS_BULK_FILE_TYPE', BulkFileType::UniqueArtwork->value);

    $config = new Config();

    expect($config->get('bulkFilePath'))->toBe('/path/to/bulk/files');
    expect($config->get('bulkFileType'))->toBe(BulkFileType::UniqueArtwork->value);
});

it('returns the default value for a non-existent config key', function () {
    $config = new Config();

    expect($config->get('nonExistentKey', 'default_value'))->toBe('default_value');
});

it('returns null for a non-existent config key without default', function () {
    $config = new Config();

    expect($config->get('nonExistentKey'))->toBeNull();
});