<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests;

use m0rtis\Picklock\Picklock;
use m0rtis\SimpleBox\AutowiringContainer;
use m0rtis\SimpleBox\Tests\Mocks\ClassWithDependencies;
use m0rtis\SimpleBox\Tests\Mocks\TestInterface;
use PHPUnit\Framework\TestCase;

final class AutowiringContainerTest extends TestCase
{
    public function configDataProvider(): array
    {
        return [
            'Config injection through interface' => [[TestInterface::class => ['testKey' => 'testValue']]],
            'Config injection through class name' => [[ClassWithDependencies::class => ['testKey' => 'testValue']]]
        ];
    }

    /**
     * @param array $config
     * @dataProvider configDataProvider
     */
    public function testGetAutowiring(array $config): void
    {
        $container = new AutowiringContainer(['config' => $config]);
        /** @var ClassWithDependencies $result */
        $result = $container->get(ClassWithDependencies::class);

        $this->assertInstanceOf(ClassWithDependencies::class, $result);
        $this->assertEquals('testValue', $result->getConfigParameter('testKey'));
    }

    public function testGetReflection(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Class WrongClassName does not exists');

        $container = new AutowiringContainer();
        Picklock::callMethod($container, 'getReflection', 'WrongClassName');
    }
}
