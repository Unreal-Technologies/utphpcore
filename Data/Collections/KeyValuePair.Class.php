<?php
namespace Utphpcore\Data\Collections;

class KeyValuePair extends Dictionary
{
    /**
     * @param array $kvp
     * @return IDictionary
     */
    public static function fromArray(array $kvp): IDictionary
    {
        $dic = new KeyValuePair();
        $dic -> aBuffer = $kvp;

        return $dic;
    }
}
