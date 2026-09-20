<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Sovic\\Cms\\Ai\\', '../../Ai/')
        ->exclude([
            '../../Ai/Dto/',
            '../../Ai/Exception/',
        ]);
};
