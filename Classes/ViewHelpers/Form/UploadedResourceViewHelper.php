<?php

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
 * ViewHelper for uploaded file resources.
 *
 * Makes the specified Image object available for its childNodes.
 * In case the form is redisplayed because of validation errors,
 * a previously uploaded image will be correctly used.
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
     * HTML tag name for the file input element.
     *
     * @var string
     */
    protected $tagName = 'input';

    /**
     * Constructor.
     *
     * @param HashService $hashService Hash service for securing resource pointers
     * @param PropertyMapper $propertyMapper Property mapper for converting uploaded resources
     */
    public function __construct(
        private readonly HashService $hashService,
        private readonly PropertyMapper $propertyMapper
    ) {
        parent::__construct();
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('as', 'string', 'The name of the variable.');
        $this->registerArgument('accept', 'array', 'Values for the accept attribute', false, []);
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this ViewHelper', false, 'f3-form-error');
    }

    /**
     * Render the view helper.
     *
     * @return string The rendered HTML
     */
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
            $output .= $this->renderResourcePointers($resource, $as);
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
     * Render resource pointers for previously uploaded files.
     *
     * Note: Union type in signature is required to handle both single and multiple file uploads.
     * PHPStan and static analysis should use the @param annotation for detailed type info.
     *
     * @param array<int, PseudoFileReference>|PseudoFileReference $resource The uploaded resource(s)
     * @param string $as Variable name for template
     * @return string The rendered hidden inputs
     */
    private function renderResourcePointers(array|PseudoFileReference $resource, string $as): string
    {
        $output = '';

        if (is_array($resource) && isset($resource[0])) {
            foreach ($resource as $key => $item) {
                $output .= $this->renderSingleResourcePointer($item, $key);
            }
        } elseif ($resource instanceof PseudoFileReference) {
            $output .= $this->renderSingleResourcePointer($resource, null);
        }

        $this->templateVariableContainer?->add($as, $resource);
        $output .= $this->renderChildren();
        $this->templateVariableContainer?->remove($as);

        return $output;
    }

    /**
     * Render a single resource pointer hidden input.
     *
     * @param PseudoFileReference $item The file reference
     * @param int|null $key Array key for multiple files
     * @return string The rendered hidden input
     */
    private function renderSingleResourcePointer(PseudoFileReference $item, ?int $key): string
    {
        $resourcePointerIdAttribute = '';
        if (isset($this->additionalArguments['id'])) {
            $suffix = $key !== null ? '-file-reference ' . $key : '-file-reference';
            $resourcePointerIdAttribute = ' id="' . htmlspecialchars((string)$this->additionalArguments['id']) . $suffix . '"';
        }

        $resourcePointerValue = $item->getUid();
        if ($resourcePointerValue === null) {
            // Newly created file reference which is not persisted yet.
            // Use the file UID instead, but prefix it with "file:" to communicate this to the type converter.
            $resourcePointerValue = 'file:' . $item->getOriginalResource()->getOriginalFile()->getUid();
        }

        $hashedValue = $this->hashService->appendHmac((string)$resourcePointerValue, HashScope::ResourcePointer->prefix());
        $nameAttribute = $key !== null
            ? htmlspecialchars($this->getName()) . '[submittedFile][resourcePointer][]'
            : htmlspecialchars($this->getName()) . '[submittedFile][resourcePointer]';

        $dataValue = $item->getOriginalResource()->getOriginalFile()->getName();

        return '<input type="hidden" data-value="' . htmlspecialchars((string)$dataValue, ENT_QUOTES, 'UTF-8') . '" name="' . $nameAttribute . '" value="' . htmlspecialchars($hashedValue) . '"' . $resourcePointerIdAttribute . ' />';
    }

    /**
     * Return a previously uploaded resource.
     *
     * Returns NULL if errors occurred during property mapping for this property.
     * Union type is required to handle single files, multiple files, or no upload.
     *
     * @return array<int, PseudoFileReference>|PseudoFileReference|null
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
