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
     * @param int $code
     */
    public function __construct(\Exception $previousException, int $code = 0)
    {
        $exceptionClass = \get_class($previousException);
        $trace = $previousException->getTrace();
        $message = \sprintf(
            'Exception %s has thrown by %s::%s with message: %s',
            $exceptionClass,
            $trace[0]['class'],
            $trace[0]['function'],
            $previousException->getMessage()
        );
        parent::__construct($message, $code, $previousException);
    }
}
