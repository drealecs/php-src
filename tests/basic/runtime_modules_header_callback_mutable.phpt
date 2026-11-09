--TEST--
Runtime modules: header callbacks retain their registration-time callable identity
--FILE--
<?php
require_once __DIR__ . '/../../Zend/tests/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_mutable_header_callback'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_450'
static function (): void {
    eval(<<<'PHP'
        class PhptMutableHeaderCallbackA {
            public static function run(): void {
                echo PhptMutableHeaderCallbackValue;
            }
        }
        class PhptMutableHeaderCallbackB {
            public static function run(): void {
                echo "mutable-header:B\n";
            }
        }
        const PhptMutableHeaderCallbackValue = "mutable-header:A\n";
    PHP);
}
MODULE_ENTRY_450
, get_defined_vars(), __DIR__);

$class = 'PhptMutableHeaderCallbackA';
$callback = [&$class, 'run'];
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_mutable_header_callback'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_451'
static fn() => header_register_callback($callback)
MODULE_ENTRY_451
, get_defined_vars(), __DIR__);
$class = 'PhptMutableHeaderCallbackB';
?>
--EXPECT--
mutable-header:A
