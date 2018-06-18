<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests\Mocks;

use Psr\Container\ContainerInterface;

final class DependencyOne
{
    public function __construct(DependencyTwo $depTwo, ContainerInterface $container)
    {
    }
}
