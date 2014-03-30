bun
===

Bun Framework

## Installation
Install via composer

    {
        "require": {
            "bun/bun": "master-dev"
        }
    }

## Creating application
First you need to have base directory hierarchy:

    -- app // web root
    -- -- public // web static files
    -- -- index.php // application start file
    -- src // application sources
    -- var // application files
    -- vendor // installed vendors

You can use any directory's names. Default names defined as constants in Bun\\Core\\Application class
You can redefine any of them in your own application index file. Create your application class:

    <?php
    # src/AppName/Application.php
    namespace AppName;

    class Application extends Bun\Core\Application
    {

    }

Now in app/index.php file run you application.

    <?php
    # app/index.php

    define('ENV', 'dev');
    $app = new AppName\Application(ENV);
    $app->run();

Your application is ready to development now.

## Templates
Bun Framework controller allows to use native phtml templates, Twig templates and Smarty2 templates. To use last ones you need to install Twig and Smarty2 packages in your application by yourself.