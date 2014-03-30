<?php
namespace Bun\PDO\Config;

use Bun\Core\Config\AbstractConfig;

/**
 * Class ContainerConfig
 *
 * @package Bun\MySQL\Config
 */
class ContainerConfig extends AbstractConfig
{
    protected $name = 'container';

    protected $config = array(
        'container'                    => array(
            'aware' => array(
                'Bun\\PDO\\PdoStorageAwareInterface' => array(
                    'setPdoStorage' => '@bun.pdo.storage'
                )
            ),
        ),
        'bun.pdo.storage'              => array(
            'class' => 'Bun\\PDO\\PdoStorage'
        ),
        'bun.pdo.repository'           => array(
            'class' => 'Bun\\PDO\\Repository\\PdoRepository'
        ),
        'bun.pdo.object_mapper'        => array(
            'class' => 'Bun\\PDO\\ObjectMapper\\PdoObjectMapper'
        ),
        'bun.pdo.model_generator'      => array(
            'class' => 'Bun\\PDO\\Generator\\ModelGenerator'
        ),
        'bun.pdo.event_listener.query' => array(
            'class' => 'Bun\\PDO\\EventListener\\QueryEventListener',
        ),
    );
}