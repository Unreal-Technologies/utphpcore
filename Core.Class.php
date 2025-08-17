<?php
namespace Utphpcore;

class Core
{
    public const Start         = __CLASS__.'\\'.(0x10000000);
    public const Version       = __CLASS__.'\\'.(0x10000001);
    public const InstanceID    = __CLASS__.'\\'.(0x10000002);
    public const DefaultRoute  = __CLASS__.'\\'.(0x10000003);
    public const Route         = __CLASS__.'\\'.(0x10000004);
    public const Root          = __CLASS__.'\\'.(0x10000010);
    public const Temp          = __CLASS__.'\\'.(0x10000011);
    public const Cache         = __CLASS__.'\\'.(0x10000012);
    public const CoreAssets    = __CLASS__.'\\'.(0x10000013);
    public const AppAssets     = __CLASS__.'\\'.(0x10000014); 
    public const AssetManager  = __CLASS__.'\\'.(0x10000020);
    public const Configuration = __CLASS__.'\\'.(0x10000021);
    public const Message       = __CLASS__.'\\'.(0xffffffff); 
    
    /**
     * @var int[]
     */
    private array $version = [];
    
    /**
     * @var string|null
     */
    private ?string $url = null;
    
    private static array $shutdownBody = [];
    
    /**
     * @param \Closure $cb
     */
    function __construct(\Closure $cb)
    {
        $this -> version = [0, 0, 0, 1];
        $this -> url = 'https://github.com/Unreal-Technologies/utphpcore';
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
            
            Data\Cache::create(Data\CacheTypes::Session, $core::Message, function()
            {
                return new Core\Messages();
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
            
            Data\Cache::create(Data\CacheTypes::Session, $core::AssetManager, function() use($core)
            {
                return new Data\AssetManager($core);
            });
            
            Data\Cache::create(Data\CacheTypes::Session, $core::Configuration, function() use($core)
            {
                return new Data\Configuration($core);
            });
            
            $configuration = Data\Cache::get($core::Configuration); /* @var $configuration Data\Configuration */
            if($configuration -> get('App/Version/Enabled'))
            {
                Data\Cache::create(Data\CacheTypes::Session, $core::Version, function() use($core, $configuration)
                {
                    $aVersion = explode('.', $configuration -> get('App/Application/Version'));
                    $url = $configuration -> get('App/Application/Url');
                    if($url === '')
                    {
                        $url = null;
                    }

                    $version = new Data\Version($configuration -> get('App/Application/Title'), $aVersion[0], $aVersion[1], $aVersion[2], $aVersion[3], $url);
                    $version -> add(new Data\Version('Utphpcore', $core -> version[0], $core -> version[1], $core -> version[2], $core -> version[3], $core -> url));

                    return $version;
                });
            }

            $core -> initializeDbs();
            $core -> initializeAdminCommands();
            $core -> initializeRouting();
        }));
    }

    /**
     * @return void
     */
    private function initializeRouting(): void
    {
        //Get DB Instance
        $coreDbc = IO\Data\Db\Database::getInstance('Core'); /* @var $coreDbc IO\Data\Db\Database */
        $instanceId = Data\Cache::get(self::InstanceID);
        $authenticated = false;
        $isServerAdmin = false;

        Data\Cache::create(Data\CacheTypes::Session, self::DefaultRoute, function() use($coreDbc, $authenticated, $instanceId)
        {
            //Get Default handler
            $coreDbc -> query(
                'select '
                . 'case when `match` is null then \'index\' else `match` end as `match` '
                . 'from `route` '
                . 'where `default` = "true" '
                . 'and ( `instance-id` = '.$instanceId.' or `instance-id` is null ) '
                . ($authenticated ? '' : 'and `auth` = "false" ')
                . 'order by `id` asc '
                . 'limit 0,1'
            );

            $defaultResults = $coreDbc -> execute();
            $defaultRoute = $defaultResults['iRowCount'] === 0 ? 'index' : $defaultResults['aResults'][0]['match'];
            
            return $defaultRoute;
        });
        
        Data\Cache::create(Data\CacheTypes::Memory, self::Route, function() use($coreDbc, $authenticated, $isServerAdmin, $instanceId)
        {
            $defaultRoute = Data\Cache::get(self::DefaultRoute);

            //Get Router Information
            $router = new Data\Router($defaultRoute);
            $slug = $router -> slug();
            $possibilities = $this -> getPossibleMatchesFromSlug($slug);

            //Get Possible routes
            $coreDbc -> query(
                'select '
                . '`method`, `match`, `target`, `type`, `auth`, `mode` '
                . 'from `route` '
                . 'where ( `instance-id` = '.$instanceId.' or `instance-id` is null ) '
                . 'and ((`match` regexp \''.implode('\' or `match` regexp \'', $possibilities).'\') or (`match` = "'.$defaultRoute.'" and `default` = "true")) '
                . ($authenticated ? '' : 'and `auth` = "false" ')
                . ($isServerAdmin ? '' : 'and `type` != \'function\' ')
            );

            //Register possible routes
            $routeResult = $coreDbc -> execute();

            foreach($routeResult['aResults'] as $row)
            {
                $router -> register($row['method'].'::'.$row['match'], $row['type'].'#'.$row['target'], $row['mode']);
            }
            
            //get current route (if matched)
            return $router -> match();
        });
        
        
        $route = Data\Cache::get(self::Route);
        //throw exception when not actually matched
        if($route === null)
        {
            //throw new \Exception('Route not found');
        }
        else if($route -> target()['type'] === 'function')
        {
            eval($route -> target()['target'].'();');
            exit;
        }
    }
    
    /**
     * @param string $slug
     * @return array
     */
    private static function getPossibleMatchesFromSlug(string $slug): array
    {
        $parts = explode('/', $slug);
        $buffer = [ '^'.$parts[0].'$' ];
        $offParts = [ $parts[0] ];
        
        for($i=1; $i<count($parts); $i++)
        {
            $offParts[$i] = '{.+}';
            
            $temp1 = implode('\\/', array_slice($parts, 0, $i));
            $temp2 = implode('\\/', array_slice($offParts, 0, $i));
            
            $buffer[] = '^'.$temp1.'\\/'.$parts[$i].'$';
            $buffer[] = '^'.$temp2.'\\/'.$parts[$i].'$';
            
            $buffer[] = '^'.$temp1.'\\/{.+}$';
            $buffer[] = '^'.$temp2.'\\/{.+}$';
        }
        
        return array_values(array_unique($buffer));
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
                case 'clear':
                    new Commands\Clear();
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
        
        $core = $this;
        Data\Cache::create(Data\CacheTypes::Session, $core::InstanceID, function() use($core)
        {
            $configuration = Data\Cache::get($core::Configuration); /* @var $configuration Data\Configuration */

            $coreDbc = IO\Data\Db\Database::getInstance('Core'); /* @var $coreDbc IO\Data\Db\Database */
            $coreDbc -> query('select `id` from `instance` where `name` = "'.$configuration -> get('App/Application/Title').'"');
            $result = $coreDbc -> execute();

            if($result['iRowCount'] > 0)
            {
                return $result['aResults'][0]['id'];
            }

            return -1;
        });
        
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
     * @param GUI\NoHtml\Xhtml $container
     * @return void
     */
    private static function shutdown_version(GUI\NoHtml\Xhtml $container): void
    {
        $version = Data\Cache::get(self::Version); /* @var $version = Data\Version|null */
        if($version !== null)
        {
            $container -> add('div@#version', function(GUI\NoHtml\Xhtml $div) use($version)
            {
                $version -> Render($div);
            });
        }
    }
    
    /**
     * @param GUI\NoHtml\Xhtml $container
     * @return void
     */
    private static function shutdown_executiontime(GUI\NoHtml\Xhtml $container): void
    {
        $dif = microtime(true) - Data\Cache::get(self::Start);

        $execTime = $container -> add('div@#execution-time');
        new GUI\NoHtml\Materialize\Icon($execTime, GUI\NoHtml\Materialize\Icon\Icons::AccessTime);
        $execTime -> text(number_format(round($dif * 1000, 4), 4, ',', '.').' ms');
    }
    
    /**
     * @param GUI\NoHtml\Xhtml $container
     * @return void
     */
    private static function shutdown_messagestack(GUI\NoHtml\Xhtml $container): void
    {
        $messageStack = Data\Cache::get(Core::Message);
        while(!$messageStack -> isEmpty())
        {
            $message = $messageStack -> pop();
            $messageToast = $container -> add('script');
            $messageToast -> attributes() -> set('type', 'text/javascript');
            $messageToast -> text('var mToast = M.toast('
                    . '{'
                        . 'html: \''.$message.'\', '
                        . 'displayLength: 15000, '
                        . 'classes: \'toast-system-message rounded\''
                    . '}'
                . ');'
            );
        }
    }
    
    /**
     * @param string $key
     * @param \Closure $callback
     */
    public static function register_shutdown_body(string $key, \Closure $callback)
    {
        if(!isset(self::$shutdownBody[$key]))
        {
            self::$shutdownBody[$key] = $callback;
        }
    }
    
    /**
     * @return void
     */
    public static function shutdown(): void
    {
        if(defined('XHTML'))
        {
            XHTML -> get('body/div@.container', function(GUI\NoHtml\Xhtml $container)
            {
                self::shutdown_version($container);
                self::shutdown_executiontime($container);
                self::shutdown_messagestack($container);
            });
            
            XHTML -> get('body', function(GUI\NoHtml\Xhtml $body)
            {
                $colorRed = new GUI\NoHtml\Materialize\Color(GUI\NoHtml\Materialize\Colors::Red, GUI\NoHtml\Materialize\ColorOffsets::Darken4);
                $colorBlue = new GUI\NoHtml\Materialize\Color(GUI\NoHtml\Materialize\Colors::Blue, GUI\NoHtml\Materialize\ColorOffsets::Darken4);
                
                $fab = new GUI\NoHtml\Materialize\FloatingActionButton(GUI\NoHtml\Materialize\Icon\Icons::Security, $colorRed);
                $fab -> link('?cmd=map', GUI\NoHtml\Materialize\Icon\Icons::Map, $colorBlue, 'Class Mapper');
                $fab -> link('?cmd=readme', GUI\NoHtml\Materialize\Icon\Icons::ChromeReaderMode, $colorBlue, 'Readme.md creator');
                $fab -> link('?cmd=clear', GUI\NoHtml\Materialize\Icon\Icons::BorderClear, $colorBlue, 'Clear Cache');
                $fab -> render($body, 'fab-admin', [
                    'direction' => '\'left\'',
                    'hoverEnabled' => 'false'
                ]);

                foreach(self::$shutdownBody as $callback)
                {
                    $callback($body);
                }
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