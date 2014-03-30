<?php
namespace Bun\Core\Model;

/**
 * Class AbstractFileMapperModel
 *
 * @package Bun\Core\Model
 */
abstract class AbstractFileMapperModel extends AbstractModel
{
    protected $objectMapperName = 'bun.core.object_mapper';
    protected $repositoryName = 'Bun\\Core\\Repository\\CoreRepository';
}