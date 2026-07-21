<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace LIA\LiaForm\Services;

use TYPO3\CMS\Core\Core\Environment;

/**
 * Service for cleaning deprecated files from a folder.
 *
 * Provides logic to delete files older than a specified deadline.
 *
 * @author Johannes Delesky <delesky@louis.info>
 */
class ClearFolderService
{
    /**
     * Default upload folder path relative to public directory.
     */
    private const DEFAULT_UPLOAD_FOLDER = '/typo3temp/formuploads';

    /**
     * Number of seconds in one hour.
     */
    private const SECONDS_PER_HOUR = 3600;

    /**
     * Path to the folder to clear.
     *
     * @var string
     */
    private readonly string $clearFolderPath;

    /**
     * File deadline in seconds.
     *
     * @var int
     */
    private readonly int $fileDeadline;

    /**
     * List of files to keep.
     *
     * @var array<int, string>
     */
    private readonly array $keepFiles;

    /**
     * Create service instance.
     *
     * @param int $hours Amount of hours the file can be saved
     * @param array<int, string> $keepFiles List of files to keep
     */
    public function __construct(
        int $hours,
        array $keepFiles = ['.htaccess', '.gitignore', '.gitkeep']
    ) {
        $this->fileDeadline = $hours * self::SECONDS_PER_HOUR;
        $this->keepFiles = $keepFiles === [] ? ['.htaccess', '.gitignore', '.gitkeep'] : $keepFiles;
        $this->clearFolderPath = Environment::getPublicPath() . self::DEFAULT_UPLOAD_FOLDER;
    }

    /**
     * Delete files recursively from directory.
     *
     * Security: Only allows deletion within the configured clearFolderPath
     * to prevent path traversal attacks.
     *
     * @param string|null $dir Path to file or directory (internal use only)
     * @return bool True on success
     */
    public function recursiveDelete(?string $dir = null): bool
    {
        $source = $this->clearFolderPath;

        if ($dir !== null && $dir !== '') {
            // Security: Validate path is within allowed folder
            $realDir = realpath($dir);
            $realBase = realpath($this->clearFolderPath);
            if ($realDir === false || $realBase === false || !str_starts_with($realDir, $realBase)) {
                return false;
            }
            $source = $dir;
        }

        if (!is_dir($source)) {
            mkdir($source, 0775, true);
        }

        $files = scandir($source);
        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = sprintf('%s/%s', $source, $file);

            if (is_dir($filePath)) {
                $this->recursiveDelete($filePath);
            } elseif ($this->canFileBeDeleted($filePath) && !in_array($file, $this->keepFiles, true)) {
                unlink($filePath);
            }
        }

        return true;
    }

    /**
     * Check if file deadline is reached.
     *
     * @param string $file Path to the file
     * @return bool True if file can be deleted
     */
    private function canFileBeDeleted(string $file): bool
    {
        $currentTime = new \DateTime();
        $fileModificationTime = filemtime($file);

        if ($fileModificationTime === false) {
            return false;
        }

        return ($currentTime->getTimestamp() - $fileModificationTime) > $this->fileDeadline;
    }
}
