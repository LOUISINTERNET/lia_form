<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Finisher;

use LIA\LiaForm\Event\ApplyCustomSettingsToViewEvent;
use LIA\LiaForm\Event\Finisher\SetDefaultValueEvent;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher as CoreEmailFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Extended email finisher with separate admin and user mail processing.
 *
 * Registered as a service override for the core EmailFinisher service id
 * (see Configuration/Services.yaml) so the container autowires the parent
 * constructor dependencies in TYPO3 v14.
 */
class EmailFinisher extends CoreEmailFinisher
{
    /**
     * @param FormRuntime $formRuntime
     */
    protected function initializeFluidEmail(FormRuntime $formRuntime): FluidEmail
    {
        return $this->processView($formRuntime);
    }

    /**
     * Process the view based on mail type.
     *
     * @throws FinisherException
     */
    protected function processView(FormRuntime $formRuntime, string $format = 'Html'): FluidEmail
    {
        // allows to set default values if needed
        $setDefaultValuesEvent = new SetDefaultValueEvent($formRuntime, $this->shortFinisherIdentifier);
        $this->eventDispatcher->dispatch($setDefaultValuesEvent);
        $formRuntime = $setDefaultValuesEvent->getFormRuntime();

        $isAdminMail = $this->shortFinisherIdentifier === 'EmailToReceiver';
        $view = $isAdminMail
            ? $this->processAdminMail($formRuntime, $format)
            : $this->processUserMail($formRuntime, $format);

        $view->assign('requestTime', new \DateTime());

        $event = new ApplyCustomSettingsToViewEvent($view);
        $this->eventDispatcher->dispatch($event);
        $view = $event->getEmailView();

        $recipients = $this->getRecipients('recipients');
        $senderName = $recipients[0]?->getName() ?? '';

        // For user mails, fallback to form field if YAML config has no recipient name
        // This ensures backwards compatibility for existing forms without '{lastname}' in recipients
        if ($this->shortFinisherIdentifier !== 'EmailToReceiver' && $senderName === '') {
            $senderName = (string)($formRuntime->getElementValue('lastname')
                ?? $formRuntime->getElementValue('name')
                ?? '');
        }

        $view->assign('senderName', $senderName !== '' ? $senderName : 'User');
        $view->assign('salutation', $formRuntime->getElementValue('salutation'));
        $view->assign('domain', $formRuntime->getElementValue('domain'));

        return $view;
    }

    /**
     * Process admin mail view.
     *
     * @throws FinisherException
     */
    protected function processAdminMail(FormRuntime $formRuntime, string $format = 'Html'): FluidEmail
    {
        $formRuntime = $this->finisherContext->getFormRuntime();

        $twoLetterIsoCode = 'de';

        // TYPO3 14: Get request from FormRuntime instead of deprecated $GLOBALS['TYPO3_REQUEST']
        $request = $formRuntime->getRequest();
        $language = $request->getAttribute('language');
        if ($language instanceof SiteLanguage) {
            $twoLetterIsoCode = $language->getLocale()->getLanguageCode();
        }

        $formRuntime->getFormState()?->setFormValue('currentLanguage', $twoLetterIsoCode);

        return parent::initializeFluidEmail($formRuntime);
    }

    /**
     * Process user mail view.
     *
     * @throws FinisherException
     */
    protected function processUserMail(FormRuntime $formRuntime, string $format = 'Html'): FluidEmail
    {
        return parent::initializeFluidEmail($formRuntime);
    }

    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     *
     * @throws FinisherException
     */
    protected function executeInternal(): void
    {
        // Flexform overrides write strings instead of integers.
        if (
            isset($this->options['addHtmlPart'])
            && $this->options['addHtmlPart'] === '0'
        ) {
            $this->options['addHtmlPart'] = false;
        }

        $subjectOption = $this->parseOption('subject');
        $subject = is_scalar($subjectOption) ? (string)$subjectOption : '';
        $recipients = $this->getRecipients('recipients');
        $senderAddress = $this->parseOption('senderAddress');
        $senderAddress = is_string($senderAddress) ? $senderAddress : '';

        $senderName = $this->parseOption('senderName');
        $senderName = is_string($senderName) ? $senderName : '';

        $replyToRecipients = $this->getRecipients('replyToRecipients');
        if ($replyToRecipients === []) {
            $replyToRecipients = $this->getLegacyRecipient('replyToAddress');
        }

        $carbonCopyRecipients = $this->getRecipients('carbonCopyRecipients');
        if ($carbonCopyRecipients === []) {
            $carbonCopyRecipients = $this->getLegacyRecipient('carbonCopyAddress');
        }

        $blindCarbonCopyRecipients = $this->getRecipients('blindCarbonCopyRecipients');
        if ($blindCarbonCopyRecipients === []) {
            $blindCarbonCopyRecipients = $this->getLegacyRecipient('blindCarbonCopyAddress');
        }
        $addHtmlPart = (bool)$this->parseOption('addHtmlPart');
        $attachUploads = $this->parseOption('attachUploads');
        $title = $this->parseOption('title');
        $title = is_string($title) && $title !== '' ? $title : $subject;

        $attachmentsOption = $this->parseOption('attachments');
        $attachments = is_string($attachmentsOption) ? $attachmentsOption : null;

        if (empty($subject)) {
            throw new FinisherException('The option "subject" must be set for the EmailFinisher.', 1327060320);
        }

        if ($recipients === []) {
            throw new FinisherException('The option "recipients" must be set for the EmailFinisher.', 1327060200);
        }

        if ($senderAddress === '' || $senderAddress === '0') {
            throw new FinisherException('The option "senderAddress" must be set for the EmailFinisher.', 1327060210);
        }

        $formRuntime = $this->finisherContext->getFormRuntime();

        $mail = $this
            ->initializeFluidEmail($formRuntime)
            ->from(new Address($senderAddress, $senderName))
            ->to(...$recipients)
            ->subject($subject)
            ->format($addHtmlPart ? FluidEmail::FORMAT_BOTH : FluidEmail::FORMAT_PLAIN)
            ->assign('title', $title);

        // TYPO3 v14: TranslationService::set/getLanguage() removed. The active language
        // is now propagated to the Fluid template via the languageKey assignment.
        if (is_string($this->options['translation']['language'] ?? null) && $this->options['translation']['language'] !== '') {
            $mail->assign('languageKey', $this->options['translation']['language']);
        }

        if ($replyToRecipients !== []) {
            $mail->replyTo(...$replyToRecipients);
        }

        if ($carbonCopyRecipients !== []) {
            $mail->cc(...$carbonCopyRecipients);
        }

        // Set BCC recipients before sending - they receive the same mail invisibly to other recipients
        if ($blindCarbonCopyRecipients !== []) {
            $mail->bcc(...$blindCarbonCopyRecipients);
        }

        if ($attachUploads) {
            $this->attachUploadsToMail($formRuntime, $mail);
            $this->attachFilesToMail($mail, $attachments);
        }

        $this->mailer->send($mail);
    }

    /**
     * Predicate deciding which form elements contribute file attachments.
     *
     * Extension point for subclasses to attach custom upload element types
     * without overriding executeInternal().
     */
    protected function isAttachableUploadElement(mixed $element): bool
    {
        return $element instanceof FileUpload;
    }

    /**
     * Attach uploaded files to the mail.
     */
    protected function attachUploadsToMail(FormRuntime $formRuntime, FluidEmail $mail): void
    {
        foreach ($formRuntime->getFormDefinition()->getRenderablesRecursively() as $element) {
            if (!$this->isAttachableUploadElement($element)) {
                continue;
            }

            $file = $formRuntime[$element->getIdentifier()];

            if ($file === null) {
                continue;
            }

            // Multiple files.
            if (is_array($file) && isset($file[0])) {
                foreach ($file as $item) {
                    if ($item instanceof FileReference) {
                        $item = $item->getOriginalResource();
                    }
                    $mail->attach($item->getContents(), $item->getName(), $item->getMimeType());
                }
                continue;
            }

            // Single file.
            if ($file instanceof FileReference) {
                $file = $file->getOriginalResource();
            }

            $mail->attach($file->getContents(), $file->getName(), $file->getMimeType());
        }
    }

    /**
     * Attach additional files to the mail.
     *
     * @param string|null $attachments Comma-separated attachment IDs from finisher options
     */
    private function attachFilesToMail(FluidEmail $mail, ?string $attachments): void
    {
        if ($attachments === null || $attachments === '') {
            return;
        }

        $attachmentIds = explode(',', $attachments);

        foreach ($attachmentIds as $attachment) {
            $attachment = trim($attachment);

            if ($attachment === '[Empty]' || $attachment === '') {
                continue;
            }

            // Validate and cast attachment ID to integer for type safety
            $fileUid = (int)$attachment;
            if ($fileUid <= 0) {
                continue;
            }

            $file = GeneralUtility::makeInstance(ResourceFactory::class)->getFileObject($fileUid);
            $mail->attach($file->getContents(), $file->getName(), $file->getMimeType());
        }
    }

    /**
     * Get recipient from legacy single-address option (TYPO3 < 12 format).
     *
     * Converts legacy options like 'carbonCopyAddress' (string) to the new
     * 'carbonCopyRecipients' format (array of Address objects).
     *
     * @param string $legacyOption The legacy option name (e.g., 'carbonCopyAddress')
     * @return array<int, Address> Array of Address objects, empty if option not set
     */
    protected function getLegacyRecipient(string $legacyOption): array
    {
        $address = $this->parseOption($legacyOption);

        if (!is_string($address) || $address === '') {
            return [];
        }

        $address = trim($address);
        if ($address === '') {
            return [];
        }

        return [new Address($address)];
    }

    /**
     * @param array $options configuration options in the format ['option1' => 'value1', 'option2' => 'value2', ...]
     */
    public function setOptions(array $options): void
    {
        parent::setOptions($options);
        if (
            array_key_exists('translation', $this->options)
            && array_key_exists('language', $this->options['translation'])
        ) {
            $this->options['translation']['language'] = '';
        }
    }
}
