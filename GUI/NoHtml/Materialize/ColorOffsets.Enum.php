<?php
namespace Utphpcore\GUI\NoHtml\Materialize;

enum ColorOffsets : string
{
    use \Utphpcore\Data\Collections\Enum\TInfo;
    
    case Lighten5 = 'lighten-5';
    case Lighten4 = 'lighten-4';
    case Lighten3 = 'lighten-3';
    case Lighten2 = 'lighten-2';
    case Lighten1 = 'lighten-1';
    case Darken1 = 'darken-1';
    case Darken2 = 'darken-2';
    case Darken3 = 'darken-3';
    case Darken4 = 'darken-4';
    case Accent1 = 'accent-1';
    case Accent2 = 'accent-2';
    case Accent3 = 'accent-3';
    case Accent4 = 'accent-4';
}
