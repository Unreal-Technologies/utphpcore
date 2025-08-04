<?php
namespace Utphpcore\Data;

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
        $assetManager = $core -> get($core::AssetManager); /* @var $assetManager AssetManager */
        
        $configCore = $assetManager -> get('Config.Core.ini', AssetTypes::Cache) ?-> asIni(); /* @var $configCore \Utphpcore\IO\Common\Ini|null */
        $configApp = $assetManager -> get('Config.App.ini', AssetTypes::Cache) ?-> asIni(); /* @var $configApp \Utphpcore\IO\Common\Ini|null */

        if($configCore === null) //Get New Core Config file if needed
        {
            $defaultConfigCore = $assetManager -> get('Config.Core.Default.ini', AssetTypes::Core) -> AsIni(); /* @var $defaultConfigCore \Utphpcore\IO\Common\Ini */
            if(!$assetManager -> copyTo($defaultConfigCore, 'Config.Core.ini', AssetTypes::Cache))
            {
                throw new \Utphpcore\Data\Exceptions\IOException('Could not copy "'.$defaultConfigCore -> path().'" to Cache');
            }
            $configCore = $assetManager -> get('Config.Core.ini', AssetTypes::Cache) ?-> asIni(); /* @var $configCore \Utphpcore\IO\Common\Ini|null */
        }
        
        if($configApp === null) //Get New App Config file if needed
        {
            $defaultConfigApp = $assetManager -> get('Config.App.Default.ini', AssetTypes::Core) -> AsIni(); /* @var $defaultConfigApp \Utphpcore\IO\Common\Ini */
            if(!$assetManager -> copyTo($defaultConfigApp, 'Config.App.ini', AssetTypes::Cache))
            {
                throw new \Utphpcore\Data\Exceptions\IOException('Could not copy "'.$defaultConfigCore -> path().'" to Cache');
            }
            $configApp = $assetManager -> get('Config.App.ini', AssetTypes::Cache) ?-> asIni(); /* @var $configApp \Utphpcore\IO\Common\Ini|null */
        }
        
        $this -> aData['Core'] = $configCore ?-> parse();
        $this -> aData['App'] = $configApp ?-> parse();
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