<?php
namespace Utphpcore;

class Debugging
{
    /**
     * @var string|null
     */
    public static $dumpTitle = null;
    
    /**
     * @var bool
     */
    public static $dumpAsHtml = false;
    
    /**
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return void
     */
    public static function errorHandler(int $errno, string $errstr, string $errfile, int $errline): void
    {
        $hasBody = false;
        
        if(defined('XHTML'))
        {
            XHTML -> get('body', function(GUI\NoHtml\Xhtml $body) use(&$hasBody, $errno, $errstr, $errfile, $errline)
            {
                $trace = self::getTrace($body);

                $body -> clear();
                $body -> add('h2') -> text('Utphpcore::ErrorHandler');
                $body -> add('xmp') -> text(print_r($errfile.':'.$errline, true));
                $body -> add('xmp') -> text($errno."\r\n".print_r($errstr, true));
                if($trace !== null)
                {
                    $body -> append($trace);
                }

                $hasBody = true; 
            });
        }

        if(!$hasBody)
        {
            echo '<h2>Utphpcore::ErrorHandler</h2>';
            echo '<xmp>';
            var_dump($errfile);
            var_dumP($errline);
            var_dumP($errno);
            var_dumP($errstr);
            echo '</xmp>';
        }
        exit;
    }
    
    /**
     * @param GUI\NoHtml\Xhtml $body
     * @return GUI\NoHtml\Xhtml|null
     */
    private static function getTrace(GUI\NoHtml\Xhtml $body): ?GUI\NoHtml\Xhtml
    {
        $trace = null;
        $body -> get('table@#trace', function(GUI\NoHtml\Xhtml $table) use(&$trace)
        {
            $trace = $table;
        });
        if($trace === null)
        {
            self::trace();
            $body -> get('table@#trace', function(GUI\NoHtml\Xhtml $table) use(&$trace)
            {
                $trace = $table;
            });
        }
        return $trace;
    }
    
     /**
     * @param \Throwable $ex
     * @return void
     */
    public static function exceptionHandler(\Throwable $ex): void
    {
        self::$dumpTitle = __METHOD__;
        $hasBody = false;
        
        if(defined('XHTML'))
        {
            XHTML -> get('body', function(GUI\NoHtml\Xhtml $body) use(&$hasBody, $ex)
            {
                self::$dumpAsHtml = true;
                $res = self::dump($ex);
                self::$dumpAsHtml = false;
                
                $body -> clear();
                $body -> text($res);

                $hasBody = true; 
            });
        }
        
        if(!$hasBody)
        {
            self::dump($ex);
        }
        self::$dumpTitle = null;
        exit;
    }
    
    /**
     * @return void
     */
    public static function trace(): void
    {
        $path = [];
        $components = debug_backtrace();
        
        for($i=1; $i<count($components); $i++)
        {
            $entry = $components[$i];
            
            $args = [];
            if(isset($entry['args']))
            {
                foreach($entry['args'] as $arg)
                {
                    if(is_object($arg))
                    {
                        $args[] = get_class($arg);
                        continue;
                    }
                    else if(is_string($arg) && !is_numeric($arg))
                    {
                        $args[] = '"'.$arg.'"';
                        continue;
                    }
                    else if(is_array($arg))
                    {
                        $args[] = 'array';
                        continue;
                    }
                    $args[] = $arg;
                }
            }
            
            if(isset($entry['class']))
            {
                if(!isset($entry['file']))
                {
                    $path = [];
                    continue;
                }
                $path[] = [$entry['file'].':'.$entry['line'], $entry['class'].' '.$entry['type'].' '.$entry['function'].'('.implode(', ', $args).')'];
                continue;
            }
            $path[] = [$entry['file'].':'.$entry['line'], $entry['function'].'('.implode(', ', $args).')'];
        }
        $pathReversed = array_reverse($path);
        
        if(defined('XHTML'))
        {
            XHTML -> get('body', function(GUI\NoHtml\Xhtml $body) use($pathReversed)
            {
                $table = $body -> add('table@#trace');
                $table -> add('tr/th@colspan=3') -> text('Trace');

                foreach($pathReversed as $idx => $data)
                {
                    list($line, $call) = $data;

                    $tr = $table -> add('tr');
                    $tr -> add('td') -> text($idx + 1);
                    $tr -> add('td') -> text($line === null ? '' : $line);
                    $tr -> add('td') -> text($call);
                }
            });
        }
        else
        {
            self::dump($pathReversed);
        }
    }
    
    /**
     * @param mixed $arguments
     * @return void
     */
    public static function dump(mixed ...$arguments): ?string
    {
        $self = debug_backtrace()[0];
        $file = IO\File::fromString($self['file']);
        $tokens = self::dumpGetTokens($file, $self['line']);

        if(self::$dumpAsHtml)
        {
            ob_start();
        }
        echo '<div class="dump">';
        echo '<h2>'.(self::$dumpTitle === null ? __METHOD__ : self::$dumpTitle).'</h2>';
        echo '<span>'.$self['file'].':'.$self['line'].'</span><br />';
        echo '<div>';
        foreach($arguments as $idx => $argument)
        {
            echo '<span>';
            $doPrint = is_array($argument) || is_object($argument);
            echo '<span>'.$tokens[$idx].'</span> = <span>';
            if($doPrint)
            {
                echo '<xmp>'.print_r($argument, true).'</xmp>';
            }
            else
            {
                var_dumP($argument);
            }
            echo '</span><br />';
            echo '</span>';
        }
        echo '</div>';
        echo '</div>';
        if(self::$dumpAsHtml)
        {
            return ob_get_clean();
        }
        return null;
    }
    
    /**
     * @param \Php2Core\IO\File $file
     * @param int $line
     * @return array
     */
    private static function dumpGetTokens(IO\File $file, int $line): array
    {
        $tokens = token_get_all($file -> read());
        
        $match = false;
        $open = false;
        $depth = 0;
        $lineTokenParameters = [];
        foreach($tokens as $token)
        {
            if(!$match && $token[0] === Source\Analyzers\PhpAnalyzer\Tokens::T_STRING && strtolower($token[1]) === 'dump' && $token[2] === $line)
            {
                $match = true;
            }
            
            if(!$open && $match && $token === '(')
            {
                $open = true;
            }
            else if($open)
            {
                if($token === ')' && $depth === 0)
                {
                    $open = false;
                    $match = false;
                    break;
                }
                else if($token === '(')
                {
                    $depth++;
                    $lineTokenParameters[] = '(';
                }
                else if($token === ')')
                {
                    $depth--;
                    $lineTokenParameters[] = ')';
                }
                else
                {
                    $lineTokenParameters[] = $token;
                }
            }
        }
        
        $components = [];
        $current = 0;

        $ltdepth = 0;
        foreach($lineTokenParameters as $token)
        {
            $type = is_array($token) && isset($token[0]) ? $token[0] : null;
            $inSubcomponent = $ltdepth > 0;
            
            if($type === Source\Analyzers\PhpAnalyzer\Tokens::T_WHITESPACE && !$inSubcomponent)
            {
                continue;
            }
            else if($token === ',' && !$inSubcomponent)
            {
                $current++;
                continue;
            }
            
            if($token === '(')
            {
                $ltdepth++;
                $components[$current] .= '(';
            }
            else if($token === ')')
            {
                $ltdepth--;
                $components[$current] .= ')';
            }
            else
            {
                if(!isset($components[$current]))
                {
                    $components[$current] = '';
                }
                $components[$current] .= $type === null ? $token : $token[1];
            }
            
        }
        
        return $components;
    }
}