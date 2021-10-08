<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result;

use ReflectionClass;
use ReflectionException;

/**
 * Class ClassTools.
 *
 * Tools for class handling.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassTools
{
    /**
     * Produces a human-readable version of a Fully-Qualified Class Name.
     *
     * @param string $class
     * @return string
     */
    public static function toReadableClassName(string $class): string
    {
        try {
            $reflectionClass = new ReflectionClass($class);
        } catch (ReflectionException $exception) {
            return $class; // Assume the class is already readable.
        }

        return $reflectionClass->isAnonymous() ? '\__anonymous__' : $class;
    }
}
