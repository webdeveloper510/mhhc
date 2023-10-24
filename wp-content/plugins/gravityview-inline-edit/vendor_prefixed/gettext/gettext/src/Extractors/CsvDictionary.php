<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 19-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityEdit\Foundation\ThirdParty\Gettext\Extractors;

use GravityKit\GravityEdit\Foundation\ThirdParty\Gettext\Translations;
use GravityKit\GravityEdit\Foundation\ThirdParty\Gettext\Utils\HeadersExtractorTrait;
use GravityKit\GravityEdit\Foundation\ThirdParty\Gettext\Utils\CsvTrait;

/**
 * Class to get gettext strings from csv.
 */
class CsvDictionary extends Extractor implements ExtractorInterface
{
    use HeadersExtractorTrait;
    use CsvTrait;

    public static $options = [
        'delimiter' => ",",
        'enclosure' => '"',
        'escape_char' => "\\"
    ];

    /**
     * {@inheritdoc}
     */
    public static function fromString($string, Translations $translations, array $options = [])
    {
        $options += static::$options;
        $handle = fopen('php://memory', 'w');

        fputs($handle, $string);
        rewind($handle);

        while ($row = static::fgetcsv($handle, $options)) {
            list($original, $translation) = $row + ['', ''];

            if ($original === '') {
                static::extractHeaders($translation, $translations);
                continue;
            }

            $translations->insert(null, $original)->setTranslation($translation);
        }

        fclose($handle);
    }
}
