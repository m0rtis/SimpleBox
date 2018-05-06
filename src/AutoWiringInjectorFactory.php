<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;


use Psr\Container\ContainerInterface;

final class AutoWiringInjectorFactory
{
    public function __invoke(ContainerInterface $container): AutoWiringInjector
    {
        return new AutoWiringInjector($container);
    }
}