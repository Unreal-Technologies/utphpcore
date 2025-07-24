<?php
namespace Utphpcore\GUI\NoHTML\FontAwesome;

class Icon
{
    /**
     * @param \Utphpcore\GUI\NoHTML\IXhtml $container
     * @param string $icon
     */
    public function __construct(\Utphpcore\GUI\NoHTML\IXhtml $container, string $icon) 
    {
        $container -> add('i', function(\Utphpcore\GUI\NoHTML\IXhtml $i) use($icon)
        {
            $i -> attributes() -> set('class', $icon);
        });
    }
}
