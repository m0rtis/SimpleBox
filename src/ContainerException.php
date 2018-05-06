<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;


use Psr\Container\ContainerExceptionInterface;

/**
 * Class ContainerException
 * @package m0rtis\SimpleBox
 */
final class ContainerException extends \RuntimeException implements ContainerExceptionInterface
{
    /**
     * ContainerException constructor.
     * @param \Exception $previousException
     */
    public function __construct(\Exception $previousException)
    {
        $exceptionClass = \get_class($previousException);
        $message = \sprintf(
            'Exception %s has thrown with message: %s',
            $exceptionClass,
            $previousException->getMessage()
        );
        parent::__construct($message);
    }
}