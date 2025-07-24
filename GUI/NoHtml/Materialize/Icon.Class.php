<?php
namespace Utphpcore\GUI\NoHtml\Materialize;

class Icon
{
    /**
     * @param \Utphpcore\GUI\NoHtml\IXhtml $container
     * @param string $icon
     */
    function __construct(\Utphpcore\GUI\NoHtml\IXhtml $container, string $icon) 
    {
        $container -> add('i', function(\Utphpcore\GUI\NoHtml\IXhtml $i) use($icon)
        {
            $i -> attributes() -> set('class', 'material-icons right');
            $i -> text($icon);
        });
    }
}
