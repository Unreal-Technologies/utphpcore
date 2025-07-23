<?php
spl_autoload_register(function(string $className)
{
    $list = glob(__DIR__.'/../'.str_replace('\\', '/', $className.'.*.php'));
    if(count($list) === 0)
    {
        \utphpcore\Debugging::trace();
        throw new \Exception('Could not find class "'.$className.'"');
    }
    $file = realpath($list[0]);
    require_once($file);
    return;
});
set_error_handler('Utphpcore\Debugging::errorHandler');
set_exception_handler('Utphpcore\Debugging::exceptionHandler');