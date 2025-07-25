<?php

namespace Utphpcore\IO\Common;

interface IXmlFile extends \Utphpcore\IO\IFile
{
    public function document(): ?\Utphpcore\IO\Xml\IXmlDocument;
}
