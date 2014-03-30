<?php
namespace Bun\Core;

/**
 * Interface ApplicationInterface
 *
 * @package Bun\Core
 */
interface ApplicationInterface
{
    const APPLICATION_ENV_DEV = 'dev';
    const APPLICATION_ENV_PROD = 'prod';
    const APPLICATION_ENV_TEST = 'test';

    /**
     * @return mixed
     */
    public function getApplicationName();

    /**
     * @return mixed
     */
    public function getApplicationDir();

    /**
     * @return mixed
     */
    public function run();
}