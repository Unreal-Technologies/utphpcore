<?php
namespace Utphpcore\GUI\NoHtml\Materialize;

class Icon
{
    /**
     * @param \Utphpcore\GUI\NoHtml\IXhtml $container
     * @param Icon\Icons $icon
     * @param Icon\Alignment $alignment
     * @param Icon\Sizes $size
     */
    function __construct(\Utphpcore\GUI\NoHtml\IXhtml $container, Icon\Icons $icon, Icon\Alignment $alignment = Icon\Alignment::None, Icon\Sizes $size = Icon\Sizes::Tiny) 
    {
        $container -> add('i', function(\Utphpcore\GUI\NoHtml\IXhtml $i) use($icon, $alignment, $size)
        {
            $i -> attributes() -> set('class', trim('material-icons '.$alignment -> value.' '.$size -> value));
            $i -> text($icon -> value);
        });
    }
}
