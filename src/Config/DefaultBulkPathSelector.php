<?php

namespace Mallardduck\ScryfallBulkSdk\Config;

use Composer\InstalledVersions;
use RuntimeException;
use Spatie\TemporaryDirectory\Exceptions\PathAlreadyExists;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Throwable;

/**
 * This class is used to find the most sane location on the system to store bulk files.
 * Ideally the user will configure the path manually and be in full control - that's option one.
 * This class will only be used if we're already beyond that option.
 */
final class DefaultBulkPathSelector
{
    const COMPOSER_PACKAGE_NAME = "mallardduck/scryfall-bulk-sdk";
    const BULK_FILE_FOLDER = "bulk-files";

    /**
     * Essentially we need to check the various options for R/W access.
     * For any instance where we don't have R/W access bail out and move onto the next option.
     * Each option should be a semi-deterministic path -
     * so that we can reliably call the same method on the same system and get the same path.
     *
     * @param string|null $userConfigBulkPath
     * @return string
     *
     * @throws PathAlreadyExists
     * @throws Throwable
     */
    public function __invoke(?string $userConfigBulkPath = null): string
    {
        if ($userConfigBulkPath != null && $userFolder = FileHelpers::canDirectoryBeUsed($userConfigBulkPath)) {
            return $userFolder;
        }

        // Option 1. Find the composer autoload folder, then the folder where this library is installed use predefined folder,
        if ($composerVendorFolder = FileHelpers::canDirectoryBeUsed(FileHelpers::composerVendorFolder())) {
            $bulkVendorFolder = $composerVendorFolder . DIRECTORY_SEPARATOR . self::BULK_FILE_FOLDER;
            if (!file_exists($bulkVendorFolder)) {
                mkdir($bulkVendorFolder);
            }
            return $bulkVendorFolder;
        }

        // Option Last - Use `spatie/temporary-directory` to just grab a system temp-dir.
        $tempDirectoryBuilder = (new TemporaryDirectory(FileHelpers::getSystemTemporaryDirectory()))
            ->name(str_replace('/', '_', self::COMPOSER_PACKAGE_NAME));
        $tempDirectoryPath = $tempDirectoryBuilder->path();
        try {
            $tempDirectoryBuilder->create();
        } catch (Throwable $throwable) {
            if (!($throwable instanceof PathAlreadyExists)) {
                throw $throwable;
            }
        }
        if ($validatedTempDir = FileHelpers::canDirectoryBeUsed($tempDirectoryPath)) {
            return $validatedTempDir;
        }

        throw new RuntimeException("Cannot even...");
    }
}