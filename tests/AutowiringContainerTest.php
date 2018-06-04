<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests;

use m0rtis\SimpleBox\AutowiringContainer;
use m0rtis\SimpleBox\Tests\Mocks\ClassWithDependencies;
use PHPUnit\Framework\TestCase;

final class AutowiringContainerTest extends TestCase
{
    public function testGetAutowiring(): void
    {
        $container = new AutowiringContainer([
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
