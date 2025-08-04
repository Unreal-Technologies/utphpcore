<?php
namespace Utphpcore\IO\Common;

class Ini extends \Utphpcore\IO\File implements IIniFile
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

        if ($requiresExtension && strtolower($this -> extension()) != 'ini') {
            throw new \Exception('"' . $path . '" does not have the .ini extension');
        }
    }
    
    /**
     * @param bool $sections
     * @return array
     */
    #[\Override]
    public function parse(bool $sections = true): array
    {
        return parse_ini_file($this -> path(), $sections);
    }
}
