<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;

use Psr\Container\ContainerInterface;

final class InjectorFactory
{
    public function __invoke(ContainerInterface $container): Injector
    {
        return new Injector($container);
    }
}
