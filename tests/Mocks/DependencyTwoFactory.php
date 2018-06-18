<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests\Mocks;

use Psr\Container\ContainerInterface;

final class DependencyTwoFactory
{
    public function __invoke(ContainerInterface $container): DependencyTwo
    {
        return new DependencyTwo();
    }

    public static function getInstance(): DependencyTwo
    {
        return new DependencyTwo();
    }
}
