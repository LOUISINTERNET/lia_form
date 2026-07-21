<?php

use LIA\LiaForm\Preview\FormPreviewRenderer;

defined('TYPO3') || die();

// Use the extended preview renderer to show configured finishers in the
// page module preview. Loaded after EXT:form due to extension dependency.
$GLOBALS['TCA']['tt_content']['types']['form_formframework']['previewRenderer']
    = FormPreviewRenderer::class;
