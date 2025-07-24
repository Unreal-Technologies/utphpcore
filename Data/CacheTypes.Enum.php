<?php
namespace Utphpcore\Data;

enum CacheTypes
{
    use \Utphpcore\Data\Collections\Enum\TInfo;
    
    case Memory;
    case Session;
}
