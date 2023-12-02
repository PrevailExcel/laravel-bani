<?php

namespace PrevailExcel\Bani;

use Exception;

/*
 * This file is part of the Laravel Bani package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class IsNullException extends Exception
{
    public static function make(): self
    {
        return new static('The signature is invalid.');
    }
}