<?php

declare(strict_types=1);

namespace LIA\LiaForm\Event;

final class BeforePhoneAreaCodeInitializeEvent
{
    /**
     * Event constructor
     *
     * @param string $dataSourcePath
     */
    public function __construct(private string $dataSourcePath) {}

    /**
     * Get the value of dataSourcePath
     */
    public function getDataSourcePath(): string
    {
        return $this->dataSourcePath;
    }

    /**
     * Set the value of dataSourcePath
     *
     * @param string $dataSourcePath
     */
    public function setDataSourcePath(string $dataSourcePath): void
    {
        $this->dataSourcePath = $dataSourcePath;
    }
}
