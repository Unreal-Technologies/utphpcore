<?php
namespace Utphpcore\Commands;

class Map 
{
    function __construct(\Utphpcore\IO\Directory $directory)
    {
        $fileData = [];
        $objectReference = [];
        
        foreach($this -> itterateFiles($directory) as $file)
        {
            $analyzer = new \Utphpcore\Source\Analyzers\PhpAnalyzer($file);
            $hash = md5($file -> path());
            $objects = [];

            foreach($analyzer -> classes() as $class)
            {
                $objName = '\\'.$analyzer -> namespace().'\\'.$class -> name();
                $objects[$objName] = [];
                $objectReference[$objName] = $hash;
                if($class -> extends() !== null)
                {
                    $objects[$objName][] = $class -> extends();
                }
                foreach($class -> implements() as $imp)
                {
                    $objects[$objName][] = $imp;
                }
                foreach($class -> uses() as $use)
                {
                    $objects[$objName][] = $use;
                }
            }
            foreach($analyzer -> enums() as $enum)
            {
                $objName = '\\'.$analyzer -> namespace().'\\'.$enum -> name();
                $objects[$objName] = [];
                $objectReference[$objName] = $hash;
                if($enum -> extends() !== null)
                {
                    $objects[$objName][] = $enum -> extends();
                }
                foreach($enum -> uses() as $use)
                {
                    $objects[$objName][] = $use;
                }
            }
            foreach($analyzer -> interfaces() as $interface)
            {
                $objName = '\\'.$analyzer -> namespace().'\\'.$interface -> name();
                $objects[$objName] = [];
                $objectReference[$objName] = $hash;
                if($interface -> extends() !== null)
                {
                    $objects[$objName][] = $interface -> extends();
                }
            }
            foreach($analyzer -> traits() as $trait)
            {
                $objName = '\\'.$analyzer -> namespace().'\\'.$trait -> name();
                $objects[$objName] = [];
                $objectReference[$objName] = $hash;
                if($trait -> extends() !== null)
                {
                    $objects[$objName][] = $trait -> extends();
                }
            }
            
            $required = [];
            foreach($objects as $object)
            {
                $required = array_merge($required, array_values($object));
            }

            if(count($objects) !== 0)
            {
                $fileData[$hash] = ['file' => $file, 'analyzer' => $analyzer, 'objects' => $objects, 'requried' => array_unique($required)];
            }
        }
        
        $fileToObject = [];

        foreach($fileData as $hash => $data)
        {
            $fileToObject[$hash] = [];
            
            foreach($data['requried'] as $object)
            {
                $fileToObject[$hash][] = $object;
            }
        }
        
        $references = $this -> compareReferences($fileToObject, $objectReference, $fileData);
        ksort($references);
        
        $ordered = $this -> ReferenceOrderedMapping($references);
        
        $mapFile = \Utphpcore\IO\File::fromDirectory($directory, 'class.map');
        if($mapFile -> fOpen('w+'))
        {
            $mapFile -> fWrite('<?php'."\r\n");
            foreach($ordered as $k => $v)
            {
                $mapFile -> fWrite('require_once(\''.$k.'\');');
                if(count($v['requires']) !== 0)
                {
                    $mapFile -> fwrite(' /* requires: ');
                    $mapFile -> fwrite(implode(' & ', $v['requires']));
                    $mapFile -> fwrite(' */');
                    
                    \Utphpcore\Debugging::dump($v);
                }
                $mapFile -> fwrite("\r\n");
            }
            $mapFile -> fWrite('?>'."\r\n");
            
            $mapFile -> fClose();
        }
        
        \Utphpcore\Debugging::dump('REDIRECT!');
    }
    
    /**
     * @param array $references
     * @return array
     */
    private function referenceOrderedMapping(array $references): array
    {
        $buffer = [];
        $rem = [];
        
        while(count($references) !== 0)
        {
            $rem = [];
            foreach($references as $idx => $ref)
            {
                if(count($ref['requires']) === 0)
                {
                    $buffer[$idx] = $ref;
                    $rem[] = $idx;
                }
                else
                {
                    $hasAll = true;
                    foreach($ref['requires'] as $req)
                    {
                        if(!isset($buffer[$req]))
                        {
                            $hasAll = false;
                            break;
                        }
                    }
                    
                    if($hasAll)
                    {
                        $buffer[$idx] = $ref;
                        $rem[] = $idx;
                    }
                }
            }
            
            foreach($rem as $idx)
            {
                unset($references[$idx]);
            }
        }
        
        return $buffer;
    }

    /**
     * @param array $fileToObject
     * @param array $objectReference
     * @param array $fileData
     * @return array
     * @throws \Utphpcore\Data\Exceptions\NotImplementedException
     */
    private function compareReferences(array $fileToObject, array $objectReference, array $fileData): array
    {
        $references = [];
        $internalClasses = ['\Exception'];
        
        foreach($fileToObject as $hash => $required)
        {
            $references[$hash] = ['requires' => [], 'requiredBy' => []];
        }
        
        foreach($fileToObject as $hash => $required)
        {
            foreach($required as $object)
            {
                if(!isset($objectReference[$object]) && !in_array($object, $internalClasses))
                {
                    throw new \Utphpcore\Data\Exceptions\NotImplementedException('Unknown object "'.$object.'"');
                }
                else if(!isset($objectReference[$object]))
                {
                    continue;
                }
                else
                {
                    $references[$hash]['requires'][] = $objectReference[$object];
                    $references[$objectReference[$object]]['requiredBy'][] = $hash;
                }
            }
        }
        
        $namedReferences = [];
        foreach($references as $fhash => $data)
        {
            foreach($data['requires'] as $idx => $hash)
            {
                $data['requires'][$idx] = $fileData[$hash]['file'] -> path();
            }
            foreach($data['requiredBy'] as $idx => $hash)
            {
                $data['requiredBy'][$idx] = $fileData[$hash]['file'] -> path();
            }
            $namedReferences[$fileData[$fhash]['file'] -> path()] = $data;
        }
        
        return $namedReferences;
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
