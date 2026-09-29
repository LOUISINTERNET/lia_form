..  _attachableUploadElement:

================================
AttachableUploadElementInterface
================================

The ``EmailFinisher`` of this extension attaches uploaded files to the mail
when the finisher option ``attachUploads`` is enabled. By default only the
core ``FileUpload`` element is considered.

A custom form element that stores uploaded files can opt in by implementing
``LIA\LiaForm\Domain\Model\FormElements\AttachableUploadElementInterface``.
No finisher override and no service re-binding is needed.

Contract
========

The element value returned by the ``FormRuntime`` must be ``null``, a
``TYPO3\CMS\Extbase\Domain\Model\FileReference``, a
``TYPO3\CMS\Core\Resource\FileInterface``, or an array or
``TYPO3\CMS\Extbase\Persistence\ObjectStorage`` of those. ``null`` entries
are skipped. Any other value makes the finisher throw a ``FinisherException``,
so a broken upload element fails loudly instead of sending a mail without its
attachments.

Example
=======

..  code-block:: php
    :caption: EXT:my_extension/Classes/Domain/Model/FormElements/MyUploadElement.php

    <?php
    declare(strict_types=1);

    namespace MY\MyExtension\Domain\Model\FormElements;

    use LIA\LiaForm\Domain\Model\FormElements\AttachableUploadElementInterface;
    use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;

    final class MyUploadElement extends AbstractFormElement implements AttachableUploadElementInterface
    {
        // Wire the UploadedFileReferenceConverter in initializeFormElement()
        // as the core FileUpload element does; the finisher does the rest.
    }

Whether a mail gets attachments at all is still decided per finisher through
``attachUploads`` in the form definition.
