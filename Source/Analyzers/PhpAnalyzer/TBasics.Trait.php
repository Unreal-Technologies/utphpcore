<?php
namespace Utphpcore\Source\Analyzers\PhpAnalyzer;

trait TBasics 
{
    /**
     * @param array $tokens
     * @param string $namespace
     * @return array
     */
    public function body(array $tokens, string $namespace): array
    {
        $pos = 0;
        $count = count($tokens);
        $functions = [];
        $uses = [];
        
        while($pos < $count)
        {
            $token = $tokens[$pos];
            
            if(is_array($token) && $token[0] === Tokens::T_FUNCTION)
            {
                $lineTokens = $this -> getTokensByLine($tokens, $token[2]);
                $hasBody = $lineTokens[count($lineTokens) - 1] === '{';
                $hasEnding = $lineTokens[count($lineTokens) - 1] === ';' || $lineTokens[count($lineTokens) - 1] === '}';
                
                if(!$hasBody && !$hasEnding)
                {
                    $hasBody = $lineTokens[count($lineTokens) - 2] === '{';
                    $hasEnding = $lineTokens[count($lineTokens) - 2] === ';' || $lineTokens[count($lineTokens) - 2] === '}';
                    
                    if(!$hasBody && !$hasEnding)
                    {
//                        \Utphpcore\Debugging::dump($lineTokens, $tokens);
//                        exit;
                        break;
                    }
                }
                
                $start = $pos - array_search($token, $lineTokens);
                $end = $start + count($lineTokens);

                if(!$hasBody)
                {
                    $functions[] = new FunctionAnalyzer($lineTokens, $namespace);
                    $pos = $end;
                    continue;
                }
                else if($hasBody)
                {
                    $end--;
                    $depth = 0;
                    
                    for($i=$end; $i<$count; $i++)
                    {
                        $mToken = $tokens[$i];
                        if($mToken === '{')
                        {
                            $depth++;
                        }
                        else if($mToken === '}')
                        {
                            $depth--;
                            if($depth === 0)
                            {
                                $end = $i;
                                break;
                            }
                        }
                    }
                    
                    $functions[] = new FunctionAnalyzer(array_slice($tokens, $start, $end - $start), $namespace);
                    $pos = $end;
                    continue;
                }
            }
            else if(is_array($token) && $token[0] === Tokens::T_USE)
            {
                $lineTokens = $this -> getTokensByLine($tokens, $token[2]);
                foreach($lineTokens as $lToken)
                {
                    if(is_array($lToken) && $lToken[0] === Tokens::T_STRING)
                    {
                        $uses[] = '\\'.$namespace.'\\'.$lToken[1];
                    }
                    else if(is_array($lToken) && $lToken[0] === Tokens::T_NAME_FULLY_QUALIFIED)
                    {
                        $uses[] = $lToken[1];
                    }
                }
            }
            
            $pos++;
        }

        return [
            'functions' => $functions,
            'uses' => $uses
        ];
    }
    
    /**
     * @param array $tokens
     * @param int $line
     * @return array
     */
    private function getTokensByLine(array $tokens, int $line): array
    {
        $buffer = [];
        $atLine = false;
        foreach($tokens as $token)
        {
            if(is_array($token) && $token[2] === $line)
            {
                $atLine = true;
                $buffer[] = $token;
            }
            else if(!is_array($token) && $atLine)
            {
                $buffer[] = $token;
            }
            else
            {
                $atLine = false;
            }
        }
        
        return $buffer;
    }
    
    private function rawTrimmed(array $tokens): string
    {
        $string = '';
        foreach($tokens as $token)
        {
            if(is_array($token))
            {
                $string .= $token[1];
            }
            else
            {
                $string .= $token;
            }
        }
        return trim($string);
    }
    
    /**
     * @param array $tokens
     * @param string $namespace
     * @return array
     */
    public function header(array $tokens, string $namespace): array
    {
        $start = 0;
        $end = 0;
        
        foreach($tokens as $idx => $token)
        {
            if($token === '{' || $token === ';' || $token === '}')
            {
                $end = $idx;
                break;
            }
        }
        
        $header = array_slice($tokens, $start, $end);
        $raw = $this -> rawTrimmed($header);
        
        $body = array_slice($tokens, $end + 1, -1);
        
        $name = null;
        $inExtends = false;
        $inImplements = false;
        $isPrivate = false;
        
        $extends = null;
        $implements = [];
        
        foreach($header as $token)
        {
            if(is_array($token) && $token[0] === Tokens::T_PRIVATE)
            {
                $isPrivate = true;
            }
            
            if($name === null && $token[0] === Tokens::T_STRING)
            {
                $name = $token[1];
                continue;
            }
            else if($name !== null)
            {
                if($token[0] === Tokens::T_IMPLEMENTS)
                {
                    $inImplements = true;
                    $inExtends = false;
                    continue;
                }
                else if($token[0] === Tokens::T_EXTENDS)
                {
                    $inImplements = false;
                    $inExtends = true;
                    continue;
                }
                
                if($inExtends)
                {
                    if($token[0] == Tokens::T_NAME_FULLY_QUALIFIED)
                    {
                        $extends = $token[1];
                        continue;
                    }
                    if($token[0] == Tokens::T_STRING)
                    {
                        $extends = '\\'.$namespace.'\\'.$token[1];
                        continue;
                    }
                }
                
                if($inImplements)
                {
                    if($token[0] == Tokens::T_NAME_FULLY_QUALIFIED)
                    {
                        $implements[] = $token[1];
                        continue;
                    }
                    if($token[0] == Tokens::T_STRING)
                    {
                        $implements[] = '\\'.$namespace.'\\'.$token[1];
                        continue;
                    }
                }
            }
        }
        
        return [
            'name' => $name,
            'extends' => $extends,
            'implements' => $implements,
            'body' => $body,
            'raw' => $raw,
            'isPrivate' => $isPrivate
        ];
    }
}
