<?php
namespace Utphpcore\GUI\NoHtml\Materialize\Form;

class Options
{
    /**
     * @var \Utphpcore\GUI\NoHtml\Materialize\Columns
     */
    private \Utphpcore\GUI\NoHtml\Materialize\Columns $oSize;
    
    /**
     * @var \Utphpcore\GUI\NoHtml\Materialize\Columns|null
     */
    private ?\Utphpcore\GUI\NoHtml\Materialize\Columns $oOffset;
    
    /**
     * @var int|null
     */
    private ?int $iMin = null;
    
    /**
     * @var int|null
     */
    private ?int $iMax = null;
    
    /**
     * @var float|null
     */
    private ?float $fStep = null;
    
    /**
     * @var SelectOptions|null
     */
    private ?SelectOptions $oOptions = null;
    
    /**
     */
    protected function __construct()
    {
        $this -> oSize = \Utphpcore\GUI\NoHtml\Materialize\Columns::S12;
        $this -> oOffset = null;
    }
    
    /**
     * @param SelectOptions|null $value
     * @param bool $clear
     * @return SelectOptions|null
     */
    public function options(?SelectOptions $value = null, bool $clear = false): ?SelectOptions
    {
        if($value !== null || $clear)
        {
            $this -> oOptions = $value;
        }
        return $this -> oOptions;
    }
    
    /**
     * @param int|null $value
     * @param bool $clear
     * @return int|null
     */
    public function min(?int $value = null, bool $clear = false): ?int
    {
        if($value !== null || $clear)
        {
            $this -> iMin = $value;
        }
        return $this -> iMin;
    }
    
    /**
     * @param int|null $value
     * @param bool $clear
     * @return int|null
     */
    public function max(?int $value=null, bool $clear = false): ?int
    {
        if($value !== null || $clear)
        {
            $this -> iMax = $value;
        }
        return $this -> iMax;
    }
    
    /**
     * @param int|null $value
     * @param bool $clear
     * @return int|null
     */
    public function step(?float $value=null, bool $clear = false): ?float
    {
        if($value !== null || $clear)
        {
            $this -> fStep = $value;
        }
        return $this -> fStep;
    }
    
    /**
     * @param \Utphpcore\GUI\NoHtml\Materialize\Columns $value
     * @param bool $clear
     * @return \Utphpcore\GUI\NoHtml\Materialize\Columns|null
     */
    public function offset(\Utphpcore\GUI\NoHtml\Materialize\Columns $value = null, bool $clear = false): ?\Utphpcore\GUI\NoHtml\Materialize\Columns
    {
        if($value !== null || $clear)
        {
            $this -> oOffset = $value;
        }
        return $this -> oOffset;
    }
    
    /**
     * @param \Utphpcore\GUI\NoHtml\Materialize\Columns $value
     * @return \Utphpcore\GUI\NoHtml\Materialize\Columns
     */
    public function size(\Utphpcore\GUI\NoHtml\Materialize\Columns $value = null): \Utphpcore\GUI\NoHtml\Materialize\Columns
    {
        if($value !== null)
        {
            $this -> oSize = $value;
        }
        return $this -> oSize;
    }
    
    /**
     * @return Options
     */
    public static function Default(): Options
    {
        return new Options();
    }
}