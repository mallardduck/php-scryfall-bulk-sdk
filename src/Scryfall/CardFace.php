<?php

namespace Mallardduck\ScryfallBulkSdk\Scryfall;

use Mallardduck\ScryfallBulkSdk\Scryfall\CardValues\Color;

final class CardFace
{
    /**
     * @param null|Color[] $color_indicator
     * @param null|Color[] $colors
     */
    public function __construct(
        public readonly null|string $artist,
        public readonly null|string $artist_id, // Could be upgraded to UUID type?
        public readonly null|string $cmc,
        public readonly null|array $color_indicator,
        public readonly null|array $colors,
        public readonly null|string $defense,
        public readonly null|string $flavor_text,
        public readonly null|string $illustration_id, // Could be upgraded to UUID type?
        public readonly null|object|array $image_uris,
        public readonly null|string $layout,
        public readonly null|string $loyalty,
        public readonly string $mana_cost,
        public readonly string $name,
        public readonly string $object,
        public readonly null|string $oracle_id,
        public readonly null|string $oracle_text,
        public readonly null|string $power,
        public readonly null|string $printed_name,
        public readonly null|string $printed_text,
        public readonly null|string $printed_type_line,
        public readonly null|string $toughness,
        public readonly null|string $type_line,
        public readonly null|string $watermark,
    ) {}
}