<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox\Tests\Mocks;

final class Invokable
{
    public function __invoke(): bool
    {
        return true;
    }
}
