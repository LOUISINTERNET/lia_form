<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace LIA\LiaForm\Event;

/**
 * Event dispatched before phone area code initialization.
 *
 * Allows customization of the data source path for phone area codes.
 *
 * @author LOUIS INTERNET <devs@louis.info>
 */
final class BeforePhoneAreaCodeInitializeEvent
{
    /**
     * Create event instance.
     *
     * @param string $dataSourcePath Path to the area code data source
     */
    public function __construct(
        private string $dataSourcePath
    ) {}

    /**
     * Get the data source path.
     *
     * @return string The path to the area code data source
     */
    public function getDataSourcePath(): string
    {
        return $this->dataSourcePath;
    }

    /**
     * Set the data source path.
     *
     * @param string $dataSourcePath The new path to the area code data source
     */
    public function setDataSourcePath(string $dataSourcePath): void
    {
        $this->dataSourcePath = $dataSourcePath;
    }
}
