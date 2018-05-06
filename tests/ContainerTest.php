<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests;


use m0rtis\SimpleBox\AutoWiringInjectorFactory;
use m0rtis\SimpleBox\Container;
use m0rtis\SimpleBox\AutoWiringInjector;
use m0rtis\SimpleBox\DependencyInjectorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{
    protected function getContainer(iterable $data = [], bool $di = true): Container
    {
        if ($di) {
            $data[DependencyInjectorInterface::class] = AutoWiringInjectorFactory::class;
        }
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
        $this->assertCount(5, $this->getContainer(range(1,5), false));
    }

    public function testResolve(): void
    {
        $container = $this->getContainer(
            [
                AutoWiringInjector::class => function ($c) {
                    /** @var ContainerInterface $c */
                    return $c->get('test2');
                },
                'test' => AutoWiringInjector::class,
                'test2' => function ($c) {
                    /** @var ContainerInterface $c */
                    $factory = $c->get(AutoWiringInjectorFactory::class);
                    return $factory($c);
                }
            ]
        );
        $test = $container->get('test');
        $this->assertInstanceOf(AutoWiringInjector::class, $test);

        $container2 = $this->getContainer([
            DependencyInjectorInterface::class => AutoWiringInjector::class
        ]);
        $test2 = $container2->get(DependencyInjectorInterface::class);
        $this->assertInstanceOf(AutoWiringInjector::class, $test2);
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
                AutoWiringInjector::class => AutoWiringInjectorFactory::class,
                DependencyInjectorInterface::class => AutoWiringInjector::class
            ]
        );

        $result1 = $container->get(DependencyInjectorInterface::class);
        $result2 = $container->get(DependencyInjectorInterface::class);

        $this->assertSame($result1, $result2);

        return $container;
    }

    /**
     * @depends testSharedRetrieving
     * @param Container $container
     */
    public function testCreate(Container $container): void
    {
        $result1 = $container->create(DependencyInjectorInterface::class);
        $result2 = $container->create(DependencyInjectorInterface::class);

        $this->assertNotSame($result1, $result2);
    }

    public function testObjectInsteadOfArray(): void
    {
        $testArray = [
            'test' => 'passed',
            'build' => 'passed',
            'coverage' => 100
        ];
        /** @var Container $container */
        $container = new Container(new \ArrayObject($testArray));

        $result = [];
        foreach ($container as $key => $item) {
            $result[$key] = $item;
        }
        $this->assertEquals($testArray, $result);
        $this->assertCount(3, $container);
    }
}