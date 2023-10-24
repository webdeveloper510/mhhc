<?php
/**
 * @license MIT
 *
 * Modified by The GravityKit Team on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityImport\Foundation\ThirdParty\Gettext\Utils;

use GravityKit\GravityImport\Foundation\ThirdParty\Gettext\Translations;

/**
 * Trait to provide the functionality of extracting headers.
 */
trait HeadersGeneratorTrait
{
    /**
     * Returns the headers as a string.
     *
     * @param Translations $translations
     *
     * @return string
     */
    protected static function generateHeaders(Translations $translations)
    {
        $headers = '';

        foreach ($translations->getHeaders() as $name => $value) {
            $headers .= sprintf("%s: %s\n", $name, $value);
        }

        return $headers;
    }
}
