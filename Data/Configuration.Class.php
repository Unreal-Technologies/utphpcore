<?php
namespace Utphpcore\Data;
require_once('AssetTypes.Enum.php');
require_once('Exceptions/UnexpectedValueException.Class.php');

class Configuration
{
    /**
     * @var array
     */
    private $aData = [];
    
    /**
     * @param \Utphpcore\Core $core
     * @throws \Utphpcore\Data\Exceptions\IOException
     */
    function __construct(\Utphpcore\Core $core)
    {
        $assetManager = $core -> get($core::AssetManager);
        
        $configCore = $assetManager -> get('Config.Core.ini', AssetTypes::Cache);
        $configApp = $assetManager -> get('Config.App.ini', AssetTypes::Cache);
        
        if($configCore === null) //Get New Core Config file if needed
        {
            $defaultConfigCore = $assetManager -> get('Config.Core.Default.ini', AssetTypes::Core);
            if(!$assetManager -> copyTo($defaultConfigCore, 'Config.Core.ini', AssetTypes::Cache))
            {
                throw new \Utphpcore\Data\Exceptions\IOException('Could not copy "'.$defaultConfigCore -> path().'" to Cache');
            }
            $configCore = $assetManager -> get('Config.Core.ini', AssetTypes::Cache);
        }
        
        if($configApp === null) //Get New App Config file if needed
        {
            $defaultConfigApp = $assetManager -> get('Config.App.Default.ini', AssetTypes::Core);
            if(!$assetManager -> copyTo($defaultConfigApp, 'Config.App.ini', AssetTypes::Cache))
            {
                throw new \Utphpcore\Data\Exceptions\IOException('Could not copy "'.$defaultConfigCore -> path().'" to Cache');
            }
            $configApp = $assetManager -> get('Config.App.ini', AssetTypes::Cache);
        }
        
        $this -> aData['Core'] = parse_ini_file($configCore -> path(), true);
        $this -> aData['App'] = parse_ini_file($configApp -> path(), true);
    }
    
    /**
     * @param string $path
     * @param string $seperator
     * @return mixed
     * @throws \Utphpcore\Data\Exceptions\UnexpectedValueException
     */
    public function get(string $path, string $seperator = '/'): mixed
    {
        $aPath = explode($seperator, $path);
        $current = $this -> aData;
        
        foreach($aPath as $part)
        {
            if(isset($current[$part]))
            {
                $current = $current[$part];
            }
            else
            {
                throw new \Utphpcore\Data\Exceptions\UnexpectedValueException('Configurationpath "'.$path.'" does not exist.');
            }
        }
        return $current;
    }
}