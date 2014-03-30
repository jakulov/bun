<?php
namespace Bun\PDO\Model;

use Bun\Core\Model\AbstractModel;

/**
 * Class AbstractPdoMapperModel
 *
 * @package Bun\PDO\Model
 */
class AbstractPdoMapperModel extends AbstractModel
{
    protected $objectMapperName = 'bun.pdo.object_mapper';
    protected $repositoryName = 'Bun\\PDO\\Repository\\PdoRepository';
}