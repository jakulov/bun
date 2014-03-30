<?php
namespace Bun;

/**
 * Class Autoload
 *
 * @package Bun
 */
class Autoload
{
    protected $baseDirectory = __DIR__;
    protected $prefix = __NAMESPACE__;

    /**
     * @param $className
     */
    public function autoload($className)
    {
        if (strpos($className, $this->prefix) === 0) {
            $file = $this->baseDirectory . DIRECTORY_SEPARATOR . '../' . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
            require $file;
        }
    }
}

spl_autoload_register(array(new Autoload(), 'autoload'), true);

require_once __DIR__ .'/const.php';