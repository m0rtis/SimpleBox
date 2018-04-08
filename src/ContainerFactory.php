<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;
use Psr\Container\ContainerInterface;


/**
 * Class ContainerFactory
 * @package m0rtis\SimpleBox
 */
final class ContainerFactory
{
    /**
     * @param iterable $data
     * @param iterable $config
     * @return Container
     */
    public function __invoke(iterable $data = [], iterable $config = []): Container
    {
        if (!empty($config)) {
            if (isset($data['config'])) {
                $dataConfig = $data['config'];
                $dataConfig[ContainerInterface::class] = $config;
                $data['config'] = $dataConfig;
            } else {
                $data['config'] = [ContainerInterface::class => $config];
            }
        }
        return new Container($data);
    }
}