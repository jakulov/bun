<?php
namespace Bun\Core\Model;

/**
 * Interface AbstractModelInterface
 *
 * @package Bun\Model
 */
interface ModelInterface
{
    public function getTableName();

    public function getSchema();

    public function getId();

    /**
     * @param $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return bool
     */
    public function isNewObject();

    public function getObjectMapperName();

    public function getRepositoryName();

    public function field($fieldName);
}