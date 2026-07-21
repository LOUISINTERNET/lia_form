<?php

declare(strict_types=1);

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\ViewHelpers\Form;

use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use TYPO3\CMS\Form\Mvc\Property\TypeConverter\PseudoFileReference;
use TYPO3\CMS\Form\Security\HashScope;

/**
 * This ViewHelper makes the specified Image object available for its
 * childNodes.
 * In case the form is redisplayed because of validation errors, a previously
 * uploaded image will be correctly used.
 *
 * Scope: frontend
 *
 * Example
 * =======
 *
 * .. code-block:: html
 *
 *      <liaform:form.uploadedResource property="{element.identifier}" as="image" id="{element.uniqueIdentifier}" class="{element.properties.elementClassAttribute}" errorClass="{element.properties.elementErrorClassAttribute}" additionalAttributes="{formvh:translateElementProperty(element: element, property: 'fluidAdditionalAttributes')}" accept="{element.properties.allowedMimeTypes}" multiple="1">
 *          <f:if condition="{image}">
 *              <div id="{element.uniqueIdentifier}-preview">
 *                  <a href="{f:uri.image(image: image, maxWidth: element.properties.imageLinkMaxWidth)}" class="{element.properties.elementClassAttribute}" >
 *                      <f:image image="{image}" maxWidth="{element.properties.imageMaxWidth}" maxHeight="{element.properties.imageMaxHeight}" alt="{formvh:translateElementProperty(element: element, property: 'altText')}" />
 *                  </a>
 *              </div>
 *          </f:if>
 *      </liaform:form.uploadedResource>
 */
class UploadedResourceViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    protected HashService $hashService;
    protected PropertyMapper $propertyMapper;

    public function injectHashService(HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    public function injectPropertyMapper(PropertyMapper $propertyMapper)
    {
        $this->propertyMapper = $propertyMapper;
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('as', 'string', 'The name of the variable.');
        $this->registerArgument('accept', 'array', 'Values for the accept attribute', false, []);
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this ViewHelper', false, 'f3-form-error');
    }

    public function render(): string
    {
        $output = '';

        $name = $this->getName();
        $as = $this->arguments['as'];
        $accept = $this->arguments['accept'];
        $resource = $this->getUploadedResource();

        if (!empty($accept)) {
            $this->tag->addAttribute('accept', implode(',', $accept));
        }

        if ($resource !== null) {
            if (is_array($resource) && isset($resource[0])) {
                foreach ($resource as $key => $item) {
                    if ($item instanceof PseudoFileReference) {
                        $resourcePointerIdAttribute = '';
                        if (isset($this->additionalArguments['id'])) {
                            $resourcePointerIdAttribute = ' id="' . htmlspecialchars((string)$this->additionalArguments['id']) . '-file-reference ' . $key . '"';
                        }

                        $resourcePointerValue = $item->getUid();
                        if ($resourcePointerValue === null) {
                            // Newly created file reference which is not persisted yet.
                            // Use the file UID instead, but prefix it with "file:" to communicate this to the type converter
                            $resourcePointerValue = 'file:' . $item->getOriginalResource()->getOriginalFile()->getUid();
                        }

                        $output .= '<input type="hidden" data-value="' . $item->getOriginalResource()->getOriginalFile()->getName() . '" name="' . htmlspecialchars($this->getName()) . '[submittedFile][resourcePointer][]" value="' . htmlspecialchars($this->hashService->appendHmac((string)$resourcePointerValue, HashScope::ResourcePointer->prefix())) . '"' . $resourcePointerIdAttribute . ' />';
                    }
                }
            } elseif ($resource instanceof PseudoFileReference) {
                $resourcePointerIdAttribute = '';
                if (isset($this->additionalArguments['id'])) {
                    $resourcePointerIdAttribute = ' id="' . htmlspecialchars((string)$this->additionalArguments['id']) . '-file-reference"';
                }

                $resourcePointerValue = $resource->getUid();
                if ($resourcePointerValue === null) {
                    // Newly created file reference which is not persisted yet.
                    // Use the file UID instead, but prefix it with "file:" to communicate this to the type converter
                    $resourcePointerValue = 'file:' . $resource->getOriginalResource()->getOriginalFile()->getUid();
                }

                $output .= '<input type="hidden" name="' . htmlspecialchars($this->getName()) . '[submittedFile][resourcePointer]" value="' . htmlspecialchars($this->hashService->appendHmac((string)$resourcePointerValue, HashScope::ResourcePointer->prefix())) . '"' . $resourcePointerIdAttribute . ' />';
            }

            $this->templateVariableContainer->add($as, $resource);
            $output .= $this->renderChildren();
            $this->templateVariableContainer->remove($as);
        }

        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $fieldName) {
            $this->registerFieldNameForFormTokenGeneration($name . '[' . $fieldName . ']');
        }

        $this->tag->addAttribute('type', 'file');

        if (isset($this->additionalArguments['multiple'])) {
            $this->tag->addAttribute('name', $name . '[]');
        } else {
            $this->tag->addAttribute('name', $name);
        }

        $this->setErrorClassAttribute();

        return $output . $this->tag->render();
    }

    /**
     * Return a previously uploaded resource.
     * Return NULL if errors occurred during property mapping for this property.
     */
    protected function getUploadedResource(): array|PseudoFileReference|null
    {
        $resource = $this->getValueAttribute();

        if ($this->getMappingResultsForProperty()->hasErrors()) {
            if ($resource) {
                return array_slice($resource, 0, count($resource) - 1);
            }

            return null;
        }

        if (is_array($resource)) {
            return $resource;
        }

        if ($resource instanceof PseudoFileReference) {
            return $resource;
        }

        return $this->propertyMapper->convert($resource, PseudoFileReference::class);
    }
}
