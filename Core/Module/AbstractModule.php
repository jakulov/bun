<?php
namespace Bun\Core\Module;

/**
 * Class AbstractModule
 *
 * @package Bun\Core\Module
 */
abstract class AbstractModule implements ModuleInterface
{
    protected $version = 'experimental';
    protected $description = 'no description available';
    protected $source;
    protected $sourceType = self::MODULE_SOURCE_TYPE_GITHUB;
    protected $sourceBranch = 'master';
    protected $dependencies = array();
    protected $config = array();

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * @return string
     */
    public function getSourceBranch()
    {
        return $this->sourceBranch;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}