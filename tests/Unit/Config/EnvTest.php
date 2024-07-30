<?php

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

it('returns the singleton instance', function () {
    $instance1 = Env::getInstance();
    $instance2 = Env::getInstance();

    expect($instance1)->toBeInstanceOf(Env::class);
    expect($instance1)->toBe($instance2);
});

it('enables and disables putenv adapter', function () {
    Env::disablePutenv();
    $reflection = new ReflectionClass(Env::class);
    $putenv = $reflection->getProperty('putenv');
    $putenv->setAccessible(true);

    expect($putenv->getValue())->toBeFalse();

    Env::enablePutenv();

    expect($putenv->getValue())->toBeTrue();
});

it('gets the environment repository instance with putenv enabled', function () {
    Env::enablePutenv();
    $repository = Env::getRepository();

    expect($repository)->toBeInstanceOf(\Dotenv\Repository\RepositoryInterface::class);
});

it('gets the environment repository instance with putenv disabled', function () {
    Env::disablePutenv();
    $repository = Env::getRepository();

    expect($repository)->toBeInstanceOf(\Dotenv\Repository\RepositoryInterface::class);
});

it('gets the value of an environment variable', function () {
    Env::getRepository()->set('TEST_KEY', 'TEST_VALUE');
    $value = Env::get('TEST_KEY', 'DEFAULT_VALUE');

    expect($value)->toBe('TEST_VALUE');
});

it('gets the default value when environment variable does not exist', function () {
    $value = Env::get('NON_EXISTENT_KEY', 'DEFAULT_VALUE');

    expect($value)->toBe('DEFAULT_VALUE');
});

it('throws an exception when required environment variable is missing', function () {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Environment variable [NON_EXISTENT_KEY] has no value.');

    Env::getOrFail('NON_EXISTENT_KEY');
});

it('returns option value correctly', function () {
    Env::getRepository()->set('TEST_KEY_TRUE', 'true');
    Env::getRepository()->set('TEST_KEY_FALSE', 'false');
    Env::getRepository()->set('TEST_KEY_EMPTY', 'empty');
    Env::getRepository()->set('TEST_KEY_NULL', 'null');

    $trueValue = Env::get('TEST_KEY_TRUE');
    $falseValue = Env::get('TEST_KEY_FALSE');
    $emptyValue = Env::get('TEST_KEY_EMPTY');
    $nullValue = Env::get('TEST_KEY_NULL');

    expect($trueValue)->toBeTrue();
    expect($falseValue)->toBeFalse();
    expect($emptyValue)->toBe('');
    expect($nullValue)->toBeNull();
});