<?php
namespace Utphpcore\Data;

class Cache 
{
    /**
     * @var array
     */
    private static array $aMemory = [];

    /**
     * @param string $key
     * @return mixed
     */
    public static function get(string $key): mixed
    {
        $list = [CacheTypes::Memory, CacheTypes::Session];
        
        foreach($list as $cache)
        {
            $result = null;
            switch($cache)
            {
                case CacheTypes::Memory:
                    $result = isset(self::$aMemory[$key]) ? self::$aMemory[$key] : null;
                    break;
                case CacheTypes::Session:
                    $result = isset($_SESSION['aCache'][$key]) ? $_SESSION['aCache'][$key] : null;
                    break;
            }
            
            if($result !== null)
            {
                return $result;
            }
        }
        
        return null;
    }
    
    /** 
     * @param CacheTypes $cache
     * @param string $key
     * @param \Closure $callback
     * @return void
     */
    public static function create(CacheTypes $cache, string $key, \Closure $callback): void
    {
        switch($cache)
        {
            case CacheTypes::Memory:
                if(!isset(self::$aMemory[$key]))
                {
                    self::$aMemory[$key] = $callback();
                }
                break;
            case CacheTypes::Session:
                if(!isset($_SESSION['aCache'][$key]))
                {
                    $_SESSION['aCache'][$key] = $callback();
                }
                break;
        }
    }
    
    /**
     * @param CacheTypes $cache
     * @param string $key
     * @param mixed $mValue
     * @return void
     */
    public static function set(CacheTypes $cache, string $key, mixed $mValue): void
    {
        switch($cache)
        {
            case CacheTypes::Memory:
                self::$aMemory[$key] = $mValue;
                break;
            case CacheTypes::Session:
                $_SESSION['aCache'][$key] = $mValue;
                break;
        }
    }
    
    /**
     * @param CacheTypes $cache
     * @param string $key
     * @return bool
     */
    public static function clear(CacheTypes $cache, string $key): bool
    {
        switch($cache)
        {
            case CacheTypes::Memory:
                if(isset(self::$aMemory[$key]))
                {
                    unset(self::$aMemory[$key]);
                    return true;
                }
                break;
            case CacheTypes::Session:
                if(isset($_SESSION['aCache'][$key]))
                {
                    unset($_SESSION['aCache'][$key]);
                    return true;
                }
                break;
        }
        return false;
    }
    
    /**
     * @param CacheTypes $cache
     * @return mixed
     */
    public static function all(CacheTypes $cache): mixed
    {
        switch($cache)
        {
            case CacheTypes::Memory:
                return self::$aMemory;
            case CacheTypes::Session:
                return isset($_SESSION['aCache']) ? $_SESSION['aCache'] : null;
        }
    }
}
