<?php

namespace Mallardduck\ScryfallBulkSdk;

use Exception;
use Mallardduck\ScryfallBulkSdk\Config\Config;
use Mallardduck\ScryfallBulkSdk\Config\DefaultBulkPathSelector;
use PhpOption\None;
use PhpOption\Option;

class BulkFileSource
{
    public static function existingFileOptions(): array
    {
        $config = Config::getInstance();

        $configBulkFilePath = $config->get(Config::BULK_FILE_PATH_KEY);
        $bulkFilePath = (new DefaultBulkPathSelector)($configBulkFilePath);
        $bulkFileType = BulkFileType::from($config->get(Config::BULK_FILE_TYPE_KEY));
        $globPath = $bulkFilePath . DIRECTORY_SEPARATOR . $bulkFileType->slug() . '-**.json';
        $existingFileOptions = glob($globPath);
        if ($existingFileOptions === false) {
            return [];
        }
        return $existingFileOptions;
    }

    /**
     * @return Option
     * @throws Exception
     */
    public function __invoke(): Option
    {
        $existingFileOptions = self::existingFileOptions();

        if (count($existingFileOptions) > 0) {
            usort($existingFileOptions, function($a, $b) {
                // Extract the numbers between the last '-' and '.json'
                preg_match('/-(\d+)\.json$/', $a, $matchesA);
                preg_match('/-(\d+)\.json$/', $b, $matchesB);

                // Convert extracted strings to integers
                $numberA = isset($matchesA[1]) ? (int)$matchesA[1] : 0;
                $numberB = isset($matchesB[1]) ? (int)$matchesB[1] : 0;

                // Compare the numbers; B, before A means descending
                return $numberB <=> $numberA;
            });

            return Option::fromValue($existingFileOptions[0]);
        }

        return None::create();
    }
}