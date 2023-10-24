<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 *
 * Modified using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityMaps\Geocoder\Dumper;

use GravityKit\GravityMaps\Geocoder\Result\ResultInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface DumperInterface
{
    /**
     * Dump a `ResultInterface` object as a string representation of
     * the implemented format.
     *
     * @param ResultInterface $result A result object
     *
     * @return string
     */
    public function dump(ResultInterface $result);
}
