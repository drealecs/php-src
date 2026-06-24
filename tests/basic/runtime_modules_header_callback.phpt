--TEST--
Runtime modules: header callbacks retain their registration-time callable identity
--FILE--
<?php
require_once __DIR__ . '/../../Zend/tests/runtime_modules/file_fixture.inc';

foreach (['phpt_header_callback_a' => 'A', 'phpt_header_callback_b' => 'B'] as $module => $value) {
    ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_444'
static fn() => eval(<<<PHP
        define('PHPT_HEADER_CALLBACK_VALUE', '$value');
        class PhptHeaderCallback {
            public static function __callStatic(string \$name, array \$arguments): void {
                echo 'header:', PHPT_HEADER_CALLBACK_VALUE, "\n";
            }
        }
    PHP)
MODULE_ENTRY_444
, get_defined_vars(), __DIR__);
}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_header_callback_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_445'
static fn() => header_register_callback([
    'PhptHeaderCallback',
    'missing',
])
MODULE_ENTRY_445
, get_defined_vars(), __DIR__);
?>
--EXPECT--
header:A
