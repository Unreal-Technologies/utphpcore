<?php
namespace Utphpcore\Commands;

class Clear 
{
    /**
     */
    function __construct()
    {
        $_SESSION = [];
        \Utphpcore\Data\Cache::create(\Utphpcore\Data\CacheTypes::Session, \Utphpcore\Core::Message, function()
        {
            return new \Utphpcore\Core\Messages();
        });
        \Utphpcore\Data\Cache::get(\Utphpcore\Core::Message) -> push('Cache Cleared');
        header('Location: '.(isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '/'));
        exit;
    }
}
