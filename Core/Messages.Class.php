<?php
namespace Utphpcore\Core;

class Messages extends \Utphpcore\Data\Stack
{
    /**
     * @param mixed $data
     * @return void
     */
    public function push(mixed $data): void 
    {
        $now = \DateTime::createFromFormat('U.u', microtime(true));
        parent::push('<b>'.$now -> format('d-m-Y H:i:s\.u').'</b>&nbsp;: '.$data);
    }
}
