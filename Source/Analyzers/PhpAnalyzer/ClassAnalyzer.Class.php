<?php
namespace Utphpcore\Source\Analyzers\PhpAnalyzer;

class ClassAnalyzer 
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
    private array $implements = [];

    /**
     * @var FunctionAnalyzer[]
     */
    private array $functions = [];
    
    /**
     * @var string[];
     */
    private array $uses = [];
    
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
        $this -> implements = $header['implements'];
        $this -> functions = $body['functions'];
        $this -> uses = $body['uses'];
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
     * @return string[]
     */
    public function uses(): array
    {
        return $this -> uses;
    }
    
    /**
     * @return FunctionAnalyzer[]
     */
    public function functions(): array
    {
        return $this -> functions;
    }
    
    /**
     * @return string[]
     */
    public function implements(): array
    {
        return $this -> implements;
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