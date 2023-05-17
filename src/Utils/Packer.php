<?php

namespace PedroACF\Invoicing\Utils;
use FilesystemIterator;
use Phar;
use PharData;
class Packer extends PharData
{
    public function __construct($filename, $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO, $alias = null, $format = Phar::TAR)
    {
        parent::__construct($filename, $flags, $alias, $format);
    }
}
