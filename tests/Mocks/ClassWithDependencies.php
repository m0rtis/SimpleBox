<?php
declare(strict_types=1);

namespace m0rtis\SimpleBox\Tests\Mocks;

final class ClassWithDependencies
{
    private $config;

    public function __construct(DependencyOne $depOne, iterable $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $parameter
     * @return mixed
     */
    public function getConfigParameter(string $parameter)
    {
        if (isset($this->config[$parameter])) {
            return $this->config[$parameter];
        }
        return null;
    }
}
