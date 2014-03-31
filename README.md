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
    -- var // application files, cache, etc (web user needs access to write here)
    -- vendor // installed vendors

You can use any directory's names. Default names defined as constants in Bun\\Core\\Application class
You can redefine any of them in your own application index file. Create your application class:

    <?php
    # src/AppName/Application.php
    namespace AppName;

    class Application extends Bun\Core\Application
    {

    }

Next create auto loader for your application:

    <?php
    # src/AppName/Autoload.php
    namespace AppName;

    class Autoload extends \Bun\Autoload
    {
        protected $baseDirectory = __DIR__;
        protected $prefix = __NAMESPACE__;
    }

    spl_autoload_register(array(new Autoload(), 'autoload'), true);

Now in app/index.php file run you application.

    <?php
    # app/index.php

    require __DIR__ .'/../vendor/autoload.php'; // require vendors
    require __DIR__ .'/../src/Autoload.php'; // require application
    define('ENV', 'dev');
    $app = new AppName\Application(ENV);
    $app->run();

Your application is ready to development now!

## Web Server setup

Setting up Nginx to work with Bun Framework application is quite easy:

    upstream phpfcgi {
        server unix:/var/run/php5-fpm.sock;
    }

    server {
            listen 80;
            root /home/yakov/projects/app_name/app;
            index index.php;
            server_name app_name;

            rewrite ^/index\.php/?(.*)$ /$1 permanent;

            location / {
                    index index.php;
                    try_files $uri @rewriteap;
            }

            location @rewriteap {
                    rewrite  ^(.*)$ /index.php/$1 last;
            }

            location ~ ^/(index)\.php(/|$) {
                    fastcgi_pass phpfcgi;
                    fastcgi_split_path_info ^(.+\.php)(/.*)$;
                    include fastcgi_params;
                    fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
            }
    }


## Templates
Bun Framework controller allows to use native phtml templates, Twig templates and Smarty2 templates. To use last ones you need to install Twig and Smarty2 packages in your application by yourself. For example with composer:

    "require": {
        "bun/bun": "master-dev",
        "twig/twig" : "1.*"
    }

