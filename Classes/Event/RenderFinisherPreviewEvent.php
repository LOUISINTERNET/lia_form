<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

declare(strict_types=1);

namespace LIA\LiaForm\Event;

/**
 * Event dispatched when rendering a single finisher's preview in the page
 * module. Extensions can listen to this event to provide custom preview
 * rendering for their own finisher types. If handled, the default rendering
 * is skipped for that finisher.
 */
final class RenderFinisherPreviewEvent
{
    /**
     * The rendered preview HTML.
     *
     * @var string
     */
    private string $previewHtml = '';

    /**
     * Whether this event was handled by a listener.
     *
     * @var bool
     */
    private bool $handled = false;

    /**
     * @param string $identifier The finisher identifier
     * @param array<string, mixed> $options The finisher options from YAML
     * @param array<string, mixed> $flexFormFinisher The finisher settings from FlexForm
     * @param bool $overrideFinishers Whether FlexForm overrides are enabled
     */
    public function __construct(
        private readonly string $identifier,
        private readonly array $options,
        private readonly array $flexFormFinisher,
        private readonly bool $overrideFinishers
    ) {}

    /**
     * Get the finisher identifier.
     *
     * @return string The finisher identifier (e.g., 'EmailToReceiver', 'MyCustomFinisher')
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Get the finisher options from YAML definition.
     *
     * @return array<string, mixed> The options array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the finisher settings from FlexForm.
     *
     * @return array<string, mixed> The FlexForm finisher settings
     */
    public function getFlexFormFinisher(): array
    {
        return $this->flexFormFinisher;
    }

    /**
     * Check if FlexForm overrides are enabled.
     *
     * @return bool True if overrides are enabled
     */
    public function isOverrideFinishers(): bool
    {
        return $this->overrideFinishers;
    }

    /**
     * Set the preview HTML. Marks the event as handled so the default
     * rendering is skipped.
     *
     * @param string $html The preview HTML
     */
    public function setPreviewHtml(string $html): void
    {
        $this->previewHtml = $html;
        $this->handled = true;
    }

    /**
     * Get the preview HTML.
     *
     * @return string The preview HTML
     */
    public function getPreviewHtml(): string
    {
        return $this->previewHtml;
    }

    /**
     * Check if this event was handled by a listener.
     *
     * @return bool True if handled
     */
    public function isHandled(): bool
    {
        return $this->handled;
    }
}
