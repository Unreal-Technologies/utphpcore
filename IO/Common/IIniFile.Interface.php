<?php
namespace Utphpcore\IO\Common;
require_once(__DIR__.'/../IFile.Interface.php');

interface IIniFile extends \Utphpcore\IO\IFile
{
    public function parse(bool $sections = true): array;
}
