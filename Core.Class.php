<?php
namespace Utphpcore;

class Core
{
    public const Start = 0x00000000;
    public const Version = 0x00000001;
    public const Root = 0x01000000;
    public const Temp = 0x01000001;
    public const Cache = 0x01000002;
    public const CoreAssets = 0x01000003;
    public const AppAssets = 0x01000004; 
    public const AssetManager = 0x02000000;
    public const Configuration = 0x02000001;
    
    /**
     * @var array
     */
    private array $aData = [];
    
    /**
     * @param \Closure $cb
     */
    function __construct(\Closure $cb)
    {
        $cb($this);
    }
    
    /**
     * @param string $path
     * @return string
     */
    public function physicalToRelativePath(string $path): string
    {
        $basePath = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].''.pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/';
        $root = $this -> get(self::Root) -> path();
        
        $new = str_replace([$root.'\\', $root.'/', '\\', '//', ':/'], ['', '', '/', '/', '://'], $path);
        if($new !== $path)
        {
            return $basePath.$new;
        }

        throw new Data\Exceptions\NotImplementedException($path);
    }
    
    /**
     * @param int $property
     * @param mixed $value
     */
    public function set(int $property, mixed $value)
    {
        $this -> aData[$property] = $value;
    }
    
    /**
     * @param int $property
     * @return mixed
     */
    public function get(int $property): mixed
    {
        if(isset($this -> aData[$property]))
        {
            return $this -> aData[$property];
        }
        return null;
    }
    
    /**
     * @return void
     */
    public static function initialize(): void
    {
        session_start();
        
        define('UTPHPCORE', new Core(function(Core $core)
        {
            $root = IO\Directory::fromString(__DIR__.'/../');
            
            $temp = IO\Directory::fromDirectory($root, '/__TEMP__');
            if($temp -> exists())
            {
                $temp -> remove();
            }
            $temp -> create();
            
            $cache = IO\Directory::fromDirectory($root, '__CACHE__');
            if(!$cache -> exists())
            {
                $cache -> create();
            }

            $core -> set($core::Root, $root);
            $core -> set($core::Temp, $temp);
            $core -> set($core::Cache, $cache);
            $core -> set($core::CoreAssets, IO\Directory::fromString($root -> path().'/Utphpcore/Assets'));
            $core -> set($core::AppAssets, IO\Directory::fromString($root -> path().'/Assets'));
            $core -> set($core::Start, microtime(true));
            $core -> set($core::Version, new Data\Version('Utphpcore', 1,0,0,0, 'https://github.com/Unreal-Technologies/utphpcore'));
            $core -> set($core::AssetManager, new Data\AssetManager($core));
            $core -> set($core::Configuration, new Data\Configuration($core));
            $core -> initializeDbs();
            $core -> initializeAdminCommands();
        }));
    }
    
    /**
     * @return void
     */
    private function initializeAdminCommands(): void
    {
        if(isset($_GET['cmd']))
        {
            switch($_GET['cmd'])
            {
                case 'map':
                    new Commands\Map($this -> get($this::Root));
                    break;
                case 'readme':
                    new Commands\Readme($this -> get($this::Root));
                    break;
                default:
                    break;
            }
        }
    }
    
    /**
     * @return void
     * @throws \Utphpcore\Data\Exceptions\NotImplementedException
     * @throws \PDOException
     */
    private function initializeDbs(): void
    {
        $configuration = $this -> get($this::Configuration); /* @var $configuration Data\Configuration */
        $assetManager = $this -> get($this::AssetManager); /* @var $assetManager Data\AssetManager */
        $cases = [Data\AssetTypes::Core, Data\AssetTypes::App];

        $refresh = false;
        foreach($cases as $case)
        {
            $text = $case -> name;
            $info = $configuration -> get($text.'/Database');
            $enabled = $info['Enabled'] === '1';
            
            if($enabled)
            {
                $instance = IO\Data\Db\Database::createInstance($text, $info['Host'], $info['Username'], $info['Password'], $info['Database']);
                $instance -> query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \''.$info['Database'].'\'');
                try
                {
                    $instance -> execute();
                } 
                catch (\PDOException $pex) 
                {
                    if($pex -> getCode() === 1049)
                    {
                        $dir = $assetManager -> get('Database', $case);
                        foreach($dir -> list() as $entry)
                        {
                            if($entry instanceof IO\File)
                            {
                                $ext = strtolower($entry -> extension());
                                switch($ext)
                                {
                                    case "sql":
                                        $instance -> structure($entry -> read(), Data\CacheTypes::Memory, true);
                                        $refresh = true;
                                        break;
                                    case "php":
                                        include($entry -> path());
                                        $refresh = true;
                                        break;
                                    default:
                                        throw new Data\Exceptions\NotImplementedException('Unknown Database Type "'.$ext.'".');
                                }
                            }
                        }
                    }
                    if($refresh)
                    {
                        continue;
                    }
                    throw $pex;
                }
            }
        }
        
        if($refresh)
        {
            $this -> redirect();
        }
    }
    
    /**
     * @param string|null $url
     * @return void
     */
    public function redirect(?string $url = null): void
    {
        if($url === null)
        {
            $url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        }
        
        header('Location: '.$url);
        exit;
    }
    
    /**
     * @return void
     */
    public static function shutdown(): void
    {
        if(defined('XHTML'))
        {
            XHTML -> get('body', function(GUI\NoHtml\Xhtml $body)
            {
                $dif = microtime(true) - UTPHPCORE -> get(self::Start);

                $body -> add('div@#execution-time') -> text('Process time: '.number_format(round($dif * 1000, 4), 4, ',', '.').' ms');
                $body -> add('div@#version', function(GUI\NoHtml\Xhtml $div)
                {
                    UTPHPCORE -> get(self::Version) -> Render($div);
                });
            });
            XHTML -> get('head', function(GUI\NoHtml\Xhtml $head)
            {
                $children = $head -> children();
                $head -> clear();

                $head -> add('link', function(GUI\NoHtml\Xhtml $link)
                {
                    $link -> attributes() -> set('rel', 'icon');
                    $link -> attributes() -> set('type', 'image/x-icon');
                    $link -> attributes() -> set('href', UTPHPCORE -> physicalToRelativePath(__DIR__.'/Assets/Images/favicon.ico'));
                }, true);
                $head -> add('link', function(GUI\NoHtml\Xhtml $link)
                {
                    $link -> attributes() -> set('rel', 'stylesheet');
                    $link -> attributes() -> set('href', 'https://fonts.googleapis.com/icon?family=Material+Icons');
                }, true);

                foreach(IO\Directory::fromString(__DIR__.'/Assets/Css') -> list('/\.css$/i') as $entry)
                {
                    if($entry instanceof IO\File)
                    {
                        $head -> add('link', function(GUI\NoHtml\Xhtml $link) use($entry)
                        {
                            $link -> attributes() -> set('rel', 'stylesheet');
                            $link -> attributes() -> set('href', UTPHPCORE -> physicalToRelativePath($entry -> path()));
                        }, true);
                    }
                }

                foreach(IO\Directory::fromString(__DIR__.'/Assets/Js') -> list('/\.js/i') as $entry)
                {
                    if($entry instanceof IO\File)
                    {
                        $head -> add('script', function(GUI\NoHtml\Xhtml $script) use($entry)
                        {
                            $script -> attributes() -> set('type', 'text/javascript');
                            $script -> attributes() -> set('src', UTPHPCORE -> physicalToRelativePath($entry -> path()));
                        });
                    }
                }

                foreach($children as $child)
                {
                    $head -> append($child);
                }
            });

            $ob = ob_get_clean();
            if(strlen($ob) !== 0) //Attach output buffer to Xhtml, and clear
            {
                XHTML -> get('body', function(GUI\NoHtml\Xhtml $body) use($ob)
                {
                    $text = $ob.((string)$body);
                    $body -> clear();
                    $body -> text($text);
                });
            }

            //output
            echo XHTML;
        }
    }
}