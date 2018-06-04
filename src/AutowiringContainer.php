<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;

use Psr\Container\ContainerInterface;

/**
 * Class AutowiringContainer
 * @package m0rtis\SimpleBox
 */
class AutowiringContainer extends Container
{
    /**
     * @param string $className
     * @return bool
     * @throws \ReflectionException
     */
    protected function canInstantiate(string $className): bool
    {
        $answer = false;
        $container = $this;
        $constructor = (new \ReflectionClass($className))->getConstructor();
        $deps = \array_filter($this->getDependencies($constructor), function ($name, $type) use ($container) {
            return !($container->has($type) || $container->has($name));
        }, ARRAY_FILTER_USE_BOTH);
        if (\count($deps) === 0) {
            $answer = true;
        }


        return $answer;
    }

    /**
     * @param string $className
     * @return object
     * @throws \ReflectionException
     */
    protected function instantiate(string $className): object
    {
        $reflect = new \ReflectionClass($className);
        $deps = $this->getDependencies($reflect->getConstructor());
        $arguments = [];
        foreach ($deps as $name => $type) {
            if ($this->has($type)) {
                $arguments[$name] = $this->get($type);
            } elseif ($this->has($name)) {
                if ('config' === $name) {
                    $config = $this->getConfigForClass($reflect, $this->get('config'));
                    $arguments[$name] = $config[$name] ?? $config;
                } else {
                    $arguments[$name] = $this->get($name);
                }
            } elseif (ContainerInterface::class === $type) {
                $arguments[$name] = $this;
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
     * @param \ReflectionClass $class
     * @param iterable $config
     * @return mixed
     */
    private function getConfigForClass(\ReflectionClass $class, iterable $config): iterable
    {
        if (isset($config[$class->getName()])) {
            $config = $config[$class->getName()];
        } else {
            foreach ($class->getInterfaceNames() as $interfaceName) {
                if (isset($config[$interfaceName])) {
                    $config = $config[$interfaceName];
                    continue;
                }
            }
        }

        return $config;
    }
}
