<?php
namespace Utphpcore\Data;

class Stack 
{
    /**
     * @var array
     */
    private array $content = [];
    
    /**
     * @var int
     */
    private int $position = 0;
    
    /**
     * @param mixed $data
     * @return void
     */
    public function push(mixed $data): void
    {
        $this -> content[$this -> position++] = $data;
    }
    
    /**
     * @return mixed
     */
    public function pop(): mixed
    {
        $this -> position--;
        $val = $this -> content[$this -> position];
        unset($this -> content[$this -> position]);

        return $val;
    }
    
    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this -> position === 0;
    }
}
