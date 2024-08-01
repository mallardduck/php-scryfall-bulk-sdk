<?php

it('returns a plain value', function ($value) {
    expect(value($value))->toBe($value);
})->with(['value', 'default', null, true, 1, 42, 32.5]);

it('calls the callable without args', function ($value, $expected) {
    expect(value($value))->toBe($expected);
})->with([
    [fn() => 42, 42],
    [fn() => 'hello', 'hello'],
    [fn() => '42', '42'],
]);

it('calls the callable with args', function ($value, $expectedGroups) {
    foreach ($expectedGroups as $expectedGroup) {
        [$args, $expected] = $expectedGroup;
        expect(value($value, $args))->toBe($expected);
    }
})->with([
    [
        fn($who) => 'hello ' . $who,
        [
            ['hello', 'hello hello'],
            ['world', 'hello world'],
            ['greg', 'hello greg'],
        ]
    ],
]);