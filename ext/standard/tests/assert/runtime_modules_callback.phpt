--TEST--
Runtime modules: assert callbacks resolve in their registration module
--INI--
zend.assertions=1
assert.active=1
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['assert-a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_409'
static function (): void {
    eval('function runtime_module_assert_callback() { echo "A\\n"; }');
}
MODULE_ENTRY_409
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['assert-b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_410'
static function (): void {
    eval('function runtime_module_assert_callback() { echo "B\\n"; }');
}
MODULE_ENTRY_410
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['assert-a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_411'
static function (): void {
    @assert_options(ASSERT_CALLBACK, 'runtime_module_assert_callback');
}
MODULE_ENTRY_411
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['assert-b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_412'
static function (): void {
    try {
        assert(false);
    } catch (AssertionError) {
    }
}
MODULE_ENTRY_412
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['assert-a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_413'
static function (): void {
    @ini_set('assert.callback', 'runtime_module_assert_callback');
}
MODULE_ENTRY_413
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['assert-b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_414'
static function (): void {
    try {
        assert(false);
    } catch (AssertionError) {
    }
}
MODULE_ENTRY_414
, get_defined_vars(), __DIR__);
?>
--EXPECT--
A
A
