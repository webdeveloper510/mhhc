<?php
/**
 * @license MIT
 *
 * Modified by The GravityKit Team on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityImport\Foundation\ThirdParty\Gettext\Generators;

use GravityKit\GravityImport\Foundation\ThirdParty\Gettext\Translations;
use GravityKit\GravityImport\Foundation\ThirdParty\Gettext\Utils\DictionaryTrait;
use GravityKit\GravityImport\Foundation\ThirdParty\Gettext\Utils\CsvTrait;

class CsvDictionary extends Generator implements GeneratorInterface
{
    use DictionaryTrait;
    use CsvTrait;

    public static $options = [
        'includeHeaders' => false,
        'delimiter' => ",",
        'enclosure' => '"',
        'escape_char' => "\\"
    ];

    /**
     * {@parentDoc}.
     */
    public static function toString(Translations $translations, array $options = [])
    {
        $options += static::$options;
        $handle = fopen('php://memory', 'w');

        foreach (static::toArray($translations, $options['includeHeaders']) as $original => $translation) {
            static::fputcsv($handle, [$original, $translation], $options);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
