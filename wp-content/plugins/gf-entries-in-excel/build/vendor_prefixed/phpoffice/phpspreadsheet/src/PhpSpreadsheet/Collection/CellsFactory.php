<?php
/**
 * @license MIT
 *
 * Modified by GravityKit on 25-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GFExcel\Vendor\PhpOffice\PhpSpreadsheet\Collection;

use GFExcel\Vendor\PhpOffice\PhpSpreadsheet\Settings;
use GFExcel\Vendor\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class CellsFactory
{
    /**
     * Initialise the cache storage.
     *
     * @param Worksheet $parent Enable cell caching for this worksheet
     *
     * @return Cells
     * */
    public static function getInstance(Worksheet $parent)
    {
        return new Cells($parent, Settings::getCache());
    }
}
