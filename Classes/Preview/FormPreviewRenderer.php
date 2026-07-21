<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace LIA\LiaForm\Preview;

use LIA\LiaForm\Event\RenderFinisherPreviewEvent;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Domain\FlexFormFieldValues;
use TYPO3\CMS\Core\Domain\RecordInterface;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Hooks\FormPagePreviewRenderer as CoreFormPagePreviewRenderer;

/**
 * Extended preview renderer for form content elements.
 *
 * Extends the core preview to display all configured finishers.
 *
 * @author LOUIS INTERNET <devs@louis.info>
 */
class FormPreviewRenderer extends CoreFormPagePreviewRenderer
{
    /**
     * Render the preview content for form elements.
     *
     * @param GridColumnItem $item The grid column item
     * @return string The rendered preview HTML
     */
    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        // Get core preview first
        $content = parent::renderPageModulePreviewContent($item);

        // Add finisher overview
        $record = $item->getRecord();
        $finisherContent = $this->renderFinisherOverview($record);

        return $content . $finisherContent;
    }

    /**
     * Render finisher overview.
     *
     * @param RecordInterface $record The content record
     * @return string HTML content
     */
    private function renderFinisherOverview(RecordInterface $record): string
    {
        if (!$record->has('pi_flexform')) {
            return '';
        }

        $flexFormData = $record->get('pi_flexform');
        if (!$flexFormData instanceof FlexFormFieldValues) {
            return '';
        }

        $persistenceIdentifier = '';
        if ($flexFormData->has('sDEF/settings.persistenceIdentifier')) {
            $persistenceIdentifier = $flexFormData->get('sDEF/settings.persistenceIdentifier');
        }

        if ($persistenceIdentifier === '') {
            return '';
        }

        try {
            $formDefinition = $this->formPersistenceManager->load($persistenceIdentifier);
        } catch (\Exception $e) {
            GeneralUtility::makeInstance(LogManager::class)->getLogger(self::class)->warning(
                'FormPreviewRenderer: Failed to load form "' . $persistenceIdentifier . '": ' . $e->getMessage()
            );
            return '';
        }

        $finishers = $formDefinition['finishers'] ?? [];
        if (empty($finishers)) {
            return '';
        }

        // Get FlexForm override settings
        $flexFormArray = $this->getFlexFormArray($record);
        $overrideFinishers = (bool)($flexFormArray['settings']['overrideFinishers'] ?? false);

        $html = '<hr style="margin: 8px 0; border-color: #ccc;">';
        $html .= '<strong style="font-size: 11px;">Finishers:</strong>';
        $html .= '<table style="font-size: 10px; margin-top: 4px; width: 100%; border-collapse: collapse;">';

        foreach ($finishers as $finisher) {
            $html .= $this->renderFinisherRow($finisher, $flexFormArray, $overrideFinishers);
        }

        $html .= '</table>';

        if ($overrideFinishers) {
            $html .= '<small style="color: #666;"><em>FlexForm overrides active</em></small>';
        }

        return $html;
    }

    /**
     * Get FlexForm data as array.
     *
     * @param RecordInterface $record The record
     * @return array<string, mixed> The FlexForm array
     */
    private function getFlexFormArray(RecordInterface $record): array
    {
        // Record::toArray() yields processed values (pi_flexform would be a
        // FlexFormFieldValues object) — the raw record still holds the XML string
        $rawRecord = $record->getRawRecord()?->toArray() ?? [];
        $flexFormXml = $rawRecord['pi_flexform'] ?? '';

        if ($flexFormXml === '' || !is_string($flexFormXml)) {
            return [];
        }

        $flexFormTools = GeneralUtility::makeInstance(FlexFormTools::class);
        return $flexFormTools->convertFlexFormContentToArray($flexFormXml);
    }

    /**
     * Render a single finisher row.
     *
     * @param array<string, mixed> $finisher The finisher configuration
     * @param array<string, mixed> $flexFormArray The FlexForm data
     * @param bool $overrideFinishers Whether finisher overrides are enabled
     * @return string HTML table row
     */
    private function renderFinisherRow(array $finisher, array $flexFormArray, bool $overrideFinishers): string
    {
        $identifier = $finisher['identifier'] ?? 'Unknown';
        $options = $finisher['options'] ?? [];

        $details = $this->getFinisherDetails($identifier, $options, $flexFormArray, $overrideFinishers);

        return sprintf(
            '<tr style="border-bottom: 1px solid #eee;"><td style="padding: 2px 4px; font-weight: bold; vertical-align: top; white-space: nowrap;">%s</td><td style="padding: 2px 4px;">%s</td></tr>',
            htmlspecialchars((string)$identifier),
            $details
        );
    }

    /**
     * Get details for a specific finisher.
     *
     * Dispatches RenderFinisherPreviewEvent first so extensions can provide a
     * custom preview for their own finisher types. If a listener handles the
     * event, its HTML wins and the built-in rendering is skipped.
     *
     * @param string $identifier The finisher identifier
     * @param array<string, mixed> $options The finisher options
     * @param array<string, mixed> $flexFormArray The FlexForm data
     * @param bool $overrideFinishers Whether overrides are enabled
     * @return string HTML details
     */
    private function getFinisherDetails(string $identifier, array $options, array $flexFormArray, bool $overrideFinishers): string
    {
        $flexFinisher = $flexFormArray['settings']['finishers'][$identifier] ?? [];

        // EventDispatcher via makeInstance: this renderer is instantiated by
        // StandardPreviewRendererResolver::makeInstance() and its parent has a
        // promoted readonly constructor, so constructor injection is not viable.
        $event = new RenderFinisherPreviewEvent($identifier, $options, $flexFinisher, $overrideFinishers);
        GeneralUtility::makeInstance(EventDispatcher::class)->dispatch($event);

        if ($event->isHandled()) {
            return $event->getPreviewHtml();
        }

        return match ($identifier) {
            'EmailToReceiver', 'EmailToSender' => $this->renderEmailDetails($identifier, $options, $flexFormArray, $overrideFinishers),
            'Redirect' => $this->renderRedirectDetails($options),
            'DeleteUploads' => 'Deletes uploads',
            default => '-',
        };
    }

    /**
     * Render email finisher details.
     *
     * @param string $identifier The finisher identifier
     * @param array<string, mixed> $options The finisher options
     * @param array<string, mixed> $flexFormArray The FlexForm data
     * @param bool $overrideFinishers Whether overrides are enabled
     * @return string HTML details
     */
    private function renderEmailDetails(string $identifier, array $options, array $flexFormArray, bool $overrideFinishers): string
    {
        $parts = [];

        // Recipients (To)
        $recipients = $options['recipients'] ?? [];
        if (!empty($recipients)) {
            $toEmails = implode(', ', array_keys($recipients));
            $parts[] = '<b>To:</b> ' . htmlspecialchars($toEmails);
        }

        // CC from FlexForm override
        if ($overrideFinishers) {
            $ccKey = 'finishers.' . $identifier . '.carbonCopyRecipients';
            $cc = $flexFormArray['settings'][$ccKey] ?? '';
            if ($cc !== '') {
                $parts[] = '<b>CC:</b> ' . htmlspecialchars((string)$cc);
            }

            $bccKey = 'finishers.' . $identifier . '.blindCarbonCopyRecipients';
            $bcc = $flexFormArray['settings'][$bccKey] ?? '';
            if ($bcc !== '') {
                $parts[] = '<b>BCC:</b> ' . htmlspecialchars((string)$bcc);
            }
        }

        // Template
        $template = $options['templateName'] ?? '';
        if ($template !== '') {
            $parts[] = '<b>Tpl:</b> ' . htmlspecialchars((string)$template);
        }

        return implode(' | ', $parts) ?: '-';
    }

    /**
     * Render redirect finisher details.
     *
     * @param array<string, mixed> $options The finisher options
     * @return string HTML details
     */
    private function renderRedirectDetails(array $options): string
    {
        $pageUid = $options['pageUid'] ?? '';
        if ($pageUid !== '') {
            return '<b>Page:</b> ' . htmlspecialchars((string)$pageUid);
        }
        return '-';
    }
}
