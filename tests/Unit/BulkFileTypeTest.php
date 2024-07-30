<?php

use Mallardduck\ScryfallBulkSdk\BulkFileType;

it('returns the correct slug for OracleCards', function () {
    $enum = BulkFileType::OracleCards;
    expect($enum->slug())->toBe('oracle-cards');
});

it('returns the correct slug for UniqueArtwork', function () {
    $enum = BulkFileType::UniqueArtwork;
    expect($enum->slug())->toBe('unique-artwork');
});

it('returns the correct slug for DefaultCards', function () {
    $enum = BulkFileType::DefaultCards;
    expect($enum->slug())->toBe('default-cards');
});

it('returns the correct slug for AllCards', function () {
    $enum = BulkFileType::AllCards;
    expect($enum->slug())->toBe('all-cards');
});

it('returns the correct slug for Rulings', function () {
    $enum = BulkFileType::Rulings;
    expect($enum->slug())->toBe('rulings');
});