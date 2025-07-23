<?php
namespace Utphpcore;

class Core
{
    public const Start = 0x00000000;
    public const Version = 0x00000001;
    public const Root = 0x01000000;
    public const Temp = 0x01000001;
    public const Cache = 0x01000002;
    
    /**
     * @var array
     */
    private array $data = [];
    
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
        $basePath = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].pathinfo($_SERVER['SCRIPT_NAME'])['dirname'];
        $root = $this -> get(self::Root) -> path();
        
        $new = str_replace([$root.'\\', $root.'/', '\\', '//', ':/'], ['', '', '/', '/', '://'], $path);
        if($new !== $path)
        {
            return $basePath.$new;
        }

        throw new Utphpcore\Exceptions\NotImplementedException($path);
    }
    
    /**
     * @param int $property
     * @param mixed $value
     */
    public function set(int $property, mixed $value)
    {
        $this -> data[$property] = $value;
    }
    
    /**
     * @param int $property
     * @return mixed
     */
    public function get(int $property): mixed
    {
        if(isset($this -> data[$property]))
        {
            return $this -> data[$property];
        }
        return null;
    }
    
    public static function initialize(): void
    {
        session_start();
        
        define('UTPHPCORE', new Core(function(Core $core)
        {
            $root = \Utphpcore\IO\Directory::fromString(__DIR__.'/../');
            
            $temp = \Utphpcore\IO\Directory::fromDirectory($root, '/__TEMP__');
            if($temp -> exists())
            {
                $temp -> remove();
            }
            $temp -> create();
            
            $cache = \Utphpcore\IO\Directory::fromDirectory($root, '__CACHE__');
            if(!$cache -> exists())
            {
                $cache -> create();
            }

            $core -> set($core::Root, $root);
            $core -> set($core::Temp, $temp);
            $core -> set($core::Cache, $cache);
            $core -> set($core::Start, microtime(true));
            $core -> set($core::Version, new \Utphpcore\Data\Version('Utphpcore', 1,0,0,0, 'https://github.com/Unreal-Technologies/utphpcore'));
        }));
    }
    
    /**
     * @return void
     */
    public static function shutdown(): void
    {
        \XHTML -> get('body', function(\UTphpcore\GUI\NoHtml\Xhtml $body)
        {
            $dif = microtime(true) - UTPHPCORE -> get(self::Start);
            
            $body -> add('div@#execution-time') -> text('Process time: '.number_format(round($dif * 1000, 4), 4, ',', '.').' ms');
            $body -> add('div@#version', function(\UTphpcore\GUI\NoHtml\Xhtml $div)
            {
                UTPHPCORE -> get(self::Version) -> Render($div);
            });
        });
        XHTML -> get('head', function(\UTphpcore\GUI\NoHtml\Xhtml $head)
        {
            $children = $head -> children();
            $head -> clear();
            
            $head -> add('link', function(\UTphpcore\GUI\NoHtml\Xhtml $link)
            {
                $link -> attributes() -> set('rel', 'icon');
                $link -> attributes() -> set('type', 'image/x-icon');
                $link -> attributes() -> set('href', UTPHPCORE -> physicalToRelativePath(__DIR__.'/Assets/Images/favicon.ico'));
            });
            $head -> add('link', function(\UTphpcore\GUI\NoHtml\Xhtml $link)
            {
                $link -> attributes() -> set('rel', 'stylesheet');
                $link -> attributes() -> set('href', 'https://fonts.googleapis.com/icon?family=Material+Icons');
            });
            
            foreach(\UTphpcore\IO\Directory::fromString(__DIR__.'/Assets/Css') -> list('/\.css$/i') as $entry)
            {
                if($entry instanceof \UTphpcore\IO\File)
                {
                    $head -> add('link', function(\UTphpcore\GUI\NoHtml\Xhtml $link) use($entry)
                    {
                        $link -> attributes() -> set('rel', 'stylesheet');
                        $link -> attributes() -> set('href', UTPHPCORE -> physicalToRelativePath($entry -> path()));
                    });
                }
            }
            
            foreach(\UTphpcore\IO\Directory::fromString(__DIR__.'/Assets/Js') -> list('/\.js/i') as $entry)
            {
                if($entry instanceof \Php2Core\IO\File)
                {
                    $head -> add('script', function(\UTphpcore\GUI\NoHtml\Xhtml $script) use($entry)
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
            XHTML -> get('body', function(\UTphpcore\GUI\NoHtml\Xhtml $body) use($ob)
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