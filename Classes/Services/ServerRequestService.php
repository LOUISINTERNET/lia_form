<?php

declare(strict_types=1);

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Services;

use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * This Services provide access to the ServerRequest from the GLOBALS.
 */
class ServerRequestService
{
    /**
     * Return the ServerRequest from the globals
     */
    public static function getServerRequest(): ServerRequest
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
