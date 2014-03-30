<?php
namespace Bun\Core\Config;

/**
 * Class ContainerConfig
 *
 * @package Bun\Core\Config
 */
class ContainerConfig extends AbstractConfig
{
    protected $name = 'container';

    protected $config = array(
        'container'                   => array(
            'aware' => array(
                'Bun\\Core\\Config\\ConfigAwareInterface'                => array(
                    'setConfig' => '@bun.core.config'
                ),
                'Bun\\Core\\Container\\ContainerAwareInterface'          => array(
                    'setContainer' => '@bun.core.container'
                ),
                'Bun\\Core\\Http\\RequestAwareInterface'                 => array(
                    'setRequest' => '@bun.core.http.request'
                ),
                'Bun\\Core\\ObjectMapper\\ObjectManagerAwareInterface'   => array(
                    'setObjectManager' => '@bun.core.object_manager'
                ),
                'Bun\\Core\\Event\\EventDispatcherAwareInterface'        => array(
                    'setEventDispatcher' => '@bun.core.event_dispatcher'
                ),
                'Bun\\Core\\Cache\\CacheDriverAwareInterface'            => array(
                    'setCacheDriver' => '@bun.core.cache'
                ),
                'Bun\\Core\\Cache\\FileCacheDriverAwareInterface'        => array(
                    'setFileCacheDriver' => '@bun.core.file_cache'
                ),
                'Bun\\Core\\Repository\\RepositoryManagerAwareInterface' => array(
                    'setRepositoryManager' => '@bun.core.repository_manager'
                ),
                'Bun\\Core\\Tests\\TestDependency2AwareInterface'        => array(
                    'setTestDependency2' => '@bun.test_dependency2'
                ),
            )
        ),
        'bun.test_dependency2'        => array(
            'class' => 'Bun\\Core\\Tests\\TestDependency2'
        ),
        'bun.test_dependency1'        => array(
            'class' => 'Bun\\Core\\Tests\\TestDependency1'
        ),
        'bun.test_service'            => array(
            'class' => 'Bun\\Core\\Tests\\TestService',
            'aware' => array(
                'setTestDependency1' => '@bun.test_dependency1'
            ),
        ),
        'bun.core.config'             => array(
            'class' => 'Bun\\Core\\Config\\ApplicationConfig'
        ),
        'bun.core.cache'              => array(
            'class' => 'Bun\\Core\\Cache\\FileCacheDriver'
        ),
        'bun.core.file_cache'         => array(
            'class' => 'Bun\\Core\\Cache\\FileCacheDriver'
        ),
        'bun.core.http.request'       => array(
            'class' => 'Bun\\Core\\Http\\Request'
        ),
        'bun.core.event_dispatcher'   => array(
            'class' => 'Bun\\Core\\Event\\EventDispatcher'
        ),
        'bun.core.router'             => array(
            'class' => 'Bun\\Core\\Router\\Router'
        ),
        'bun.core.file_storage'       => array(
            'class' => 'Bun\\Core\\Storage\\FileStorage'
        ),
        'bun.core.object_mapper'      => array(
            'class' => 'Bun\\Core\\ObjectMapper\\FileObjectMapper'
        ),
        'bun.core.object_manager'     => array(
            'class' => 'Bun\\Core\\ObjectMapper\\ObjectMapperManager'
        ),
        'bun.core.repository_manager' => array(
            'class' => 'Bun\\Core\\Repository\\RepositoryManager'
        ),
    );
}