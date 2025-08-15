<?php
namespace Utphpcore\GUI;

class ToDo 
{
    function __construct(string ...$arguments)
    {
        $self = debug_backtrace()[0];
        
        if(defined('XHTML'))
        {
            $hasRow = false;
            XHTML -> get('body/div@.container/div@.row', function() use(&$hasRow)
            {
                $hasRow = true;
            });
            if(!$hasRow)
            {
                XHTML -> get('body/div@.container')[0] -> add('div@.row');
            }
            
            XHTML -> get('body/div@.container/div@.row', function(NoHtml\Xhtml $container) use($arguments, $self)
            {
                $children = $container -> children();
                $container -> clear(NoHtml\Clearmodes::Children);

                $table = $container -> add('div@.col s6 offset-s3/table');
                $tHead = $table -> add('thead');
                
                $tHead -> add('tr/th@colspan=2&.center orange/h2') -> text('ToDo');
                $tHead -> add('tr/th@colspan=2&.center red') -> text($self['file'].'::'.$self['line']);
                
                $tBody = $table -> add('tbody');
                
                foreach($arguments as $idx => $arg)
                {
                    $tr = $tBody -> add('tr');
                    $tr -> add('td@.blue') -> text('# '.($idx + 1));
                    $tr -> add('td@.green') -> text($arg);
                }
                
                $container -> add('div@.col s3') -> text('&nbsp;');
                foreach($children as $child)
                {
                    $container -> append($child);
                }
            });
        }
    }
}
