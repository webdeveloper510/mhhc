<?php
/**
 * @license MIT
 *
 * Modified by The GravityKit Team on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityImport\Foundation\ThirdParty\Gettext\Extractors;

use GravityKit\GravityImport\Foundation\ThirdParty\Gettext\Translations;

interface ExtractorInterface
{
    /**
     * Extract the translations from a file.
     *
     * @param array|string $file         A path of a file or files
     * @param Translations $translations The translations instance to append the new translations.
     * @param array        $options
     */
    public static function fromFile($file, Translations $translations, array $options = []);

    /**
     * Parses a string and append the translations found in the Translations instance.
     *
     * @param string       $string
     * @param Translations $translations
     * @param array        $options
     */
    public static function fromString($string, Translations $translations, array $options = []);
}
