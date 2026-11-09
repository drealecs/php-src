--TEST--
Runtime modules: mutable exception handlers resolve in their registration module
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_mutable_exception_handler'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_144'
static function (): void {
    eval(<<<'PHP'
        class PhptMutableExceptionHandlerA {
            public static function handle(Throwable $exception): void {
                echo "mutable-exception:A:", $exception->getMessage(), "\n";
            }
        }
        class PhptMutableExceptionHandlerB {
            public static function handle(Throwable $exception): void {
                echo "mutable-exception:B:", $exception->getMessage(), "\n";
            }
        }
    PHP);
}
MODULE_ENTRY_144
, get_defined_vars(), __DIR__);

$class = 'PhptMutableExceptionHandlerA';
$callback = [&$class, 'handle'];
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_mutable_exception_handler'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_145'
static fn() => set_exception_handler($callback)
MODULE_ENTRY_145
, get_defined_vars(), __DIR__);
$class = 'PhptMutableExceptionHandlerB';
throw new Exception('late');
?>
--EXPECT--
mutable-exception:B:late
