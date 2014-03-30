<?php
namespace Bun\Core\File;

/**
 * Class Directory
 *
 * @package Bun\Core\File
 */
class Directory
{
    /**
     * @param $dir
     */
    public static function removeRecursive($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        self::removeRecursive($dir . "/" . $object);
                    }
                    else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}