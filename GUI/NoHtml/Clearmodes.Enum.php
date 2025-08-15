<?php
namespace Utphpcore\GUI\NoHtml;

enum Clearmodes 
{
    use \Utphpcore\Data\Collections\Enum\TInfo;
    
    case Both;
    case Attributes;
    case Children;
}
