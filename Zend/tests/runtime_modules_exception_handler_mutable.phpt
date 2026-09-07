--TEST--
Runtime modules: mutable exception handlers resolve in their registration module
--FILE--
<?php
module_run('phpt_mutable_exception_handler', static function (): void {
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
});

$class = 'PhptMutableExceptionHandlerA';
$callback = [&$class, 'handle'];
module_run(
    'phpt_mutable_exception_handler',
    static fn() => set_exception_handler($callback),
);
$class = 'PhptMutableExceptionHandlerB';
throw new Exception('late');
?>
--EXPECT--
mutable-exception:B:late
