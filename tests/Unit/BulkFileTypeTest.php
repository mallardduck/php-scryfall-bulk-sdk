<?php

use Mallardduck\ScryfallBulkSdk\BulkFileType;

it('returns the correct slug', function ($enum, $expectedSlug) {
    expect($enum->slug())->toBe($expectedSlug);
})->with([
    [BulkFileType::OracleCards, 'oracle-cards'],
    [BulkFileType::UniqueArtwork, 'unique-artwork'],
    [BulkFileType::DefaultCards, 'default-cards'],
    [BulkFileType::AllCards, 'all-cards'],
    [BulkFileType::Rulings, 'rulings']
]);
