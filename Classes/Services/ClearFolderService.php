<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Services;

use TYPO3\CMS\Core\Core\Environment;

/**
 * This is the service class with the logic to delete the files in given folder or
 * the default one.
 */
class ClearFolderService
{
    /**
     * @var string $clearFolderPath
     */
    private $clearFolderPath;

    /**
     * @var int $dayInSeconds
     */
    private $dayInSeconds = 3600;

    /**
     * @var int $fileDeadline
     */
    private $fileDeadline;

    /**
     * A list of files which has to be kept.
     *
     * @var array $keepFiles
     */
    private $keepFiles = [];

    /**
     * Class constructor
     *
     * @param int $hours amount of hours the file can be saved
     * @param string $folderName folder to clean
     * @param array $keepFiles a list of file which has to be kept.
     */
    public function __construct(int $hours, string $folderName, array $keepFiles = ['.htaccess', '.gitignore', '.gitkeep'])
    {
        $this->fileDeadline = $hours * $this->dayInSeconds;
        $this->keepFiles = $keepFiles === [] ? ['.htaccess', '.gitignore', '.gitkeep'] : $keepFiles;
        $this->clearFolderPath  = Environment::getPublicPath() . '/typo3temp/' . $folderName;
    }

    /**
     * Delete a file or recursively delete a directory
     *
     * @param string|null $dir Path to file or directory
     *
     * @return bool
     */
    public function recursiveDelete($dir = null)
    {
        $source = $dir ?? $this->clearFolderPath;

        if (!is_dir($source)) {
            mkdir($source, 0775, true);
        }

        foreach (scandir($source) as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            if (is_dir(sprintf('%s/%s', $source, $file))) {
                $this->recursiveDelete(sprintf('%s/%s', $source, $file));
            } elseif ($this->canFileBeDeleted(sprintf('%s/%s', $source, $file))) {
                if (!in_array($file, $this->keepFiles)) {
                    unlink(sprintf('%s/%s', $source, $file));
                }
            }
        }

        return true;
    }

    /**
     * This function check if the deadline of the file is reached.
     *
     * @param string $file
     *
     * @return bool
     */
    private function canFileBeDeleted($file)
    {
        $dt = new \DateTime();

        if (filemtime($file) === false) {
            return false;
        }

        return ($dt->getTimestamp() - filemtime($file)) > $this->fileDeadline;
    }
}
