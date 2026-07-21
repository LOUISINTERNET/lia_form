<?php

/**
 * Extension Manager/Repository config file for ext:lia_form.
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'LIA Form',
    'description' => 'Extends the core form extension.',
    'category' => 'plugin',
    'author' => 'LOUIS TYPO3 Developers',
    'author_company' => 'LOUIS INTERNET',
    'author_email' => 'devs@louis.info',
    'state' => 'stable',
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.99.99',
            'form' => '14.3.0-14.99.99',
            'php' => '8.2.0-8.99.99',
        ],
    ],
];
