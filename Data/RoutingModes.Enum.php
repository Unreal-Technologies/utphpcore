<?php
namespace Utphpcore\Data;

enum RoutingModes 
{
    use \Utphpcore\Data\Collections\Enum\TInfo;
    
    case Data;
    case Page;
    case Modal;
}
