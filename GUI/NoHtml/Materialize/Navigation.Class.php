<?php
namespace Utphpcore\GUI\NoHtml\Materialize;

class Navigation extends Submenu
{
    #[\Override]
    public function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * @param string $text
     * @param \Closure $callback
     * @return void
     */
    public function submenu(string $text, \Closure $callback): void
    {
        $sub = new Submenu();
        $this -> aChildren[] = [$text, $sub];
        $callback($sub);
    }
    
    /**
     * @param \Utphpcore\GUI\NoHtml\IXhtml $container
     * @param Color $color
     * @param string $title
     * @return void
     */
    public function navBar(\Utphpcore\GUI\NoHtml\IXhtml $container, Color $color, string $title): void
    {
        $links = $this -> toArray();
        $dropdownCounter = 0;
        
        $container -> add('div@.navbar-fixed/nav/div@.nav-wrapper '.$color, function(\Utphpcore\GUI\NoHtml\Xhtml $wrapper) use($links, &$dropdownCounter, $container, $color, $title)
        {
            $logo = $wrapper -> add('a@.brand-logo');
            $logo -> text($title);
            $logo -> attributes() -> set('href', '/');

            $wrapper -> add('ul@.right hide-on-med-and-down&#nav-mobile', function(\Utphpcore\GUI\NoHtml\Xhtml $ul) use($links, &$dropdownCounter, $container, $color)
            {
                foreach($links as $link)
                {
                    if($link === null)
                    {
                        $ul -> add('li@.vertical-divider');
                        continue;
                    }

                    $ul -> add('li/a', function(\Utphpcore\GUI\NoHtml\XHtml $a) use($link, &$dropdownCounter, $container, $color)
                    {
                        list($text, $object, $target) = $link;

                        $a -> text($text);

                        if(is_array($object))
                        {
                           $a -> attributes() -> set('href', '#!');
                           $a -> attributes() -> set('class', 'dropdown-trigger');
                           $a -> attributes() -> set('data-target', 'dropdown'.$dropdownCounter);
                           new \Utphpcore\GUI\NoHtml\Materialize\Icon($a, Icon\Icons::ArrowDropDown, Icon\Alignment::Right);

                           $dropLinks = $object;

                           $container -> add('ul@.dropdown-content '.$color.'&#dropdown'.$dropdownCounter, function(\Utphpcore\GUI\NoHtml\XHtml $ul) use($dropLinks)
                           {
                                foreach($dropLinks as $link)
                                {
                                    if($link === null)
                                    {
                                        $ul -> add('li@.divider');
                                        continue;
                                    }

                                    $ul -> add('li/a', function(\Utphpcore\GUI\NoHtml\XHtml $a) use($link)
                                    {
                                        list($text, $object, $target) = $link;

                                        $a -> text($text);
                                        $a -> attributes() -> set('href', $object);
                                        if($target !== null)
                                        {
                                            $a -> attributes() -> set('target', $target);
                                        }
                                    });
                                }
                           });

                           $dropdownCounter++;
                        }
                        else
                        {
                           $a -> attributes() -> set('href', $object);
                           if($link[2] !== null)
                           {
                               $a -> attributes() -> set('target', $target);
                           }
                        }
                    });
                }
            });
        });

        if($dropdownCounter !== 0)
        {
            \Utphpcore\Core::register_shutdown_body(__CLASS__, function(\Utphpcore\GUI\NoHtml\Xhtml $body)
            {
                $body -> javascript() -> text('$(document).ready(function() 
                {
                    $(".dropdown-trigger").dropdown({ constrainWidth: false });
                });');
            });
        }
    }
}
