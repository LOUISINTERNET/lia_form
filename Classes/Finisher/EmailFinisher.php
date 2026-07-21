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
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher as CoreEmailFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Service\TranslationService;

/**
 * Class EmailFinisher
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
     * @return FluidEmail
     * @throws FinisherException
     */
    protected function processView(FormRuntime $formRuntime, string $format = 'Html')
    {
        $dispatcher = GeneralUtility::makeInstance(EventDispatcher::class);

        // allows to set default values if needed
        $setDefaultValuesEvent = new SetDefaultValueEvent($formRuntime, $this->shortFinisherIdentifier);
        $dispatcher->dispatch($setDefaultValuesEvent);
        $formRuntime = $setDefaultValuesEvent->getFormRuntime();

        $isAdminMail = $this->shortFinisherIdentifier === 'EmailToReceiver';
        $view = $isAdminMail ? $this->processAdminMail($formRuntime, $format) : $this->processUserMail($formRuntime, $format);

        $view->assign('requestTime', new \DateTime());

        $event = new ApplyCustomSettingsToViewEvent($view);
        $dispatcher->dispatch($event);
        $view = $event->getEmailView();

        if (isset($this->getRecipients('recipients')[0])) {
            $view->assign('senderName', $this->getRecipients('recipients')[0]->getName());
        } else {
            $view->assign('senderName', 'User');
        }

        $view->assign('salutation', $formRuntime->getElementValue('salutation'));
        $view->assign('domain', $formRuntime->getElementValue('domain'));

        return $view;
    }

    /**
     * @return FluidEmail
     * @throws FinisherException
     */
    protected function processAdminMail(FormRuntime $formRuntime, string $format = 'Html')
    {
        $formRuntime = $this->finisherContext->getFormRuntime();

        $twoLetterIsoCode = 'de';

        if (
            $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface
            && $GLOBALS['TYPO3_REQUEST']->getAttribute('language') instanceof SiteLanguage
        ) {
            $twoLetterIsoCode = $GLOBALS['TYPO3_REQUEST']->getAttribute('language')->getLocale()->getLanguageCode();
        }

        $formRuntime->getFormState()->setFormValue('currentLanguage', $twoLetterIsoCode);

        return parent::initializeFluidEmail($formRuntime);
    }

    /**
     * @param FormRuntime $formRuntime
     * @return FluidEmail
     * @throws FinisherException
     */
    protected function processUserMail($formRuntime, string $format = 'Html')
    {
        return parent::initializeFluidEmail($formRuntime);
    }

    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     *
     * @throws FinisherException
     */
    protected function executeInternal()
    {
        $languageBackup = null;
        // Flexform overrides write strings instead of integers so
        // we need to cast the string '0' to false.
        if (
            isset($this->options['addHtmlPart'])
            && $this->options['addHtmlPart'] === '0'
        ) {
            $this->options['addHtmlPart'] = false;
        }

        $subject = $this->parseOption('subject');
        $recipients = $this->getRecipients('recipients');
        $senderAddress = $this->parseOption('senderAddress');
        $senderAddress = is_string($senderAddress) ? $senderAddress : '';

        $senderName = $this->parseOption('senderName');
        $senderName = is_string($senderName) ? $senderName : '';

        $replyToRecipients = $this->getRecipients('replyToRecipients');
        $carbonCopyRecipients = $this->getRecipients('carbonCopyRecipients');
        $blindCarbonCopyRecipients = $this->getRecipients('blindCarbonCopyRecipients');
        $addHtmlPart = (bool)$this->parseOption('addHtmlPart');
        $attachUploads = $this->parseOption('attachUploads');
        $title = $this->parseOption('title');
        $title = is_string($title) && $title !== '' ? $title : $subject;

        $attachments = $this->parseOption('attachments');

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

        $translationService = GeneralUtility::makeInstance(TranslationService::class);
        if (is_string($this->options['translation']['language'] ?? null) && $this->options['translation']['language'] !== '') {
            $languageBackup = $translationService->getLanguage();
            $translationService->setLanguage($this->options['translation']['language']);
        }

        $mail = $this
            ->initializeFluidEmail($formRuntime)
            ->from(new Address($senderAddress, $senderName))
            ->to(...$recipients)
            ->subject($subject)
            ->format($addHtmlPart ? FluidEmail::FORMAT_BOTH : FluidEmail::FORMAT_PLAIN)
            ->assign('title', $title);

        if ($replyToRecipients !== []) {
            $mail->replyTo(...$replyToRecipients);
        }

        if ($carbonCopyRecipients !== []) {
            $mail->cc(...$carbonCopyRecipients);
        }

        if (!empty($languageBackup)) {
            $translationService->setLanguage($languageBackup);
        }

        if ($attachUploads) {
            foreach ($formRuntime->getFormDefinition()->getRenderablesRecursively() as $element) {
                if (!$element instanceof FileUpload) {
                    continue;
                }

                $file = $formRuntime[$element->getIdentifier()];

                if ($file) {
                    // multiple files
                    if (is_array($file) && isset($file[0])) {
                        foreach ($file as $item) {
                            if ($item instanceof FileReference) {
                                $item = $item->getOriginalResource();
                            }

                            $mail->attach($item->getContents(), $item->getName(), $item->getMimeType());
                        }

                        // single file
                    } else {
                        if ($file instanceof FileReference) {
                            $file = $file->getOriginalResource();
                        }

                        $mail->attach($file->getContents(), $file->getName(), $file->getMimeType());
                    }
                }
            }

            $attachments = explode(',', $attachments);
            foreach ($attachments as $attachment) {
                if ($attachment === '[Empty]') {
                    $attachment = '';
                }

                if ($attachment !== '' && $attachment !== '0') {
                    $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
                    $file = $resourceFactory->getFileObject((int)$attachment);
                    if ($file) {
                        if ($file instanceof FileReference) {
                            $file = $file->getOriginalResource();
                        }

                        $mail->attach($file->getContents(), $file->getName(), $file->getMimeType());
                    }
                }
            }
        }

        GeneralUtility::makeInstance(MailerInterface::class)->send($mail);

        if ($blindCarbonCopyRecipients !== []) {
            $mail->to(...$blindCarbonCopyRecipients);
            GeneralUtility::makeInstance(MailerInterface::class)->send($mail);
        }
    }

    /**
     * @param array $options configuration options in the format ['option1' => 'value1', 'option2' => 'value2', ...]
     */
    public function setOptions(array $options): void
    {
        parent::setOptions($options);
        if (array_key_exists('translation', $this->options) && array_key_exists('language', $this->options['translation'])) {
            $this->options['translation']['language'] = '';
        }
    }
}
