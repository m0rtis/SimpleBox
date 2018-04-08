<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests;


use m0rtis\SimpleBox\Container;
use m0rtis\SimpleBox\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{
    protected function getContainer(array $data = []): Container
    {
        return new Container($data);
    }

    public function testOffsetMethods(): void
    {
        $container = $this->getContainer(['testKey' => 'testValue']);
        $this->assertArrayHasKey('testKey', $container);
        $this->assertSame('testValue', $container['testKey']);

        unset($container['testKey']);
        $this->assertArrayNotHasKey('testKey', $container);

        $container['testKey'] = 'test';
        $this->assertSame('test', $container['testKey']);
    }

    public function testIterate(): void
    {
        $container = $this->getContainer([
            'firstKey' => 'firstValue',
            'secondKey' => 'secondValue'
        ]);
        $result = [];
        foreach ($container as $key => $item) {
            $result[$item] = $key;
        }

        $this->assertArrayHasKey('secondValue', $result);
        $this->assertEquals('firstKey', $result['firstValue']);
    }

    public function testCount(): void
    {
        $this->assertCount(5, $this->getContainer(range(1,5)));
    }

    public function testResolve(): void
    {
        $container = $this->getContainer(
            [
                Container::class => function ($c) {
                    /** @var ContainerInterface $c */
                    return $c->get('test2');
                },
                'test' => Container::class,
                'test2' => function ($c) {
                    /** @var ContainerInterface $c */
                    $factory = $c->get(ContainerFactory::class);
                    return $factory();
                }
            ]
        );
        $test = $container->get('test');
        $this->assertInstanceOf(Container::class, $test);

        $container2 = $this->getContainer([
            Container::class => ContainerFactory::class
        ]);
        $test2 = $container2->get(Container::class);
        $this->assertInstanceOf(Container::class, $test2);
    }

    public function testNotFoundException(): void
    {
        $container = $this->getContainer();

        $this->expectException(NotFoundExceptionInterface::class);
        $container->get('invalidKey');
    }

    public function testSharedRetrieving(): Container
    {
        $container = $this->getContainer([
                Container::class => ContainerFactory::class,
                ContainerInterface::class => Container::class
            ]
        );

        $result1 = $container->get(ContainerInterface::class);
        $result2 = $container->get(ContainerInterface::class);

        $this->assertSame($result1, $result2);

        return $container;
    }

    /**
     * @depends testSharedRetrieving
     * @param Container $container
     */
    public function testBuild(Container $container): void
    {
        $result1 = $container->build(ContainerInterface::class);
        $result2 = $container->build(ContainerInterface::class);

        $this->assertNotSame($result1, $result2);
    }
}