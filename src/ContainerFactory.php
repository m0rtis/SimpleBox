<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;


/**
 * Class ContainerFactory
 * @package m0rtis\SimpleBox
 */
final class ContainerFactory
{
    /**
     * @param iterable $config
     * @param iterable $data
     * @return Container
     */
    public function __invoke(iterable $config = [], iterable $data = []): Container
    {
        $definitions = $config['definitions'] ?? [];
        return new Container($data, $definitions);
    }
}