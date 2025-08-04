<?php
namespace Utphpcore\Source\Analyzers;

class PhpAnalyzer
{
    /**
     * @var string|null
     */
    private ?string $namespace = null;
    
    /**
     * @var PhpAnalyzer\ClassAnalyzer[]
     */
    private array $classes = [];
    
    /**
     * @var PhpAnalyzer\EnumAnalyzer[]
     */
    private array $enums = [];
    
    /**
     * @var PhpAnalyzer\InterfaceAnalyzer[]
     */
    private array $interfaces = [];
    
    /**
     * @var PhpAnalyzer\TraitAnalyzer[]
     */
    private array $traits = [];
    
    /**
     * @param \Utphpcore\IO\File $file
     */
    public function __construct(\Utphpcore\IO\File $file)
    {
        $stream = $file -> read();
        $tokens = token_get_all($stream);
        for($i=0; $i<count($tokens); $i++)
        {
            $t = $tokens[$i];
            if(is_array($t))
            {
                $t[3] = PhpAnalyzer\Tokens::getToken($t[0]);
            }
            $tokens[$i] = $t;
        }

        $offset = 0;
        $hasNamespace = false;
        foreach($tokens as $token)
        {
            if($token[0] === PhpAnalyzer\Tokens::T_NAMESPACE)
            {
                $hasNamespace = true;
            }
            else if(
                $hasNamespace && 
                $this -> namespace === null && 
                (
                    $token[0] == PhpAnalyzer\Tokens::T_STRING || 
                    $token[0] == PhpAnalyzer\Tokens::T_NAME_FULLY_QUALIFIED || 
                    $token[0] == PhpAnalyzer\Tokens::T_NAME_QUALIFIED
                )
            )
            {
                $this -> namespace = $token[1];
            }
            else if($hasNamespace && $this -> namespace !== null)
            {
                if(
                    $token[0] === PhpAnalyzer\Tokens::T_CLASS || 
                    $token[0] === PhpAnalyzer\Tokens::T_ENUM || 
                    $token[0] === PhpAnalyzer\Tokens::T_INTERFACE || 
                    $token[0] === PhpAnalyzer\Tokens::T_TRAIT
                )
                {
                    $depth = 0;
                    $start = $offset;
                    $end = $start;
                    for($i=$offset; $i<count($tokens); $i++)
                    {
                        $t = $tokens[$i];
                        if($t === '{')
                        {
                            $depth++;
                        }
                        else if($t === '}')
                        {
                            if($depth === 1)
                            {
                                $end = $i;
                                break;
                            }
                            $depth--;
                        }
                    }
                    if($token[0] === PhpAnalyzer\Tokens::T_CLASS)
                    {
                        $this -> classes[] = new PhpAnalyzer\ClassAnalyzer(array_slice($tokens, $start, $end), $this -> namespace);
                    }
                    if($token[0] === PhpAnalyzer\Tokens::T_ENUM)
                    {
                        $this -> enums[] = new PhpAnalyzer\EnumAnalyzer(array_slice($tokens, $start, $end), $this -> namespace);
                    }
                    if($token[0] === PhpAnalyzer\Tokens::T_INTERFACE)
                    {
                        $this -> interfaces[] = new PhpAnalyzer\InterfaceAnalyzer(array_slice($tokens, $start, $end), $this -> namespace);
                    }
                    if($token[0] === PhpAnalyzer\Tokens::T_TRAIT)
                    {
                        $this -> traits[] = new PhpAnalyzer\TraitAnalyzer(array_slice($tokens, $start, $end), $this -> namespace);
                    }
                    $offset = $end;
                }
            }
            $offset++;
        }
    }
    
    /**
     * @return string|null
     */
    public function namespace(): ?string
    {
        return $this -> namespace;
    }
    
    /**
     * @return PhpAnalyzer\TraitAnalyzer[]
     */
    public function traits(): array
    {
        return $this -> traits;
    }
    
    /**
     * @return PhpAnalyzer\InterfaceAnalyzer[]
     */
    public function interfaces(): array
    {
        return $this -> interfaces;
    }
    
    /**
     * @return PhpAnalyzer\EnumAnalyzer[]
     */
    public function enums(): array
    {
        return $this -> enums;
    }
    
    /**
     * @return PhpAnalyzer\ClassAnalyzer[]
     */
    public function classes(): array
    {
        return $this -> classes;
    }
}