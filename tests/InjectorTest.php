<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests;

use m0rtis\SimpleBox\Tests\Mocks\ClassWithDependencies;


final class InjectorTest extends ContainerTest
{
    public function testGetAutowiring(): void
    {
        $container = $this->getContainer([
            'config' => [
                'testKey' => 'testValue'
            ]
        ]);
        /** @var ClassWithDependencies $result */
        $result = $container->get(ClassWithDependencies::class);

        $this->assertInstanceOf(ClassWithDependencies::class, $result);
        $this->assertEquals('testValue', $result->getConfigParameter('testKey'));
    }
}