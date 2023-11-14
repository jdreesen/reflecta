<?php

namespace VeeWee\Reflecta\Reflect;

use VeeWee\Reflecta\Reflect\Exception\UnreflectableException;

/**
 * @template T of object
 * @param class-string<T> $className
 * @return T
 *
 * @throws UnreflectableException
 */
function instantiate(string $className): mixed {
    $classInfo = reflect_class($className);

    try {
        return $classInfo->newInstanceWithoutConstructor();
    } catch (\Throwable $previous) {
        throw UnreflectableException::nonInstantiatable($className, $previous);
    }
}
