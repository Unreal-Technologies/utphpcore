<?php

namespace Utphpcore\IO\Common;

class Xml extends \Utphpcore\IO\File implements IXmlFile
{
    /**
     * @param string $path
     * @param bool $requiresExtension
     * @throws \Exception
     */
    #[\Override]
    public function __construct(string $path, bool $requiresExtension = true)
    {
        parent::__construct($path);

        if ($requiresExtension && strtolower($this -> extension()) != 'xml') {
            throw new \Exception('"' . $path . '" does not have the .xml extension');
        }
    }

    /**
     * 
     * @return \Utphpcore\IO\Xml\IXmlDocument|null
     */
    #[\Override]
    public function document(): ?\Utphpcore\IO\Xml\IXmlDocument
    {
        if (!$this -> exists()) {
            return null;
        }
        return \Utphpcore\IO\Xml\Document::createFromFile($this) -> asDocument();
    }
}
