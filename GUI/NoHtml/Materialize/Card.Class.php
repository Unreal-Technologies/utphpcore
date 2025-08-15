<?php
namespace Utphpcore\GUI\NoHtml\Materialize;

class Card
{
    /**
     * @param \Utphpcore\GUI\NoHtml\Xhtml $container
     * @param string $title
     * @param string $description
     * @param \Utphpcore\Data\Collections\KeyValuePair $links
     */
    function __construct(\Utphpcore\GUI\NoHtml\Xhtml $container, string $title, string $description, \Utphpcore\Data\Collections\KeyValuePair $links)
    {
        $self = $container -> add('div@.col s3/div@.card');
        
        $content = $self -> add('div@.card-content');
        $content -> add('span@.card-title') -> text($title);
        $content -> add('p') -> text($description);
        
        $action = $self -> add('div@.card-action');
        
        foreach($links -> toArray() as $k => $v)
        {
            $a1 = $action -> add('a');
            $a1 -> text($k);
            $a1 -> attributes() -> set('href', $v);
            $a1 -> attributes() -> set('target', '_blank');
        }
    }
}