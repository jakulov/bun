<?php
namespace Bun\Core\File;

/**
 * Class UploadedFile
 *
 * @package Bun\Core\File
 */
class UploadedFile extends File
{
    /** @var string */
    protected $clientName;
    protected $type;
    protected $tmpName;
    protected $error;
    protected $size;

    /**
     * @param null $file
     */
    public function __construct($file)
    {
        $this->clientName = $file['name'];
        $this->type = $file['type'];
        $this->tmpName = $file['tmp_name'];
        $this->error = $file['error'];
        $this->size = $file['size'];

        $this->path = dirname($this->tmpName);
        $this->name = end(explode('/', $this->tmpName));
    }

    /**
     * @return string
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * @return string
     */
    public function getClientExtension()
    {
        $fileNameParts = explode('.', $this->clientName);

        return end($fileNameParts);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getTmpName()
    {
        return $this->tmpName;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }
}