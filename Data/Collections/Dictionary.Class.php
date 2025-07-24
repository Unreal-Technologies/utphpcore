<?php

namespace Utphpcore\Data\Collections;

class Dictionary implements IDictionary
{
    /**
     * @var array
     */
    protected array $aBuffer = [];

    /**
     * @param array $kvp
     * @return Dictionary
     */
    public static function fromArray(array $kvp): Dictionary
    {
        $dic = new Dictionary();
        $dic -> aBuffer = $kvp;

        return $dic;
    }

    /**
     * @param  mixed $key
     * @param  mixed $value
     * @param  bool  $setAsArray
     * @return bool
     */
    public function add(mixed $key, mixed $value, bool $setAsArray = false): bool
    {
        if (isset($this -> aBuffer[$key])) {
            return false;
        }
        $this -> aBuffer[$key] = $setAsArray ? [$value] : $value;
        return true;
    }

    /**
     * @param  mixed $key
     * @return mixed
     */
    public function get(mixed $key): mixed
    {
        if (isset($this -> aBuffer[$key])) {
            return $this -> aBuffer[$key];
        }
        return null;
    }

    /**
     * @param  mixed $key
     * @return bool
     */
    public function remove(mixed $key): bool
    {
        if (isset($this -> aBuffer[$key])) {
            unset($this -> aBuffer[$key]);
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this -> aBuffer;
    }

    /**
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this -> aBuffer);
    }

    /**
     * @return array
     */
    public function values(): array
    {
        return array_values($this -> aBuffer);
    }
}
