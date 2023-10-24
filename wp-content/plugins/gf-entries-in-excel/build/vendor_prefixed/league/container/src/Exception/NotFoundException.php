<?php
/**
 * @license MIT
 *
 * Modified by GravityKit on 25-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GFExcel\Vendor\League\Container\Exception;

use GFExcel\Vendor\Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
}
