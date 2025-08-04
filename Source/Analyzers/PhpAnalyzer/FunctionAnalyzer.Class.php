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
     * @param array $tokens
     * @param string $namespace
     */
    public function __construct(array $tokens, string $namespace)
    {
        $header = $this -> header($tokens, $namespace);
        $body = $this -> body($header['body'], $namespace);
        
        $this -> name = $header['name'];
    }
    
    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this -> name;
    }
}