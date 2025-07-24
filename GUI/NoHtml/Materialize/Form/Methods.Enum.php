<?php
namespace Utphpcore\GUI\NoHtml\Materialize\Form;

enum Methods: string
{
    use \Utphpcore\Data\Collections\Enum\TInfo;
    
    case Get = 'get';
    case Post = 'post';
}