<?php
namespace Utphpcore\GUI\NoHtml\Materialize;

class Color 
{
    /**
     * @var Colors|null
     */
    private ?Colors $color = null;
    
    /**
     * @var ColorOffsets|null
     */
    private ?ColorOffsets $offset = null;
    
    /**
     * @param Colors $color
     * @param ColorOffsets $offset
     */
    function __construct(Colors $color, ColorOffsets $offset = null)
    {
        $this -> color = $color;
        $this -> offset = $offset;
    }
    
    /**
     * @return string
     */
    function __toString(): string 
    {
        return $this -> color -> value.($this -> offset === null ? null : ' '.$this -> offset -> value);
    }
}
