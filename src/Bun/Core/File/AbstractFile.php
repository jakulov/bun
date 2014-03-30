<?php
namespace Bun\Core\File;

/**
 * Class AbstractFile
 *
 * @package Bun\Core\File
 */
abstract class AbstractFile implements FileInterface
{
    protected $name;
    protected $extension;
    protected $path;

    protected $size;
    protected $modifiedTime;
    protected $content;

    /**
     * @param null $filename
     * @param bool $create
     */
    public function __construct($filename = null, $create = false)
    {
        if ($filename !== null) {
            if ($create) {
                $this->create($filename);
            }
            else {
                $this->open($filename);
            }
        }
    }

    /**
     * @param $filename
     * @return bool
     */
    public static function exists($filename)
    {
        return file_exists($filename);
    }

    /**
     * @param $filename
     * @return bool
     */
    public function open($filename)
    {
        if (file_exists($filename) && is_readable($filename)) {
            $nameParts = explode(DIRECTORY_SEPARATOR, $filename);
            $this->name = $nameParts[count($nameParts) - 1];
            $namePartsExt = explode('.', $this->name);
            if (count($namePartsExt) > 1) {
                $this->extension = end($namePartsExt);
            }
            unset($nameParts[count($nameParts) - 1]);
            $this->path = join(DIRECTORY_SEPARATOR, $nameParts);

            return true;
        }

        return false;
    }

    /**
     * @param $filename
     *
     * @return bool
     */
    public function create($filename)
    {
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        if (touch($filename)) {
            return $this->open($filename);
        }

        return false;
    }

    /**
     * @param bool $withoutExtension
     *
     * @return string
     */
    public function getName($withoutExtension = false)
    {
        return $withoutExtension ?
            ($this->extension !== null ?
                str_replace('.' . $this->extension, '', $this->name) :
                $this->name
            ) :
            $this->name;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        if ($this->size === null) {
            $this->size = filesize($this->getFullName());
        }

        return $this->size;
    }

    /**
     * @return bool
     */
    public function reload()
    {
        if ($this->open($this->getFullName())) {
            $this->getContent(true);
            $this->size = null;
            $this->modifiedTime = null;

            return true;
        }

        return false;
    }

    /**
     * @return bool|int
     */
    public function write()
    {
        if (is_writable($this->getFullName())) {
            return file_put_contents($this->getFullName(), $this->content);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function remove()
    {
        if (is_writable($this->getFullName())) {
            return unlink($this->getFullName());
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->name;
    }

    /**
     * @param bool $reload
     * @return string
     */
    public function getContent($reload = false)
    {
        if ($this->content === null || $reload) {
            $this->content = file_get_contents($this->getFullName());
        }

        return $this->content;
    }

    /**
     * @param $content
     * @param bool $write
     *
     * @return bool|int|null
     */
    public function setContent($content, $write = false)
    {
        $this->content = $content;
        if ($write) {
            return $this->write();
        }

        return null;
    }

    /**
     * @param $content
     * @param bool $write
     * @return bool|int|null
     */
    public function appendContent($content, $write = false)
    {
        $this->content .= $content;
        if ($write) {
            return $this->write();
        }

        return null;
    }

    /**
     * @param $fileName
     * @param $content
     * @return int
     */
    public static function append($fileName, $content)
    {
        return file_put_contents($fileName, $content, FILE_APPEND);
    }

    /**
     * @param $sourceFileName
     * @param $destFileName
     * @return bool
     */
    public static function copy($sourceFileName, $destFileName)
    {
        return copy($sourceFileName, $destFileName);
    }

    /**
     * @return int
     */
    public function getModifiedTime()
    {
        if ($this->modifiedTime === null) {
            $this->modifiedTime = filemtime($this->getFullName());
        }

        return $this->modifiedTime;
    }

    /**
     * @param $timestamp
     *
     * @return bool
     */
    public function modifiedLater($timestamp)
    {
        return $this->getModifiedTime() < $timestamp;
    }

}