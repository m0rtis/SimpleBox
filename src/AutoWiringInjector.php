<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Class AutoWiringInjector
 * @package m0rtis\SimpleBox
 */
final class AutoWiringInjector implements DependencyInjectorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * AutoWiringInjector constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $className
     * @return bool
     * @throws \ReflectionException
     */
    public function canInstantiate(string $className): bool
    {
        $answer = false;
        $container = $this->container;
        $constructor = (new \ReflectionClass($className))->getConstructor();
        $deps = \array_filter($this->getDependencies($constructor), function ($name, $type) use ($container) {
            return !($container->has($type) || $container->has($name));
        }, ARRAY_FILTER_USE_BOTH);
        if (empty($deps)) {
            $answer = true;
        }

        return $answer;
    }

    /**
     * @param string $className
     * @return object
     * @throws \ReflectionException
     */
    public function instantiate(string $className): object
    {
        $reflect = new \ReflectionClass($className);
        $deps = $this->getDependencies($reflect->getConstructor());
        $arguments = [];
        foreach ($deps as $name => $type) {
            if ($this->container->has($type)) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $arguments[$name] = $this->container->get($type);
            } elseif ($this->container->has($name)) {
                if ('config' === $name) {
                    $config = $this->container->get('config');
                    $arguments[$name] = $config[$name] ?? $config;
                } else {
                    $arguments[$name] = $this->container->get($name);
                }
            } elseif (ContainerInterface::class === $type) {
                $arguments[$name] = $this->container;
            }
        }
        return $reflect->newInstanceArgs($arguments);
    }

    /**
     * @param \ReflectionMethod|null $constructor
     * @return array
     */
    private function getDependencies(?\ReflectionMethod $constructor): array
    {
        $deps = [];
        if ($constructor) {
            $deps = $this->getNames(array_filter($constructor->getParameters(), function ($dep) {
                /** @var \ReflectionParameter $dep */
                return !$dep->isOptional();
            }));
        }
        return $deps;
    }

    /**
     * @param \ReflectionParameter[] $deps
     * @return string[]
     */
    private function getNames(array $deps): array
    {
        $names = [];
        foreach ($deps as $dep) {
            if ($dep->hasType()) {
                $names[$dep->getName()] = $dep->getType()->getName();
            }
        }
        return $names;
    }

    /**
     * @param $className
     * @return mixed
     * @throws ContainerExceptionInterface
     */
    private function getConfigForClass($className)
    {
        //TODO: Implement method
    }
}
