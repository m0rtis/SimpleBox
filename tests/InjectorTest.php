<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests;

use m0rtis\SimpleBox\AutoWiringInjectorFactory;
use m0rtis\SimpleBox\Container;
use m0rtis\SimpleBox\DependencyInjectorInterface;
use m0rtis\SimpleBox\Tests\Mocks\ClassWithDependencies;
use PHPUnit\Framework\TestCase;


final class InjectorTest extends TestCase
{
    public function testGetAutowiring(): void
    {
        $container = new Container([
            'config' => [
                'testKey' => 'testValue'
            ],
            DependencyInjectorInterface::class => AutoWiringInjectorFactory::class
        ]);
        /** @var ClassWithDependencies $result */
        $result = $container->get(ClassWithDependencies::class);

        $this->assertInstanceOf(ClassWithDependencies::class, $result);
        $this->assertEquals('testValue', $result->getConfigParameter('testKey'));
    }
}