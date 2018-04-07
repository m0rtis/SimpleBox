<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;


use Psr\Container\NotFoundExceptionInterface;

final class NotFoundException extends \InvalidArgumentException implements NotFoundExceptionInterface
{

}