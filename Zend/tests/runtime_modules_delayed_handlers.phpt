--TEST--
Runtime modules: delayed handlers retain their registration-time callable identity
--FILE--
<?php
foreach (['phpt_delayed_handler_a' => 'A', 'phpt_delayed_handler_b' => 'B'] as $module => $value) {
    module_run($module, static fn() => eval(<<<PHP
        define('PHPT_DELAYED_HANDLER_VALUE', '$value');
        function phpt_delayed_error_handler(int \$type, string \$message): bool {
            echo 'error:$value:', \$message, "\n";
            return true;
        }
        function phpt_delayed_exception_handler(Throwable \$exception): void {
            echo 'exception:$value:', \$exception->getMessage(), "\n";
        }
        class PhptDelayedMagicHandler {
            public function __call(string \$name, array \$arguments): bool {
                echo 'magic:', PHPT_DELAYED_HANDLER_VALUE, "\n";
                return true;
            }
            public static function __callStatic(string \$name, array \$arguments): bool {
                echo 'magic-static:', PHPT_DELAYED_HANDLER_VALUE, "\n";
                return true;
            }
        }
        class PhptMutableErrorHandlerA {
            public static function handle(int \$type, string \$message): bool {
                echo "mutable-error:A:\$message\n";
                return true;
            }
        }
        class PhptMutableErrorHandlerB {
            public static function handle(int \$type, string \$message): bool {
                echo "mutable-error:B:\$message\n";
                return true;
            }
        }
    PHP));
}

module_run('phpt_delayed_handler_a', static fn() => set_error_handler('phpt_delayed_error_handler'));
module_run('phpt_delayed_handler_b', static fn() => set_error_handler('phpt_delayed_error_handler'));
restore_error_handler();
trigger_error('retained', E_USER_WARNING);

module_run('phpt_delayed_handler_a', static fn() => set_error_handler([
    new PhptDelayedMagicHandler(),
    'missing',
]));
module_run('phpt_delayed_handler_b', static fn() => trigger_error('magic', E_USER_WARNING));
restore_error_handler();

module_run('phpt_delayed_handler_a', static fn() => set_error_handler([
    'PhptDelayedMagicHandler',
    'missingStatic',
]));
module_run('phpt_delayed_handler_b', static fn() => trigger_error('magic-static', E_USER_WARNING));
restore_error_handler();

$class = 'PhptMutableErrorHandlerA';
$callback = [&$class, 'handle'];
module_run('phpt_delayed_handler_a', static fn() => set_error_handler($callback));
$class = 'PhptMutableErrorHandlerB';
module_run('phpt_delayed_handler_a', static function (): void {
    echo 'stored:', (get_error_handler())[0], "\n";
});
module_run('phpt_delayed_handler_b', static fn() => trigger_error('mutable', E_USER_WARNING));
restore_error_handler();

module_run('phpt_delayed_handler_a', static fn() => set_exception_handler('phpt_delayed_exception_handler'));
module_run('phpt_delayed_handler_b', static fn() => throw new Exception('uncaught'));
?>
--EXPECT--
error:A:retained
magic:A
magic-static:A
stored:PhptMutableErrorHandlerB
mutable-error:B:mutable
exception:A:uncaught
