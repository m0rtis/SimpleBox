<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests;

use m0rtis\Picklock\Picklock;
use m0rtis\SimpleBox\Container;
use m0rtis\SimpleBox\ContainerException;
use m0rtis\SimpleBox\Tests\Mocks\ClassWithDependencies;
use m0rtis\SimpleBox\Tests\Mocks\DependencyOne;
use m0rtis\SimpleBox\Tests\Mocks\DependencyTwo;
use m0rtis\SimpleBox\Tests\Mocks\DependencyTwoFactory;
use m0rtis\SimpleBox\Tests\Mocks\Invokable;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{
    protected function getContainer(iterable $data = []): Container
    {
        return new Container($data);
    }

    public function testArrayAccessMethods(): void
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
            if (\is_string($item)) {
                $result[$item] = $key;
            }
        }

        $this->assertArrayHasKey('secondValue', $result);
        $this->assertEquals('firstKey', $result['firstValue']);
    }

    public function testCount(): void
    {
        $this->assertCount(5, $this->getContainer(range(1, 5)));
    }

    public function hasDataProvider(): iterable
    {
        yield ['testKey', false];
        yield [ContainerInterface::class, $this->getContainer()];
        yield [ClassWithDependencies::class, 'testPassed'];
    }

    /**
     * @dataProvider hasDataProvider
     * @param string $key
     * @param $value
     */
    public function testHas(string $key, $value): void
    {
        $container = $this->getContainer([$key => $value]);

        $this->assertTrue($container->has($key));
    }

    public function testResolve(): void
    {
        $container = $this->getContainer(
            [
                'test' => ClassWithDependencies::class,
                ClassWithDependencies::class => function ($c) {
                    /** @var ContainerInterface $c */
                    return new ClassWithDependencies($c->get(DependencyOne::class), []);
                },
                DependencyOne::class => function ($c) {
                    /** @var ContainerInterface $c */
                    return new DependencyOne($c->get(DependencyTwoFactory::class), $c);
                },
                DependencyTwoFactory::class => DependencyTwoFactory::class.'::getInstance'
            ]
        );
        $test = $container->get('test');
        $this->assertInstanceOf(ClassWithDependencies::class, $test);

        $invokableButNotAFactory = $container->get(Invokable::class);
        $this->assertTrue($invokableButNotAFactory());
    }

    public function testCall(): void
    {
        $container = $this->getContainer();
        $result = $container->get(DependencyTwoFactory::class);
        $this->assertInstanceOf(DependencyTwo::class, $result);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to call. The type of given callable is boolean');
        Picklock::callMethod($container, 'call', true);
    }

    public function testContainerException(): void
    {
        $container = new class extends Container {
            protected function canInstantiate(string $className): bool
            {
                return true;
            }
        };

        $this->expectException(ContainerException::class);
        $container->get(DependencyTwo::class);
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
                DependencyTwo::class => DependencyTwoFactory::class
            ]);

        $result1 = $container->get(DependencyTwo::class);
        $result2 = $container->get(DependencyTwo::class);

        $this->assertSame($result1, $result2);

        return $container;
    }

    /**
     * @depends testSharedRetrieving
     * @param Container $container
     */
    public function testCreate(Container $container): void
    {
        $result1 = $container->create(DependencyTwo::class);
        $result2 = $container->create(DependencyTwo::class);
        $this->assertNotSame($result1, $result2);

        $result3 = $container->create(DependencyTwoFactory::class);
        $result4 = $container->create(DependencyTwoFactory::class);
        $this->assertNotSame($result3, $result4);
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
