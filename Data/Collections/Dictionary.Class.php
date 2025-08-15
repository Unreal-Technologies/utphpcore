<?php

namespace Utphpcore\Data\Collections;

class Dictionary implements IDictionary, \ArrayAccess
{
    /**
     * @var array
     */
    protected array $aBuffer = [];

    /**
     * @param mixed $index
     * @return void
     */
    public function offsetUnset(mixed $index): void {}
    
    /**
     * @param mixed $index
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $index, mixed $value): void
    {  
        if(isset($this -> aBuffer[$index])) 
        {
            unset($this -> aBuffer[$index]);
        }

        $u = &$this -> aBuffer[$index];
        if(is_array($value)) 
        {
            $u = new ArrayAccessImpl();
            foreach($value as $idx => $e)
            {
                $u[$idx] = $e;
            }
        } 
        else
        {
            $u=$value;
        }
    }
    
    /**
     * @param mixed $index
     * @return mixed
     */
    public function offsetGet(mixed $index): mixed
    {
        if(!isset($this -> aBuffer[$index]))
        {
            $this -> aBuffer[$index] = new ArrayAccessImpl();
        }

        return $this -> aBuffer[$index];
    }
    
    /**
     * @param mixed $index
     * @return bool
     */
    public function offsetExists(mixed $index): bool
    {
        if(isset($this -> aBuffer[$index])) 
        {
            if($this -> aBuffer[$index] instanceof ArrayAccessImpl) 
            {
                if(count($this -> aBuffer[$index] -> data) > 0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            } 
            else
            {
                return true;
            }
        } 
        else
        {
            return false;
        }
    }
    
    /**
     * @param array $kvp
     * @return IDictionary
     */
    public static function fromArray(array $kvp): IDictionary
    {
        $dic = new Dictionary();
        $dic['X'] = 'Y';
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
