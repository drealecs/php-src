--TEST--
Runtime modules: internal exception handlers execute in their registration module
--FILE--
<?php
class AutoloadException extends Exception {
    public function __toString(): string {
        return 'ExceptionTarget';
    }
}
module_run('exception-owner', static function (): void {
    spl_autoload_register(static function (string $class): void {
        echo "owner:$class\n";
    });
    set_exception_handler('spl_autoload_call');
});
$exception = new AutoloadException();
module_run('exception-trigger', static function () use ($exception): void {
    spl_autoload_register(static function (string $class): void {
        echo "trigger:$class\n";
    });
    throw $exception;
});
?>
--EXPECT--
owner:ExceptionTarget
