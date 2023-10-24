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
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 */
class WktDumper implements DumperInterface
{
    /**
     * @param ResultInterface $result
     *
     * @return string
     */
    public function dump(ResultInterface $result)
    {
        return sprintf('POINT(%F %F)', $result->getLongitude(), $result->getLatitude());
    }
}
