<?php
namespace Utphpcore\Data;

class Route
{
    /**
     * @var string
     */
    private string $sMatch;
    
    /**
     * @var RoutingModes
     */
    private RoutingModes $sMode;
    
    /**
     * @var string
     */
    private string $sTarget;
    
    /**
     * @var string[]
     */
    private array $aParameters;
    
    /**
     * @var string[]
     */
    private array $aQueryString;
    
    /**
     * @param string $match
     * @param string $target
     * @param array $parameters
     * @param RoutingModes $mode
     * @param array $queryString
     */
    public function __construct(string $match, string $target, array $parameters, RoutingModes $mode, array $queryString)
    {
        $this -> sMode = $mode;
        $this -> sMatch = $match;
        $this -> sTarget = $target;
        $this -> aParameters = $parameters;
        $this -> aQueryString = $queryString;
    }
    
    /**
     * @return \Utphpcore\IO\File|null
     */
    public function file(): ?\Utphpcore\IO\File
    {
        $composedPath = 'Pages/'.$this -> mode() -> name.'/'.$this -> match()['method'].'/';
        
        $targetFilePath1 = Cache::get(\Utphpcore\Core::Root) -> path().'/'.$composedPath.$this -> target()['target'];
        $targetFile = realpath($targetFilePath1);
        if($targetFile === false)
        {
            $targetFilePath2 = __DIR__.'/../'.$composedPath.$this -> target()['target'];
            $targetFile = realpath($targetFilePath2);
            if($targetFile === false)
            {
                throw new \Exception('Cannot find routing path on: "'.$targetFilePath1.'" or "'.$targetFilePath2.'"');
                return null;
            }
        }

        return \Utphpcore\IO\File::fromString($targetFile);
    }
    
    /**
     * @return RoutingModes
     */
    public function mode(): RoutingModes
    {
        return $this -> sMode;
    }
    
    /**
     * @return array
     */
    public function route(): array
    {
        $parts = explode(':', $this -> sMatch);
        return [
            'method' => $parts[0],
            'slug' => $parts[1]
        ];
    }
    
    /**
     * @return array
     */
    public function target(): array
    {
        $parts = explode('#', $this -> sTarget);
        
        return [
            'type' => $parts[0],
            'target' => implode('#', array_slice($parts, 1, count($parts) - 1))
        ];
    }
    
    /**
     * @return array
     */
    public function queryString(): array
    {
        return $this -> aQueryString;
    }
    
    /** 
     * @return array
     */
    public function parameters(): array
    {
        return $this -> aParameters;
    }
    
    /**
     * @return array
     */
    public function match(): array
    {
        $parts = explode('::', $this -> sMatch);
        
        return [
            'method' => $parts[0],
            'slug' => $parts[1]
        ];
    }
}
