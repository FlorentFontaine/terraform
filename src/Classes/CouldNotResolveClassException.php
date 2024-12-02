<?php

namespace Classes;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class CouldNotResolveClassException extends RuntimeException implements NotFoundExceptionInterface
{
}
