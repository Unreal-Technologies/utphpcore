<?php
namespace Utphpcore\Source\Analyzers\PhpAnalyzer;

class InterfaceAnalyzer 
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
     * @var FunctionAnalyzer[]
     */
    private array $functions = [];
    
    /**
     * @var string|null
     */
    private ?string $namespace = null;

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
        $this -> functions = $body['functions'];
        $this -> namespace = $namespace;
    }
    
    /**
     * @return string|null
     */
    public function namespace(): ?string
    {
        return $this -> namespace;
    }
    
    /**
     * @return FunctionAnalyzer[]
     */
    public function functions(): array
    {
        return $this -> functions;
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