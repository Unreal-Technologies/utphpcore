<?php
namespace Utphpcore\Data;

enum RoutingModes 
{
    use \Utphpcore\Data\Collections\Enum\TInfo;
    
    case Raw;
    case Full;
}
