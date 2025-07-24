<?php
namespace Utphpcore\GUI\NoHtml\Materialize\Form;

class SelectOptions
{
    /**
     * @var array
     */
    private array $aData;
    
    /**
     */
    public function __construct()
    {
        $this -> aData = [];
    }
    
    /**
     * @param string $text
     * @param string $value
     * @param bool $isSelected
     * @return void
     */
    public function set(string $text, string $value, bool $isSelected): void
    {
        $this -> aData[] = [
            'text' => $text,
            'value' => $value,
            'selected' => $isSelected
        ];
    }
    
    /**
     * @return array
     */
    public function data(): array
    {
        return $this -> aData;
    }
}