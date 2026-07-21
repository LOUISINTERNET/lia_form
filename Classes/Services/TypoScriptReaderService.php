<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Services;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * This service provide some function to get data from the typoscript of this Extension.
 */
class TypoScriptReaderService implements SingletonInterface
{
    /**
     * Cached TypoScript plugin settings.
     *
     * @var array<string, mixed>
     */
    private array $formPluginSettings = [];

    /**
     * Flag indicating if settings have been initialized.
     *
     * @var bool
     */
    private bool $initialized = false;

    /**
     * Constructor.
     *
     * @param TypoScriptService $typoScriptService Service for TypoScript conversion
     * @param ConfigurationManager $configurationManager Configuration manager for TypoScript access
     */
    public function __construct(
        private readonly TypoScriptService $typoScriptService,
        private readonly ConfigurationManager $configurationManager
    ) {}

    /**
     * Return the TypoScript plugin settings.
     *
     * @return array<string, mixed> The form plugin settings
     */
    public function getFormSettings(): array
    {
        if (!$this->initialized) {
            $this->initializeSettings();
        }

        return $this->formPluginSettings;
    }

    /**
     * Check if AJAX is active for the form plugin.
     *
     * @return bool True if AJAX is active
     */
    public function isAjaxActive(): bool
    {
        if (!$this->initialized) {
            $this->initializeSettings();
        }

        if (empty($this->formPluginSettings['ajax'])) {
            return false;
        }

        return (bool)$this->formPluginSettings['ajax']['active'];
    }

    /**
     * Get the pageNum fallback value from TypoScript.
     *
     * @return int|null The fallback page type or null if not configured
     */
    public function getContentPageTypeFallback(): ?int
    {
        if (!$this->initialized) {
            $this->initializeSettings();
        }

        if (empty($this->formPluginSettings['ajax']['getContentPageTypeFallback'])) {
            return null;
        }

        return (int)$this->formPluginSettings['ajax']['getContentPageTypeFallback'];
    }

    /**
     * Initialize the TypoScript settings.
     *
     * Loads TypoScript configuration and caches it for the request.
     */
    private function initializeSettings(): void
    {
        $this->formPluginSettings = $this->typoScriptService->convertTypoScriptArrayToPlainArray(
            $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT)
        )['plugin']['tx_form']['settings'] ?? [];

        $this->initialized = true;
    }
}
