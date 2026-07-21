<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'LIA Form',
    'description' => 'Extends the core form extension.',
    'category' => 'plugin',
    'author' => 'LOUIS TYPO3 Developers',
    'author_company' => 'LOUIS INTERNET',
    'author_email' => 'devs@louis.info',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'form' => '13.4.0-13.4.99',
            'php' => '8.2.0-8.4.99',
        ],
    ],
];
