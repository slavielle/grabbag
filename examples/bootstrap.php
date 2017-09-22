<?php

function initBootstrap()
{
    $basePath = realpath(dirname(__FILE__));
    $loaded = FALSE;
    foreach (['/../vendor/autoload.php', '/../../../autoload.php'] as $bootstrapLocation) {
        if (file_exists($basePath . $bootstrapLocation)) {
            require_once $basePath . $bootstrapLocation;
            $loaded = TRUE;
        }
    }
    if(!$loaded){
        throw new \Exception('autoload.php not found.');
    }
}

initBootstrap();
