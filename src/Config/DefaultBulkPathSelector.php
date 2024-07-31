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

    private static function checkPathWriteableDirectory(string $path): string|false
    {
        $realPath = realpath($path);
        if ($realPath !== false) {
            if (is_dir($realPath) && is_writable($realPath)) {
                return $realPath;
            }
        }

        return false;
    }

    private static function verifyDirectoryIsWriteable($directory)
    {
        $testFile = $directory . DIRECTORY_SEPARATOR . 'test_write.tmp';

        $currentErrors = ini_get('display_errors');
        ini_set('display_errors', 0);
        if (file_put_contents($testFile, 'test') === false) {
            ini_set('display_errors', $currentErrors);
            return false;
        }

        // Check if the file is writable
        if (!is_writable($testFile)) {
            return false;
        }

        // Try deleting the file
        if (!unlink($testFile)) {
            return false;
        }

        return true;
    }

    private static function composerVendorFolder(): string|false
    {
        $baseVendorPath = InstalledVersions::getInstallPath(DefaultBulkPathSelector::COMPOSER_PACKAGE_NAME);
        $potentialBlobPath = $baseVendorPath . DIRECTORY_SEPARATOR . 'blobs';
        return DefaultBulkPathSelector::checkPathWriteableDirectory($potentialBlobPath);
    }

    public static function canDirectoryBeUsed(string|false $directory): string|false
    {
        if ($directory) {
            if (DefaultBulkPathSelector::verifyDirectoryIsWriteable($directory)) {
                return $directory;
            }
        }

        return false;
    }

    protected function getSystemTemporaryDirectory(): string
    {
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
    }

    /**
     * Essentially we need to check the various options for R/W access.
     * For any instance where we don't have R/W access bail out and move onto the next option.
     * Each option should be a semi-deterministic path - so that we can reliably call the same method on the same system and get the same path.
     *
     * @return string
     *
     * @throws RuntimeException
     * @throws PathAlreadyExists
     * @throws Throwable
     */
    public function __invoke(): string
    {
        // Option 1. Find the composer autoload folder, then the folder where this library is installed use predefined folder,
        if ($composerVendorFolder = self::canDirectoryBeUsed($this->composerVendorFolder())) {
            $bulkFolderPath = $composerVendorFolder . DIRECTORY_SEPARATOR . self::BULK_FILE_FOLDER;
            mkdir($bulkFolderPath, 0777, true);
            return $bulkFolderPath;
        }
        // Option 2. Only if CLI context, use the CWD
        if (php_sapi_name() == "cli") {
            if ($cwdBase = self::canDirectoryBeUsed(getcwd())) {
                $bulkFolderPath = $cwdBase . DIRECTORY_SEPARATOR . self::BULK_FILE_FOLDER;
                mkdir($bulkFolderPath, 0777, true);
                return $bulkFolderPath;
            }
        }
        // Option Last - Use `spatie/temporary-directory` to just grab a system temp-dir.
        $tempDirectoryBuilder = (new TemporaryDirectory($this->getSystemTemporaryDirectory()))
            ->name(str_replace('/', '_', self::COMPOSER_PACKAGE_NAME));
        $tempDirectoryPath = $tempDirectoryBuilder->path();
        try {
            $tempDirectoryBuilder->create();
        } catch (Throwable $throwable) {
            if (!($throwable instanceof PathAlreadyExists)) {
                throw $throwable;
            }
        }
        if ($validatedTempDir = self::canDirectoryBeUsed($tempDirectoryPath)) {
            return $validatedTempDir;
        }

        throw new RuntimeException("Cannot even...");
    }
}