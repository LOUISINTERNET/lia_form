<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

ExtensionManagementUtility::addStaticFile('lia_form', 'Configuration/TypoScript', 'LIA Form - Load after TYPO3 Form');
