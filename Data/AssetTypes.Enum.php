<?php
namespace Utphpcore\Data;
require_once('Collections/Enum/TInfo.Trait.php');

enum AssetTypes : int
{
    use \Utphpcore\Data\Collections\Enum\TInfo;
    
    case Cache = 0;
    case Temp  = 1;
    case Core  = 2;
    case App   = 3;
    case All   = -1;
}
