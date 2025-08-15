<?php
namespace Utphpcore\GUI\NoHtml\Materialize\Tooltip;

enum Positions : string
{
    use \Utphpcore\Data\Collections\Enum\TInfo;
    
    case Top = 'top';
    case Bottom = 'bottom';
    case Left = 'left';
    case Right = 'right';
}
