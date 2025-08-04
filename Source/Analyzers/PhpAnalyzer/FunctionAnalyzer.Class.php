<?php
namespace Utphpcore\Source\Analyzers\PhpAnalyzer;

class FunctionAnalyzer 
{
    use TBasics;
    
    /**
     * @var string|null
     */
    private ?string $name = null;

    /**
     * @var string|null
     */
    private ?string $raw = null;
    
    /**
     * @var bool
     */
    private bool $isPrivate = false;
    
    /** 
     * @param array $tokens
     * @param string $namespace
     */
    public function __construct(array $tokens, string $namespace)
    {
        $header = $this -> header($tokens, $namespace);
        $body = $this -> body($header['body'], $namespace);
        
        $this -> name = $header['name'];
        $this -> raw = $header['raw'];
        $this -> isPrivate = $header['isPrivate'];
    }
    
    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this -> isPrivate;
    }
    
    /**
     * @return string|null
     */
    public function raw(): ?string
    {
        return $this -> raw;
    }
    
    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this -> name;
    }
}