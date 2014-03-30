<?php
namespace Bun\Core\Module;

/**
 * Interface ModuleInterface
 *
 * @package Bun\Core\Module
 */
interface ModuleInterface
{
    const MODULE_SOURCE_TYPE_GITHUB = 'github';

    public function getVersion();

    public function getSource();

    public function getDescription();

    public function getSourceType();

    public function getSourceBranch();

    public function getDependencies();

    public function getConfig();
}