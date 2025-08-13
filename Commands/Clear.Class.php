<?php
namespace Utphpcore\Commands;

class Clear 
{
    function __construct()
    {
        $_SESSION = [];
        \Utphpcore\Data\Cache::set(\Utphpcore\Data\CacheTypes::Session, 'Message', 'Cache Cleared');
        header('Location: '.$_SERVER['REDIRECT_URL']);
        exit;
    }
}
