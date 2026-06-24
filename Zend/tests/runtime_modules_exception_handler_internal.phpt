--TEST--
Runtime modules: internal exception handlers execute in their registration module
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

class AutoloadException extends Exception {
    public function __toString(): string {
        return 'ExceptionTarget';
    }
}
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['exception-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_142'
static function (): void {
    spl_autoload_register(static function (string $class): void {
        echo "owner:$class\n";
    });
    set_exception_handler('spl_autoload_call');
}
MODULE_ENTRY_142
, get_defined_vars(), __DIR__);
$exception = new AutoloadException();
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['exception-trigger'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_143'
static function () use ($exception): void {
    spl_autoload_register(static function (string $class): void {
        echo "trigger:$class\n";
    });
    throw $exception;
}
MODULE_ENTRY_143
, get_defined_vars(), __DIR__);
?>
--EXPECT--
owner:ExceptionTarget
