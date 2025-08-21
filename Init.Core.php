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
    
    $xhtml -> get('body', function(Utphpcore\GUI\NoHtml\Xhtml $body)
    {
        $modal = $body -> add('div@#utphpcoremodal&.modal');
        $modal -> add('div@.modal-content') -> text('--content--');
        $modal -> add('div@.modal-footer') -> text('--footer--');
    });
    
    \Utphpcore\Core::register_shutdown_body('modal', function(Utphpcore\GUI\NoHtml\Xhtml $body)
    {
        $body -> javascript() -> text('function modal(url)
        {
            var modal = document.getElementById(\'utphpcoremodal\');
            var instance = M.Modal.getInstance(modal);
            var content = modal.getElementsByClassName(\'modal-content\')[0];
            var footer = modal.getElementsByClassName(\'modal-footer\')[0];

            Xhr.modal(url, function(raw)
            {
                let temp = document.createElement(\'div\');
                temp.innerHTML = raw;
                
                let scripts = temp.getElementsByTagName(\'script\');
                for(var i=0; i<scripts.length; i++)
                {
                    let script = document.createElement(\'script\');
                    script.textContent = scripts[i].textContent;

                    temp.remove(scripts[i]);
                    document.body.appendChild(script);
                }

                content.innerHTML = temp.innerHTML;
                instance.open();
            });
            modal.removeChild(footer);
        }
        $(document).ready(function()
        {
            $(\'.modal\').modal();
        });');
    });
    
    Utphpcore\Data\Cache::set(\Utphpcore\Data\CacheTypes::Memory, \Utphpcore\Core::Xhtml, $xhtml);
    
    new Utphpcore\GUI\ToDo('Add register page', 'Put commands behind authentication', 'Put admin menu behind authentication', 'Add user password change', 'User icon selection / upload');
}
else if($route -> mode() === \Utphpcore\Data\RoutingModes::Modal)
{
    $xhtml = new \Utphpcore\GUI\NoHtml\Xhtml('<!DOCTYPE html>');
    $xhtml -> add('head');
    $xhtml -> add('body/div@.container');
    
    Utphpcore\Data\Cache::set(\Utphpcore\Data\CacheTypes::Memory, \Utphpcore\Core::Xhtml, $xhtml);
}

require_once($route -> file() -> path());
?>