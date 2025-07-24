<?php
namespace Utphpcore\GUI\NoHtml\FontAwesome;

class Icon
{
    /**
     * @param \Utphpcore\GUI\NoHtml\IXhtml $container
     * @param string $icon
     */
    public function __construct(\Utphpcore\GUI\NoHtml\IXhtml $container, string $icon) 
    {
        $container -> add('i', function(\Utphpcore\GUI\NoHtml\IXhtml $i) use($icon)
        {
            $i -> attributes() -> set('class', $icon);
        });
    }
}
