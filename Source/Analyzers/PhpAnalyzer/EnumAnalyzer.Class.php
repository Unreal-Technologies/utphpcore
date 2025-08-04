<?php
namespace Utphpcore\Source\Analyzers\PhpAnalyzer;

class EnumAnalyzer 
{
    use TBasics;
    
    /**
     * @var string|null
     */
    private ?string $name = null;
    
    /**
     * @var string|null
     */
    private ?string $extends = null;
    
    /**
     * @var string[]
     */
    private array $uses = [];

    /**
     * @param array $tokens
     * @param string $namespace
     */
    public function __construct(array $tokens, string $namespace)
    {
        $header = $this -> header($tokens, $namespace);
        $body = $this -> body($header['body'], $namespace);
        
        $this -> name = $header['name'];
        $this -> extends = $header['extends'];
        $this -> uses = $body['uses'];
    }
    
    /**
     * @return string[]
     */
    public function uses(): array
    {
        return $this -> uses;
    }

    /**
     * @return string|null
     */
    public function extends(): ?string
    {
        return $this -> extends;
    }
    
    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this -> name;
    }
}