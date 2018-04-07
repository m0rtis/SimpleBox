<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;


use Psr\Container\ContainerInterface;

/**
 * Interface DependencyInjectorInterface
 * @package m0rtis\SimpleBox
 */
interface DependencyInjectorInterface
{
    /**
     * DependencyInjectorInterface constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container);

    /**
     * @param string $className
     * @return bool
     */
    public function canInstantiate(string $className): bool;

    /**
     * @param string $className
     * @return object
     */
    public function instantiate(string $className): object;
}