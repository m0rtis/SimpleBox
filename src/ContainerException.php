<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;


use Psr\Container\ContainerExceptionInterface;

final class ContainerException extends \RuntimeException implements ContainerExceptionInterface
{
    public function __construct(\Exception $previousException)
    {
        $exceptionClass = \get_class($previousException);
        $message = \sprintf(
            'Exception %s thrown with message: %s',
            $exceptionClass,
            $previousException->getMessage()
        );
        parent::__construct($message);
    }
}