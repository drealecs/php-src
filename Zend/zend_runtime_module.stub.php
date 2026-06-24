<?php

/** @generate-class-entries */

/**
 * @strict-properties
 * @not-serializable
 */
final class RuntimeModule
{
    public function __construct() {}

    public static function current(): RuntimeModule {}

    public function require(string $filename): mixed {}

    public function addDependency(RuntimeModule $dependency): void {}
}
