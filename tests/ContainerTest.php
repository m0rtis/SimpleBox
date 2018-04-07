<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests;


use m0rtis\SimpleBox\Container;
use m0rtis\SimpleBox\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{
    protected function getContainer(array $data = [], array $definitions = []): Container
    {
        return new Container($data, $definitions);
    }

    public function testOffsetUnset(): void
    {
        $container = $this->getContainer(['testKey' => 'testValue']);
        $this->assertArrayHasKey('testKey', $container);

        unset($container['testKey']);
        $this->assertArrayNotHasKey('testKey', $container);
    }

    public function testCount(): void
    {
        $this->assertCount(5, $this->getContainer(range(1,5)));
    }

    public function testResolve(): void
    {
        $container = $this->getContainer(
            [
                Container::class => ContainerFactory::class
            ],
            [
                'test' => Container::class,
                'test2' => ContainerFactory::class
            ]
        );
        $test = $container->get('test');
        $test2 = $container->get('test2');

        $this->assertInstanceOf(Container::class, $test);
        $this->assertInstanceOf(Container::class, $test2);
    }

    public function testNotFoundException(): void
    {
        $container = $this->getContainer();

        $this->expectException(NotFoundExceptionInterface::class);
        $container->get('invalidKey');
    }
}