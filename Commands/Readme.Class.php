<?php

namespace Utphpcore\Commands;

class Readme 
{
    /**
     * @var \Utphpcore\Source\Analyzers\PhpAnalyzer\ClassAnalyzer[]
     */
    private array $classes = [];
    
    /**
     * @var \Utphpcore\Source\Analyzers\PhpAnalyzer\InterfaceAnalyzer[]
     */
    private array $interfaces = [];
    
    /**
     * @var \Utphpcore\Source\Analyzers\PhpAnalyzer\EnumAnalyzer[]
     */
    private array $enums = [];
    
    /**
     * @var \Utphpcore\Source\Analyzers\PhpAnalyzer\TraitAnalyzer[]
     */
    private array $traits = [];
    
    /**
     * @param string $fqn
     * @return string
     */
    private function getObjectType(string $fqn): string
    {
        if($fqn[0] !== '\\')
        {
            $fqn = '\\'.$fqn;
        }
        
        $types = [
            'Class' => $this -> classes,
            'Interface' => $this -> interfaces,
            'Enum' => $this -> enums,
            'Trait' => $this -> traits
        ];
        
        foreach($types as $text => $data)
        {
            foreach($data as $object)
            {
                $ns = $object -> namespace().'\\'.$object -> name();
                if($ns[0] !== '\\')
                {
                    $ns = '\\'.$ns;
                }

                if($fqn === $ns)
                {
                    return $text;
                }
            }
        }
        
        return 'Unknown';
    }
    
    function __construct(\Utphpcore\IO\Directory $directory)
    {
        $md = \Utphpcore\IO\File::fromDirectory($directory, 'README.md');
        if($md -> fOpen('w+'))
        {
            foreach($this -> itterateFiles($directory) as $file)
            {
                $analyzer = new \Utphpcore\Source\Analyzers\PhpAnalyzer($file);
                $this -> classes = array_merge($this -> classes, $analyzer -> classes());
                $this -> interfaces = array_merge($this -> interfaces, $analyzer -> interfaces());
                $this -> enums = array_merge($this -> enums, $analyzer -> enums());
                $this -> traits = array_merge($this -> traits, $analyzer -> traits());
            }
            
            $md -> fWrite('#--app--'."\r\n");
            
            $stream = [];
            
            foreach($this -> classes as $class)
            {
                $buffer = [];
                $fqn = '\\'.$class -> namespace().'\\'.$class -> name();
                $buffer[] = '## '.$this -> getObjectType($fqn).': '.$fqn."\r\n";

                if($class -> extends() !== null)
                {
                    $type = $this -> getObjectType($class -> extends());
                    $buffer[] = '### Extends'."\r\n";
                    $buffer[] = $type.': ['.$class -> extends().'](#'.str_replace('\\', '', strtolower($type.'-'.$class -> extends())).')'."\r\n";
                    $buffer[] = "\r\n";
                }

                if(count($class -> implements()) !== 0)
                {
                    $buffer[] = '### Implements'."\r\n";
                    foreach($class -> implements() as $imp)
                    {
                        $type = $this -> getObjectType($imp);
                        $buffer[] = $type.': ['.$imp.'](#'.str_replace('\\', '', strtolower($type.'-'.$imp)).')'."\r\n";
                    }
                    $buffer[] = "\r\n";
                }

                if(count($class -> uses()) !== 0)
                {
                    $buffer[] = '### Uses'."\r\n";
                    foreach($class -> uses() as $use)
                    {
                        $type = $this -> getObjectType($use);
                        $buffer[] = $type.': ['.$use.'](#'.str_replace('\\', '', strtolower($type.'-'.$use)).')'."\r\n";
                    }
                    $buffer[] = "\r\n";
                }

                $buffer[] = '### Functions'."\r\n";
                $functions = (new \Utphpcore\Data\Collections\Linq($class -> functions())) -> where(function(\Utphpcore\Source\Analyzers\PhpAnalyzer\FunctionAnalyzer $fa)
                {
                    return !$fa -> isPrivate();
                }) -> orderBy(function(\Utphpcore\Source\Analyzers\PhpAnalyzer\FunctionAnalyzer $fa)
                {
                    return $fa -> raw();
                }) -> toArray();

                foreach($functions as $function)
                {
                    $buffer[] = '```'."\r\n";
                    $buffer[] = $function -> raw()."\r\n";
                    $buffer[] = '```'."\r\n";
                }
                
                $stream[$fqn] = implode('', $buffer);
            }
            unset($class);
            
            foreach($this -> interfaces as $interface)
            {
                $buffer = [];
                $fqn = '\\'.$interface -> namespace().'\\'.$interface -> name();
                $buffer[] = '## '.$this -> getObjectType($fqn).': '.$fqn."\r\n";

                if($interface -> extends() !== null)
                {
                    $type = $this -> getObjectType($interface -> extends());
                    $buffer[] = '### Extends'."\r\n";
                    $buffer[] = $type.': ['.$interface -> extends().'](#'.str_replace('\\', '', strtolower($type.'-'.$interface -> extends())).')'."\r\n";
                    $buffer[] = "\r\n";
                }

                $buffer[] = '### Functions'."\r\n";
                $functions = (new \Utphpcore\Data\Collections\Linq($interface -> functions())) -> where(function(\Utphpcore\Source\Analyzers\PhpAnalyzer\FunctionAnalyzer $fa)
                {
                    return !$fa -> isPrivate();
                }) -> orderBy(function(\Utphpcore\Source\Analyzers\PhpAnalyzer\FunctionAnalyzer $fa)
                {
                    return $fa -> raw();
                }) -> toArray();

                foreach($functions as $function)
                {
                    $buffer[] = '```'."\r\n";
                    $buffer[] = $function -> raw()."\r\n";
                    $buffer[] = '```'."\r\n";
                }
                
                $stream[$fqn] = implode('', $buffer);
            }
            unset($interface);
            
            foreach($this -> enums as $enum)
            {
                $buffer = [];
                $fqn = '\\'.$enum -> namespace().'\\'.$enum -> name();
                $buffer[] = '## '.$this -> getObjectType($fqn).': '.$fqn."\r\n";

                if($enum -> extends() !== null)
                {
                    $type = $this -> getObjectType($enum -> extends());
                    $buffer[] = '### Extends'."\r\n";
                    $buffer[] = $type.': ['.$enum -> extends().'](#'.str_replace('\\', '', strtolower($type.'-'.$enum -> extends())).')'."\r\n";
                    $buffer[] = "\r\n";
                }

                if(count($enum -> uses()) !== 0)
                {
                    $buffer[] = '### Uses'."\r\n";
                    foreach($enum -> uses() as $use)
                    {
                        $type = $this -> getObjectType($use);
                        $buffer[] = $type.': ['.$use.'](#'.str_replace('\\', '', strtolower($type.'-'.$use)).')'."\r\n";
                    }
                    $buffer[] = "\r\n";
                }

                $stream[$fqn] = implode('', $buffer);
            }
            unset($enum);
            
            foreach($this -> traits as $trait)
            {
                $buffer = [];
                $fqn = '\\'.$trait -> namespace().'\\'.$trait -> name();
                $buffer[] = '## '.$this -> getObjectType($fqn).': '.$fqn."\r\n";

                if($trait -> extends() !== null)
                {
                    $type = $this -> getObjectType($trait -> extends());
                    $buffer[] = '### Extends'."\r\n";
                    $buffer[] = $type.': ['.$trait -> extends().'](#'.str_replace('\\', '', strtolower($trait.'-'.$class -> extends())).')'."\r\n";
                    $buffer[] = "\r\n";
                }

                $buffer[] = '### Functions'."\r\n";
                $functions = (new \Utphpcore\Data\Collections\Linq($trait -> functions())) 
                    -> where(function(\Utphpcore\Source\Analyzers\PhpAnalyzer\FunctionAnalyzer $fa)
                    {
                        return !$fa -> isPrivate();
                    }) 
                    -> orderBy(function(\Utphpcore\Source\Analyzers\PhpAnalyzer\FunctionAnalyzer $fa)
                    {
                        return $fa -> raw();
                    }) 
                    -> toArray();

                foreach($functions as $function)
                {
                    $buffer[] = '```'."\r\n";
                    $buffer[] = $function -> raw()."\r\n";
                    $buffer[] = '```'."\r\n";
                }
                
                $stream[$fqn] = implode('', $buffer);
            }
            
            ksort($stream);
            $md -> fWrite(implode('', $stream));
        }
        
        \Utphpcore\Data\Cache::get(\Utphpcore\Core::Message) -> push('Readme Written');
        header('Location: '.(isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '/'));
        exit;
    }
    
    /**
     * @param \Utphpcore\IO\Directory $directory
     * @return array
     */
    private function itterateFiles(\Utphpcore\IO\Directory $directory): array
    {
        $buffer = [];
        foreach($directory -> list() as $entry)
        {
            if($entry instanceof \Utphpcore\IO\Directory)
            {
                $buffer = array_merge($buffer, $this -> itterateFiles($entry));
            }
            else if($entry instanceof \Utphpcore\IO\File && $entry -> extension() === 'php')
            {
                $buffer[] = $entry;
            }
        }
        
        return $buffer;
    }
}
