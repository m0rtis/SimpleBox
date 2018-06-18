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
     * @var array
     */
    private $reflections = [];
    /**
     * @param string $className
     * @return bool
     */
    protected function canInstantiate(string $className): bool
    {
        $answer = false;
        if (\class_exists($className)) {
            $container = $this;
            $constructor = $this->getReflection($className)->getConstructor();
            $deps = \array_filter($this->getDependencies($constructor), function ($type, $name) use ($container) {
                return !($container->has($type) || $container->has($name) || ContainerInterface::class === $type);
            }, ARRAY_FILTER_USE_BOTH);
            if (\count($deps) === 0) {
                $answer = true;
            }
        }

        return $answer;
    }

    /**
     * @param string $className
     * @throws \InvalidArgumentException
     * @return object
     */
    protected function instantiate(string $className): object
    {
        $reflection = $this->getReflection($className);
        $deps = $this->getDependencies($reflection->getConstructor());
        $arguments = [];
        foreach ($deps as $name => $type) {
            if ($this->has($type)) {
                $arguments[$name] = $this->get($type);
            } elseif ($this->has($name)) {
                if ('config' === $name) {
                    $config = $this->getConfigForClass($reflection, $this->get('config'));
                    $arguments[$name] = $config[$name] ?? $config;
                } else {
                    $arguments[$name] = $this->get($name);
                }
            } elseif (ContainerInterface::class === $type) {
                $arguments[$name] = $this;
            }
        }
        return $this->getInstance($reflection, $arguments);
    }

    /**
     * @param string $className
     * @return \ReflectionClass
     * @throws \InvalidArgumentException
     */
    private function getReflection(string $className): \ReflectionClass
    {
        if (!\class_exists($className)) {
            throw new \InvalidArgumentException("Class $className does not exists");
        }
        return $this->reflections[$className] ?? $this->reflections[$className] = new \ReflectionClass($className);
    }

    /**
     * @param \ReflectionClass $reflection
     * @param array $arguments
     * @return object
     */
    private function getInstance(\ReflectionClass $reflection, array $arguments): object
    {
        $object = $reflection->newInstanceArgs($arguments);
        if (isset($this->reflections[$reflection->getName()])) {
            unset($this->reflections[$reflection->getName()]);
        }
        return $object;
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
                    break;
                }
            }
        }

        return $config;
    }
}
