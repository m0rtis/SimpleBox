<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests\Mocks;

final class DependencyOne
{
    public function __construct(DependencyTwo $depTwo)
    {
    }
}
