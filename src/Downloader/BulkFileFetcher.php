<?php

namespace Mallardduck\ScryfallBulkSdk\Downloader;

use Carbon\Carbon;
use GuzzleHttp\Psr7\Utils;
use Mallardduck\ScryfallBulkSdk\BulkFileSource;
use Mallardduck\ScryfallBulkSdk\BulkFileType;
use Mallardduck\ScryfallBulkSdk\Config\Config;
use Mallardduck\ScryfallBulkSdk\Config\DefaultBulkPathSelector;

class BulkFileFetcher
{
    const SCRYFALL_BULK_URL = "https://api.scryfall.com/bulk-data";

    /**
     * @return false Returns based on if it fetched a file, not success. Only failure is an exception.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Spatie\TemporaryDirectory\Exceptions\PathAlreadyExists
     * @throws \Throwable
     */
    public function __invoke(): bool
    {
        $config = Config::getInstance();
        $bulkFileType = BulkFileType::from($config->get(Config::BULK_FILE_TYPE_KEY));


        $newestFile = (new BulkFileSource)();
        if ($newestFile->isDefined()) {
            preg_match('/-(\d+)\.json$/', $newestFile->get(), $date);
            $fileUpdatedAt = Carbon::createFromFormat('YmdHis', $date[1]);

            if ($fileUpdatedAt->lessThan(Carbon::now()->subHours(8)) === false) {
                echo "Will not fetch file until older than 8 hours." . PHP_EOL;
                return false;
            }
        }

        echo "The file is missing or outdated, will fetch" . PHP_EOL;

        $configBulkFilePath = $config->get(Config::BULK_FILE_PATH_KEY);
        $bulkFilePath = (new DefaultBulkPathSelector)($configBulkFilePath);

        # The file is missing or older, so we build it...
        $client = (new ClientBuilder)();

        $response = $client->get(self::SCRYFALL_BULK_URL);
        $rawBody = $response->getBody();

        $bulkDataList = json_decode($rawBody, true);
        $targetList = array_values(array_filter($bulkDataList['data'], function ($item) use ($bulkFileType) {
            return $item['type'] === $bulkFileType->value;
        }))[0];

        $targetUri = $targetList['download_uri'];
        $fileName = substr($targetUri, strrpos($targetUri, '/', -1) + 1);
        $targetFilePath = $bulkFilePath . DIRECTORY_SEPARATOR . $fileName;
        $newFile = Utils::tryFopen($targetFilePath, 'w');
        $stream = Utils::streamFor($newFile);

        $downloadResponse = $client->get($targetUri, ['sink' => $stream]);
        if ($downloadResponse->getStatusCode() !== 200) {
            throw new \RuntimeException($downloadResponse->getBody()->getContents());
        }
        return true;
    }
}