<?php

namespace Mallardduck\ScryfallBulkSdk\Scryfall;

use Carbon\Carbon;
use Mallardduck\ScryfallBulkSdk\Scryfall\CardValues\Color;

final class Card
{
    private static array $_dropFields = [
        'digital',
    ];

    /**
     * @param Color[] $color_identity
     * @param null|Color[] $color_indicator
     * @param null|Color[] $colors
     * @param null|Color[] $produced_mana
     * @param null|string[] $artist_ids
     */
    public function __construct(
        public readonly null|int $arena_id,
        public readonly string $id, // Could be upgraded to UUID type?
        public readonly string $lang,
        public readonly null|int $mtgo_id,
        public readonly null|int $mtgo_foil_id,
        public readonly null|array $multiverse_ids,
        public readonly null|int $tcgplayer_id,
        public readonly null|int $tcgplayer_etched_id,
        public readonly null|int $cardmarket_id,
        public readonly string $object,
        public readonly string $layout,
        public readonly null|string $oracle_id, // Could be upgraded to UUID type?
        public readonly string $prints_search_uri,
        public readonly string $rulings_uri,
        public readonly string $scryfall_uri,
        public readonly string $uri,
        public readonly null|array $all_parts,
        public readonly null|array $card_faces,
        public readonly null|int|float $cmc,
        public readonly array $color_identity,
        public readonly null|array $color_indicator,
        public readonly null|array $colors,
        public readonly null|string $defense,
        public readonly null|int $edhrec_rank,
        public readonly null|string $hand_modifier,
        public readonly array $keywords,
        public readonly object|array $legalities,
        public readonly null|string $life_modifier,
        public readonly null|string $loyalty,
        public readonly null|string $mana_cost,
        public readonly string $name,
        public readonly null|string $oracle_text,
        public readonly null|int $penny_rank,
        public readonly null|string $power,
        public readonly null|array $produced_mana,
        public readonly bool $reserved,
        public readonly null|string $toughness,
        public readonly null|string $type_line,
        public readonly null|string $artist,
        public readonly null|array $artist_ids, // Could be upgraded to UUID type?
        public readonly null|array $attraction_lights,
        public readonly bool $booster,
        public readonly string $border_color,
        public readonly null|string $card_back_id, // Could be upgraded to UUID type? // SCRYFALL BUG: they didn't mark this shit nullable
        public readonly null|string $collector_number,
        public readonly null|bool $content_warning,
        // public readonly bool $digital,
        public readonly array $finishes,
        public readonly null|string $flavor_name,
        public readonly null|string $flavor_text,
        public readonly null|array $frame_effects,
        public readonly string $frame,
        public readonly bool $full_art,
        public readonly array $games,
        public readonly bool $highres_image,
        public readonly null|string $illustration_id, // Could be upgraded to UUID type?
        public readonly string $image_status,
        public readonly null|object|array $image_uris,
        public readonly bool $oversized,
        public readonly object|array $prices,
        public readonly null|string $printed_name,
        public readonly null|string $printed_text,
        public readonly null|string $printed_type_line,
        public readonly bool $promo,
        public readonly null|array $promo_types,
        public readonly null|object|array $purchase_uris,
        public readonly string $rarity,
        public readonly object|array $related_uris,
        public readonly Carbon $released_at,
        public readonly bool $reprint,
        public readonly string $scryfall_set_uri,
        public readonly string $set_name,
        public readonly string $set_search_uri,
        public readonly string $set_type,
        public readonly string $set_uri,
        public readonly string $set,
        public readonly null|string $set_id, // Could be upgraded to UUID type?
        public readonly bool $story_spotlight,
        public readonly bool $textless,
        public readonly bool $variation,
        public readonly null|string $variation_of, // Could be upgraded to UUID type?
        public readonly null|string $security_stamp,
        public readonly null|string $watermark,
        // Maybe add preview fields?
    ) {}
}