<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace LIA\LiaForm\Domain\Model\FormElements;

use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

/**
 * Marker interface for form elements whose value holds uploaded files.
 *
 * The EmailFinisher attaches the value of every element implementing this
 * interface when the finisher option "attachUploads" is enabled - exactly like
 * the core FileUpload element, without the element having to extend it.
 *
 * Contract: the element value returned by the FormRuntime must be null, a
 * TYPO3\CMS\Extbase\Domain\Model\FileReference, a
 * TYPO3\CMS\Core\Resource\FileInterface, or an array or
 * TYPO3\CMS\Extbase\Persistence\ObjectStorage of those. Null entries are
 * skipped; any other value makes the finisher throw a FinisherException.
 *
 * @author LOUIS INTERNET <devs@louis.info>
 */
interface AttachableUploadElementInterface extends FormElementInterface {}
