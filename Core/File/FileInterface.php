<?php
namespace Bun\Core\File;

/**
 * Interface FileInterface
 *
 * @package Bun\Core\File
 */
interface FileInterface
{
    public function getName();

    public function getSize();

    public function getPath();

    public function getModifiedTime();

    public function getFullName();

    public function getContent($reload = false);

    public function setContent($content, $write = false);

    public function write();

    public function reload();

    public function open($filename);
}