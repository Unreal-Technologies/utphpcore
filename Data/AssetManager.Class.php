<?php
namespace Utphpcore\Data;

class AssetManager
{
    /**
     * @var array
     */
    private $aPaths = [];
    
    /**
     * @param \Utphpcore\Core $core
     */
    public function __construct(\Utphpcore\Core $core)
    {
        $this -> aPaths = [$core -> get($core::Cache), $core -> get($core::Temp), $core -> get($core::CoreAssets), $core -> get($core::AppAssets)];
    }
    
    /**
     * @param \Utphpcore\IO\IFile $file
     * @param string $name
     * @param AssetTypes $to
     * @return bool
     */
    public function copyTo(\Utphpcore\IO\IFile $file, string $name, AssetTypes $to): bool
    {
        $target = $this -> aPaths[$to -> value];
        return $file -> copyTo($target, $name);
    }
    
    /**
     * @param string $asset
     * @param AssetTypes $from
     * @return \Utphpcore\IO\IFile|null
     */
    public function get(string $asset, AssetTypes $from = AssetTypes::All): ?\Utphpcore\IO\IFile
    {
        if($from !== AssetTypes::All)
        {
            return $this -> getFromDirectory($asset, $this -> aPaths[$from -> value]);
        }
        else
        {
            foreach($this -> aPaths as $path)
            {
                $file = $this -> getFromDirectory($asset, $path);
                if($file !== null)
                {
                    return $file;
                }
            }
            
            return null;
        }
        
        return null;
    }
    
    /**
     * @param string $asset
     * @param \Utphpcore\IO\IDirectory $directory
     * @return \Utphpcore\IO\IFile|null
     */
    private function getFromDirectory(string $asset, \Utphpcore\IO\IDirectory $directory): ?\Utphpcore\IO\IFile
    {
        if(!$directory -> exists())
        {
            return null;
        }
        $file = \Utphpcore\IO\File::fromDirectory($directory, $asset);
        if($file -> exists())
        {
            return $file;
        }
        return null;
    }
}