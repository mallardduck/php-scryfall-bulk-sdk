<?php

namespace Mallardduck\ScryfallBulkSdk\Scryfall;

final class RelatedCard
{
    public function __construct(
        public readonly string $id, // Could be upgraded to UUID type?
        public readonly string $object,
        public readonly string $component,
        public readonly string $name,
        public readonly string $type_line,
        public readonly string $uri,
    ) {}
}