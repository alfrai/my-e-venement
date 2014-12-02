/**
 * Modifies a string to remove all non ASCII characters and spaces.
 */
function slugify($text)
{
    $charset = sfConfig::get('software_internals_charset');
    
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

    // trim
    $text = trim($text, '-');

    // transliterate
    if (function_exists('iconv'))
    {
        $text = iconv($charset['db'], $charset['ascii'], $text);
    }

    // lowercase
    $text = strtolower($text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    if (empty($text))
        return 'n-a';

    return $text;
}
