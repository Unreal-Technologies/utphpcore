<?php
$root = realpath(__DIR__.'/../');
if(file_exists($root.'/__CACHE__/class.map'))
{
    require_once($root.'/__CACHE__/class.map');
}
else
{
    require_once('Data/Cache.Class.php');
    require_once('Core.Class.php');
    require_once('Debugging.Class.php');
}

spl_autoload_register(function(string $className)
{
    $backtrace = debug_backtrace()[0];
    if(!isset($backtrace['file']))
    {
        $backtrace = debug_backtrace()[1];
    }
    
    echo 'Autoloading object "'.$className.'" used in "'.$backtrace['file'].':'.$backtrace['line'].'"<br />';
    
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

$route = Utphpcore\Data\Cache::get(\Utphpcore\Core::Route); /* @var $route Utphpcore\Data\Route|null */
if($route === null)
{
    throw new \Exception('No route found for: '.$_SERVER['REDIRECT_URL']);
}
else if($route -> mode() === \Utphpcore\Data\RoutingModes::Page)
{
    $configuration = Utphpcore\Data\Cache::get(\Utphpcore\Core::Configuration); /* @var $configuration \Utphpcore\Data\Configuration */

    $xhtml = new \Utphpcore\GUI\NoHtml\Xhtml('<!DOCTYPE html>');
    $head = $xhtml -> add('head');
    $head -> add('title') -> text($configuration -> get('App/Application/Title'));
    $xhtml -> add('body/div@.container');
}

require_once($route -> file() -> path());

new Utphpcore\GUI\ToDo('Authentication Login & register', 'Main menu', 'Put commands behind authentication', 'Put admin menu behind authentication', 'Add option to disable authentication (this wil also disable admin tools)', 'Add option to remove main menu', 'Add option to only login not register');