<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 * @package m0rtis\SimpleBox
 */
final class NotFoundException extends \InvalidArgumentException implements NotFoundExceptionInterface
{

}
