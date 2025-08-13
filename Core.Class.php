<?php
namespace Utphpcore;

class Core
{
    public const Start = 0x10000000;
    public const Version = 0x10000001;
    public const Root = 0x10000010;
    public const Temp = 0x10000011;
    public const Cache = 0x10000012;
    public const CoreAssets = 0x10000013;
    public const AppAssets = 0x10000014; 
    public const AssetManager = 0x10000020;
    public const Configuration = 0x10000021;
    
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
        $basePath = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].''.str_replace('//', '/', pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/');
        $root = Data\Cache::get(self::Root) -> path();

        $new = str_replace([$root.'\\', $root.'/', '\\', '//', ':/'], ['', '', '/', '/', '://'], $path);
        if($new !== $path)
        {
            return $basePath.$new;
        }

        throw new Data\Exceptions\NotImplementedException($path);
    }

    /**
     * @return void
     */
    public static function initialize(): void
    {
        session_start();
        
        define('UTPHPCORE', new Core(function(Core $core)
        {
            Data\Cache::create(Data\CacheTypes::Session, $core::Root, function()
            {
                return IO\Directory::fromString(__DIR__.'/../');
            });
            
            Data\Cache::create(Data\CacheTypes::Session, $core::Temp, function() use($core)
            {
                $root = Data\Cache::get($core::Root);
                $temp = IO\Directory::fromDirectory($root, '/__TEMP__');
                if($temp -> exists())
                {
                    $temp -> remove();
                }
                $temp -> create();
                return $temp;
            });
            
            Data\Cache::create(Data\CacheTypes::Session, $core::Cache, function() use($core)
            {
                $root = Data\Cache::get($core::Root);
                $cache = IO\Directory::fromDirectory($root, '__CACHE__');
                if(!$cache -> exists())
                {
                    $cache -> create();
                }
                return $cache;
            });
            
            Data\Cache::create(Data\CacheTypes::Session, $core::CoreAssets, function() use($core)
            {
                $root = Data\Cache::get($core::Root);
                return IO\Directory::fromString($root -> path().'/Utphpcore/Assets');
            });
            
            Data\Cache::create(Data\CacheTypes::Session, $core::AppAssets, function() use($core)
            {
                $root = Data\Cache::get($core::Root);
                return IO\Directory::fromString($root -> path().'/Assets');
            });
            
            Data\Cache::create(Data\CacheTypes::Session, $core::AppAssets, function() use($core)
            {
                $root = Data\Cache::get($core::Root);
                return IO\Directory::fromString($root -> path().'/Assets');
            });
            
            Data\Cache::create(Data\CacheTypes::Memory, $core::Start, function()
            {
                return microtime(true);
            });
            
            Data\Cache::create(Data\CacheTypes::Session, $core::Version, function()
            {
                return new Data\Version('Utphpcore', 0,0,0,1, 'https://github.com/Unreal-Technologies/utphpcore');
            });
            
            Data\Cache::create(Data\CacheTypes::Session, $core::AssetManager, function() use($core)
            {
                return new Data\AssetManager($core);
            });
            
            Data\Cache::create(Data\CacheTypes::Session, $core::Configuration, function() use($core)
            {
                return new Data\Configuration($core);
            });

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
                    new Commands\Map(Data\Cache::get($this::Root));
                    break;
                case 'readme':
                    new Commands\Readme(Data\Cache::get($this::Root));
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
        $configuration = Data\Cache::get($this::Configuration); /* @var $configuration Data\Configuration */
        $assetManager = Data\Cache::get($this::AssetManager); /* @var $assetManager Data\AssetManager */
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
            XHTML -> get('body/div@.container', function(GUI\NoHtml\Xhtml $body)
            {
                $dif = microtime(true) - Data\Cache::get(self::Start);

                $body -> add('div@#execution-time') -> text('Process time: '.number_format(round($dif * 1000, 4), 4, ',', '.').' ms');
                $body -> add('div@#version', function(GUI\NoHtml\Xhtml $div)
                {
                    Data\Cache::get(self::Version) -> Render($div);
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