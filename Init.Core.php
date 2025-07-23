<?php
spl_autoload_register(function(string $className)
{
    $list = glob(__DIR__.'/../'.str_replace('\\', '/', $className.'.*.php'));
    if(count($list) === 0)
    {
        throw new \Exception('Could not find class "'.$className.'"');
    }
    $file = realpath($list[0]);
    require_once($file);
    return;
});
set_error_handler('Utphpcore\Debugging::errorHandler');
set_exception_handler('Utphpcore\Debugging::exceptionHandler');
register_shutdown_function('\Utphpcore\Core::shutdown');
\Utphpcore\Core::initialize();

if(!defined('XHTML'))
{
    $xhtml = new \Utphpcore\GUI\NoHtml\Xhtml('<!DOCTYPE html>');
    $head = $xhtml -> add('head');
    $xhtml -> add('body');

    $head -> add('script', function(\Utphpcore\GUI\NoHtml\Xhtml $script)
    {
        $script -> attributes() -> set('type', 'text/javascript');
        $script -> attributes() -> set('src', UTPHPCORE -> physicalToRelativePath(__DIR__.'/GUI/NoHhtml/Materialize/Form.js'));
    });
}