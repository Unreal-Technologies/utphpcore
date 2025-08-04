<?php
namespace Utphpcore\IO\Common;

interface IIniFile extends \Utphpcore\IO\IFile
{
    public function parse(bool $sections = true): array;
}
