<?php
namespace Bun\Migration;

/**
 * Interface MigrationInterface
 *
 * @package Bun\Migration
 */
interface MigrationInterface
{
    /**
     * @param $dateTime
     * @return $this
     */
    public function setDateTime($dateTime);

    /**
     * @param $name
     * @return $this
     */
    public function setName($name);
}