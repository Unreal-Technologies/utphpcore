<?php
namespace Utphpcore\GUI\NoHtml\Materialize;

class Tooltip 
{
    /**
     * @param \Utphpcore\GUI\NoHtml\Xhtml $container
     * @param string $text
     * @param Tooltip\Positions $position
     */
    function __construct(\Utphpcore\GUI\NoHtml\Xhtml $container, string $text, Tooltip\Positions $position = Tooltip\Positions::Bottom)
    {
        $attributes = $container -> attributes(); /* @var $attributes \Utphpcore\GUI\NoHTML\Attributes */
        $class = $attributes -> get('class');
        
        if(!stristr($class, 'tooltipped'))
        {
            $attributes -> set('class', $class.' tooltipped');
        }
        $container -> attributes() -> set('data-position', $position -> value);
        $container -> attributes() -> set('data-tooltip', $text);
        
        \Utphpcore\Core::register_shutdown_body(__CLASS__, function(\Utphpcore\GUI\NoHtml\Xhtml $body)
        {
            $body -> javascript() -> text('document.addEventListener(\'DOMContentLoaded\', function() '
                . '{'
                    . 'var elems = document.querySelectorAll(\'.tooltipped\');'
                    . 'var instances = M.Tooltip.init(elems, {});'
                . '});'
            );
        });
    }
}
