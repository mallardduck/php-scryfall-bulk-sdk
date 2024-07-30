<?php

namespace Mallardduck\ScryfallBulkSdk;

enum BulkFileType: string
{
    case OracleCards = "oracle_cards";
    case UniqueArtwork = "unique_artwork";
    case DefaultCards = "default_cards";
    case AllCards = "all_cards";
    case Rulings = "rulings";

    public function slug(): string
    {
        return str_replace('_', '-', $this->value);
    }
}