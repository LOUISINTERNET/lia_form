<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Finisher;

use LIA\LiaForm\Domain\Model\FormElements\AttachableUploadElementInterface;
use LIA\LiaForm\Event\ApplyCustomSettingsToViewEvent;
use LIA\LiaForm\Event\Finisher\SetDefaultValueEvent;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher as CoreEmailFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Event\BeforeEmailFinisherInitializedEvent;

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
        // Let listeners modify the finisher options before they are read (as the core finisher does).
        $this->options = $this->eventDispatcher
            ->dispatch(new BeforeEmailFinisherInitializedEvent($this->finisherContext, $this->options))
            ->getOptions();

        // Flexform overrides write strings instead of integers.
        if (
            isset($this->options['addHtmlPart'])
            && $this->options['addHtmlPart'] === '0'
        ) {
            $this->options['addHtmlPart'] = false;
        }

        $subjectOption = $this->parseOptionForDisplay('subject');
        $subject = is_scalar($subjectOption) ? (string)$subjectOption : '';
        $recipients = $this->getRecipients('recipients');
        $senderAddress = $this->parseOption('senderAddress');
        $senderAddress = is_string($senderAddress) ? $senderAddress : '';

        $senderName = $this->parseOptionForDisplay('senderName');
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
        $title = $this->parseOptionForDisplay('title');
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

        $this->assignMessageToMail($mail);

        if ($attachUploads) {
            $this->attachUploadsToMail($formRuntime, $mail);
            $this->attachFilesToMail($mail, $attachments);
        }

        try {
            $this->mailer->send($mail);
        } catch (TransportExceptionInterface $exception) {
            throw new FinisherException(
                'Failed to send the email: ' . $exception->getMessage(),
                1754047320,
                $exception
            );
        }
    }

    /**
     * Assign the finisher option "message" to the mail, split at the {formValues} placeholder.
     *
     * Mirrors the core EmailFinisher: the core mail templates render messageBefore,
     * the form values and messageAfter; without placeholder the form values are hidden.
     */
    protected function assignMessageToMail(FluidEmail $mail): void
    {
        $message = $this->parseOptionForDisplay('message');
        if (!is_string($message) || $message === '') {
            return;
        }

        // Remove whitespace between HTML tags to prevent lib.parseFunc_RTE
        // from converting newlines into additional blank lines in the email output.
        $message = (string)preg_replace('/>\s+</', '><', $message);
        $placeholderPosition = strpos($message, '{formValues}');
        if ($placeholderPosition === false) {
            $mail->assign('messageBefore', $message);
            $mail->assign('messageAfter', '');
            $mail->assign('hideFormValues', true);
            return;
        }

        $mail->assign('messageBefore', substr($message, 0, $placeholderPosition));
        $mail->assign('messageAfter', substr($message, $placeholderPosition + strlen('{formValues}')));
    }

    /**
     * Parse an option that is read by a human (subject, sender name, title, message).
     *
     * Since TYPO3 14.3.7 the core resolves {<elementIdentifier>} in these options to the
     * display value, e.g. the translated label of a select option instead of its key
     * (Important-106903). On 14.3.0 to 14.3.6 parseOptionAsDisplayValue() does not exist
     * yet, so the plain parseOption() is used there to stay compatible from 14.3.0 on.
     *
     * @return string|array|int|bool|\Closure|callable|null
     */
    private function parseOptionForDisplay(string $optionName)
    {
        if (method_exists($this, 'parseOptionAsDisplayValue')) {
            return $this->parseOptionAsDisplayValue($optionName);
        }

        return $this->parseOption($optionName);
    }

    /**
     * Predicate deciding which form elements contribute file attachments.
     *
     * Covers the core FileUpload element and every element implementing
     * AttachableUploadElementInterface - the extension point for custom
     * upload elements shipped by other extensions.
     */
    protected function isAttachableUploadElement(mixed $element): bool
    {
        return $element instanceof FileUpload
            || $element instanceof AttachableUploadElementInterface;
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

            $value = $formRuntime[$element->getIdentifier()];

            // Multiple files: ObjectStorage (core FileUpload with "multiple") or a list (custom elements).
            if ($value instanceof ObjectStorage || is_array($value)) {
                foreach ($value as $item) {
                    $this->attachFileToMail($mail, $item, $element->getIdentifier());
                }
                continue;
            }

            $this->attachFileToMail($mail, $value, $element->getIdentifier());
        }
    }

    /**
     * Attach a single uploaded file value to the mail.
     *
     * A null value means "no file uploaded" and is skipped. Any other value that is
     * neither a FileReference nor a FileInterface violates the element contract.
     *
     * @throws FinisherException
     */
    private function attachFileToMail(FluidEmail $mail, mixed $value, string $elementIdentifier): void
    {
        if ($value === null) {
            return;
        }

        if ($value instanceof FileReference) {
            $value = $value->getOriginalResource();
        }

        if (!$value instanceof FileInterface) {
            throw new FinisherException(
                sprintf(
                    'The value of the upload element "%s" must be null, a FileReference, a FileInterface or a list of those, "%s" given.',
                    $elementIdentifier,
                    get_debug_type($value)
                ),
                1759046400
            );
        }

        $mail->attach($value->getContents(), $value->getName(), $value->getMimeType());
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
