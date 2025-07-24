<?php
namespace Utphpcore\GUI\NoHtml\Materialize\Form;

enum InputTypes: string
{
    use \Utphpcore\Data\Collections\Enum\TInfo;
    
    case Text = 'text';
    case Password = 'password';
    case YesNo = 'yes-no';
    case Number = 'number';
    case Select = 'select';
}