<?php

namespace Mallardduck\ScryfallBulkSdk\Config;

use Composer\InstalledVersions;

final class FileHelpers
{
    public static function checkPathWriteableDirectory(string $path): string|false
    {
        $realPath = realpath($path);
        if ($realPath !== false) {
            if (is_dir($realPath) && is_writable($realPath)) {
                return $realPath;
            }
        }

        return false;
    }

    public static function verifyDirectoryIsWriteable($directory)
    {
        $testFile = $directory . DIRECTORY_SEPARATOR . 'test_write.tmp';

        $currentErrors = ini_get('display_errors');
        ini_set('display_errors', 0);
        if (file_put_contents($testFile, 'test') === false) {
            ini_set('display_errors', $currentErrors);
            return false;
        }
        ini_set('display_errors', $currentErrors);

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

    public static function canDirectoryBeUsed(string|false $directory): string|false
    {
        if ($directory) {
            if (FileHelpers::verifyDirectoryIsWriteable($directory)) {
                return $directory;
            }
        }

        return false;
    }

    public function getSystemTemporaryDirectory(): string
    {
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
    }

    public static function composerVendorFolder(): string|false
    {
        $baseVendorPath = InstalledVersions::getInstallPath(DefaultBulkPathSelector::COMPOSER_PACKAGE_NAME);
        return FileHelpers::checkPathWriteableDirectory($baseVendorPath . DIRECTORY_SEPARATOR . 'blobs');
    }
}