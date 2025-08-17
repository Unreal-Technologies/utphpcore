<?php
namespace Utphpcore\GUI\NoHtml\Materialize;

class FloatingActionButton 
{
    /** 
     * @var Icon\Icons|null
     */
    private ?Icon\Icons $icon = null;
    
    /**
     * @var Color|null
     */
    private ?Color $color = null;
    
    /**
     * @var array
     */
    private array $links = [];
    
    /**
     * @param Icon\Icons $icon
     * @param Color $color
     */
    function __construct(Icon\Icons $icon, Color $color)
    {
        $this -> icon = $icon;
        $this -> color = $color;
    }
    
    /**
     * @param string $url
     * @param Icon\Icons $icon
     * @param Color $color
     * @param string $tooltip
     * @param string $target
     * @return void
     */
    public function link(string $url, Icon\Icons $icon, Color $color, string $tooltip = null, string $target = null): void
    {
        $this -> links[] = [
            'url' => $url,
            'icon' => $icon,
            'color' => $color,
            'tooltip' => $tooltip,
            'target' => $target
        ];
    }
    
    /**
     * @param \Utphpcore\GUI\NoHtml\Xhtml $container
     * @return void
     */
    public function render(\Utphpcore\GUI\NoHtml\Xhtml $container = null, $class = null, array $jsArguments = []): void
    {
        $hash = md5(serialize($jsArguments));
        $reference = 'fixed-action-btn'.$hash;
        if($class !== null)
        {
            $reference = $class.$hash;
        }
        if($container === null)
        {
            $container = XHTML -> get('body')[0];
        }
        
        $fabAdmin = $container -> add('div@.fixed-action-btn'.($class === null ? null : ' '.$class));
        new Icon($fabAdmin -> add('a@.btn-floating btn-large '.$this -> color), $this -> icon);
        
        $fabAdminUl = $fabAdmin -> add('ul');
        foreach($this -> links as $link)
        {
            $a = $fabAdminUl -> add('li/a@.btn-floating '.$link['color']);
            $a -> attributes() -> set('href', $link['url']);
            if($link['target'] !== null)
            {
                $a -> attributes() -> set('target', $link['target']);
            }
            if($link['tooltip'] !== null)
            {
                new Tooltip($a, $link['tooltip']);
            }
            new Icon($a, $link['icon']);
        }
        
        \Utphpcore\Core::register_shutdown_body(__CLASS__.'@'.$reference, function(\Utphpcore\GUI\NoHtml\Xhtml $body) use($jsArguments)
        {
            $args = [];
            foreach($jsArguments as $k => $v)
            {
                $args[] = $k.': '.$v;
            }

            $body -> javascript() -> text('document.addEventListener(\'DOMContentLoaded\', function() '
            . '{'
                . 'var elems = document.querySelectorAll(\'.fixed-action-btn\');'
                . 'var instances = M.FloatingActionButton.init(elems, {'.implode(', ', $args).'});'
            . '});'
            );
        });
    }
}
