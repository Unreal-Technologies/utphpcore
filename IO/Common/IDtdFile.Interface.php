<?php

namespace Utphpcore\IO\Common;

interface IDtdFile extends \Utphpcore\IO\IFile
{
    public function systemId(): ?string;
}
