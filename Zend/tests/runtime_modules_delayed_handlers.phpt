--TEST--
Runtime modules: delayed handlers retain their registration-time callable identity
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

foreach (['phpt_delayed_handler_a' => 'A', 'phpt_delayed_handler_b' => 'B'] as $module => $value) {
    ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_102'
static fn() => eval(<<<PHP
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
    PHP)
MODULE_ENTRY_102
, get_defined_vars(), __DIR__);
}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_handler_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_103'
static fn() => set_error_handler('phpt_delayed_error_handler')
MODULE_ENTRY_103
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_handler_b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_104'
static fn() => set_error_handler('phpt_delayed_error_handler')
MODULE_ENTRY_104
, get_defined_vars(), __DIR__);
restore_error_handler();
trigger_error('retained', E_USER_WARNING);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_handler_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_105'
static fn() => set_error_handler([
    new PhptDelayedMagicHandler(),
    'missing',
])
MODULE_ENTRY_105
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_handler_b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_106'
static fn() => trigger_error('magic', E_USER_WARNING)
MODULE_ENTRY_106
, get_defined_vars(), __DIR__);
restore_error_handler();

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_handler_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_107'
static fn() => set_error_handler([
    'PhptDelayedMagicHandler',
    'missingStatic',
])
MODULE_ENTRY_107
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_handler_b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_108'
static fn() => trigger_error('magic-static', E_USER_WARNING)
MODULE_ENTRY_108
, get_defined_vars(), __DIR__);
restore_error_handler();

$class = 'PhptMutableErrorHandlerA';
$callback = [&$class, 'handle'];
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_handler_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_109'
static fn() => set_error_handler($callback)
MODULE_ENTRY_109
, get_defined_vars(), __DIR__);
$class = 'PhptMutableErrorHandlerB';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_handler_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_110'
static function (): void {
    echo 'stored:', (get_error_handler())[0], "\n";
}
MODULE_ENTRY_110
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_handler_b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_111'
static fn() => trigger_error('mutable', E_USER_WARNING)
MODULE_ENTRY_111
, get_defined_vars(), __DIR__);
restore_error_handler();

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_handler_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_112'
static fn() => set_exception_handler('phpt_delayed_exception_handler')
MODULE_ENTRY_112
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_handler_b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_113'
static fn() => throw new Exception('uncaught')
MODULE_ENTRY_113
, get_defined_vars(), __DIR__);
?>
--EXPECT--
error:A:retained
magic:A
magic-static:A
stored:PhptMutableErrorHandlerB
mutable-error:B:mutable
exception:A:uncaught
